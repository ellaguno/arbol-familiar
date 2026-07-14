<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Message;
use App\Models\Person;
use App\Models\User;
use App\Services\RelationshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Controllers\InvitationController;

class PersonController extends Controller
{
    public function __construct(protected RelationshipService $relationships)
    {
    }

    /**
     * Muestra el listado de personas.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Filtrar por privacidad según los 4 niveles (scope reutilizable en Person)
        $query = Person::query()->visibleTo($user);

        // Busqueda por nombre (inteligente)
        if ($request->filled('search')) {
            $query->searchByName($request->search);
        }

        // Filtro por genero
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filtro por herencia etnica
        if ($request->filled('ethnic_heritage')) {
            $query->where('has_ethnic_heritage', $request->ethnic_heritage === 'yes');
        }

        // Filtro por region de herencia
        if ($request->filled('heritage_region')) {
            $query->where('heritage_region', $request->heritage_region);
        }

        // Filtro por estado (vivo/fallecido)
        if ($request->filled('is_living')) {
            $query->where('is_living', $request->is_living === 'yes');
        }

        // Ordenamiento
        $sortBy = $request->get('sort', 'first_name');
        $sortDir = $request->get('dir', 'asc');
        $allowedSorts = ['first_name', 'patronymic', 'birth_date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $persons = $query->paginate(20)->withQueryString();

        return view('persons.index', compact('persons'));
    }

    /**
     * Muestra el formulario para crear una persona.
     */
    public function create(Request $request)
    {
        // Datos prellenados segun parentesco
        $prefill = [];
        $relatedPerson = null;
        $relation = $request->get('relation');
        $relatedToId = $request->get('related_to');
        $families = collect(); // Familias disponibles para agregar hijo
        $selectedFamilyId = $request->get('family_id');

        if ($relation && $relatedToId) {
            $relatedPerson = Person::find($relatedToId);

            if ($relatedPerson) {
                switch ($relation) {
                    case 'child':
                        // Obtener todas las familias donde la persona es conyuge
                        $families = $relatedPerson->familiesAsSpouse()->with(['husband', 'wife'])->get();

                        // Si hay una familia seleccionada, usar esa
                        if ($selectedFamilyId) {
                            $family = $families->firstWhere('id', $selectedFamilyId);
                        } else {
                            $family = $families->first();
                        }

                        if ($family) {
                            $prefill['patronymic'] = $family->husband?->patronymic ?? $relatedPerson->patronymic;
                            $prefill['matronymic'] = $family->wife?->patronymic ?? '';
                        } else {
                            // Si no tiene conyuge, usar el apellido de la persona segun su genero
                            if ($relatedPerson->gender === 'M') {
                                $prefill['patronymic'] = $relatedPerson->patronymic;
                            } else {
                                $prefill['matronymic'] = $relatedPerson->patronymic;
                            }
                        }
                        break;

                    case 'father':
                    case 'parent':
                        // Si es padre/madre, prellenar segun genero esperado
                        // El padre debe tener el apellido paterno del hijo
                        $prefill['patronymic'] = $relatedPerson->patronymic;
                        $prefill['gender'] = 'M'; // Por defecto padre
                        break;

                    case 'mother':
                        // La madre debe tener el apellido materno del hijo como su apellido paterno
                        $prefill['patronymic'] = $relatedPerson->matronymic ?? '';
                        $prefill['gender'] = 'F';
                        break;

                    case 'sibling':
                        // Hermano comparte ambos apellidos
                        $prefill['patronymic'] = $relatedPerson->patronymic;
                        $prefill['matronymic'] = $relatedPerson->matronymic;
                        break;

                    case 'spouse':
                        // Conyuge no hereda apellidos
                        break;
                }

                // Heredar herencia etnica si el familiar la tiene
                if ($relatedPerson->has_ethnic_heritage && in_array($relation, ['child', 'sibling'])) {
                    $prefill['has_ethnic_heritage'] = true;
                    $prefill['heritage_region'] = $relatedPerson->heritage_region;
                    $prefill['origin_town'] = $relatedPerson->origin_town;
                }
            }
        }

        return view('persons.create', compact('prefill', 'relatedPerson', 'relation', 'families', 'selectedFamilyId'));
    }

    /**
     * Almacena una nueva persona.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePerson($request);
        $user = auth()->user();

        $person = Person::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        ActivityLog::log('person_created', $user, $person);

        // Enviar invitación de consentimiento si se proporcionó email
        $invitationMessage = null;
        if (!empty($validated['email'])) {
            $invitationController = new InvitationController();
            $invitationResult = $invitationController->sendConsentInvitation($person);
            if ($invitationResult['success']) {
                $invitationMessage = $invitationResult['message'];
            }
        }

        // Crear relacion familiar si se especifico
        $relation = $request->get('relation');
        $relatedToId = $request->get('related_to');
        $familyId = $request->get('family_id');

        if ($relation && $relatedToId) {
            $relatedPerson = Person::find($relatedToId);
            if ($relatedPerson) {
                switch ($relation) {
                    case 'child':
                        // La nueva persona es hijo del relacionado
                        $this->relationships->addChildRelationship($relatedPerson, $person, $familyId);
                        break;

                    case 'father':
                        // La nueva persona es el padre del relacionado
                        $this->relationships->addParentRelationship($relatedPerson, $person, 'M');
                        break;

                    case 'mother':
                        // La nueva persona es la madre del relacionado
                        $this->relationships->addParentRelationship($relatedPerson, $person, 'F');
                        break;

                    case 'parent':
                        // La nueva persona es padre/madre del relacionado (usa genero de la persona)
                        $this->relationships->addParentRelationship($relatedPerson, $person);
                        break;

                    case 'sibling':
                        // La nueva persona es hermano del relacionado
                        $this->relationships->addSiblingRelationship($relatedPerson, $person);
                        break;

                    case 'spouse':
                        // La nueva persona es conyuge del relacionado
                        $this->relationships->addSpouseRelationship($relatedPerson, $person, [
                            'family_status' => 'married',
                        ]);
                        break;
                }

                ActivityLog::log('relationship_created', $user, $person, [
                    'related_person_id' => $relatedPerson->id,
                    'type' => $relation,
                ]);

                // Redirigir al arbol del familiar relacionado si tiene permiso
                $successMessage = __('Persona creada y agregada al arbol correctamente.');
                if ($invitationMessage) {
                    $successMessage .= ' ' . $invitationMessage;
                }

                // Refrescar modelo para que las nuevas relaciones se reflejen en canBeViewedBy
                $relatedPerson->refresh();

                if ($relatedPerson->canBeViewedBy($user)) {
                    return redirect()->route('tree.view', $relatedPerson)
                        ->with('success', $successMessage);
                } else {
                    return redirect()->route('persons.show', $person)
                        ->with('success', $successMessage);
                }
            }
        }

        $successMessage = __('Persona creada correctamente.');
        if ($invitationMessage) {
            $successMessage .= ' ' . $invitationMessage;
        }
        return redirect()->route('persons.show', $person)
            ->with('success', $successMessage);
    }

    /**
     * Muestra los detalles de una persona.
     */
    public function show(Person $person)
    {
        $user = auth()->user();

        if (!$person->canBeViewedBy($user)) {
            // Si no puede ver completo pero si para claim, mostrar vista limitada
            if ($person->canBeViewedForClaim($user)) {
                return view('persons.show-limited', compact('person'));
            }

            $previousUrl = url()->previous();
            $currentUrl = url()->current();
            $redirectUrl = ($previousUrl && $previousUrl !== $currentUrl)
                ? $previousUrl
                : route('persons.index');

            return redirect($redirectUrl)->with('error', __('No tienes permiso para ver esta persona.'));
        }

        $person->load([
            'familiesAsHusband.wife',
            'familiesAsHusband.children',
            'familiesAsWife.husband',
            'familiesAsWife.children',
            'familiesAsChild.husband',
            'familiesAsChild.wife',
            'media',
            'events.media',
        ]);

        return view('persons.show', compact('person'));
    }

    /**
     * Muestra el formulario para editar una persona.
     */
    public function edit(Person $person)
    {
        $this->authorizeEdit($person);

        return view('persons.edit', compact('person'));
    }

    /**
     * Actualiza una persona.
     */
    public function update(Request $request, Person $person)
    {
        $this->authorizeEdit($person);

        $validated = $this->validatePerson($request, $person);
        $user = auth()->user();

        // Guardar email anterior para comparar
        $oldEmail = $person->email;

        $person->update([
            ...$validated,
            'updated_by' => $user->id,
        ]);

        ActivityLog::log('person_updated', $user, $person);

        // Enviar invitación si se agregó un nuevo email
        $invitationMessage = null;
        if (!empty($validated['email']) && $validated['email'] !== $oldEmail && !$person->user_id) {
            $invitationController = new InvitationController();
            $invitationResult = $invitationController->sendConsentInvitation($person);
            if ($invitationResult['success']) {
                $invitationMessage = $invitationResult['message'];
            }
        }

        $successMessage = __('Persona actualizada correctamente.');
        if ($invitationMessage) {
            $successMessage .= ' ' . $invitationMessage;
        }

        return redirect()->route('persons.show', $person)
            ->with('success', $successMessage);
    }

    /**
     * Elimina una persona.
     */
    public function destroy(Person $person)
    {
        $this->authorizeEdit($person);

        $user = auth()->user();

        // No permitir eliminar si tiene usuario asociado
        if ($person->user_id) {
            return back()->with('error', 'No se puede eliminar una persona con cuenta de usuario.');
        }

        // No permitir eliminar si es parte de una familia
        $hasFamily = $person->familiesAsHusband()->exists()
            || $person->familiesAsWife()->exists()
            || $person->familiesAsChild()->exists();

        if ($hasFamily) {
            return back()->with('error', 'Elimina las relaciones familiares primero.');
        }

        // Soft delete: la persona se marca como eliminada (reversible). Se
        // conservan la foto y sus archivos por si se restaura; la limpieza
        // fisica solo debe hacerse en un borrado permanente (forceDelete).
        ActivityLog::log('person_deleted', $user, $person);

        $person->delete();

        return redirect()->route('persons.index')
            ->with('success', 'Persona eliminada correctamente.');
    }

    /**
     * Actualiza la foto de una persona.
     */
    public function updatePhoto(Request $request, Person $person)
    {
        $this->authorizeEdit($person);

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'photo.required' => 'Selecciona una imagen.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'photo.max' => 'La imagen no debe superar 5MB.',
        ]);

        $user = auth()->user();
        $thumbnailService = app(\App\Services\ThumbnailService::class);

        // Eliminar foto anterior y su thumbnail
        if ($person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
        }
        $thumbnailService->delete($person->photo_thumbnail_path);

        $path = $request->file('photo')->store('photos/persons', 'public');
        $thumbnailPath = $thumbnailService->generate($path, 'photos/persons/thumbnails');
        $person->update([
            'photo_path' => $path,
            'photo_thumbnail_path' => $thumbnailPath,
            'updated_by' => $user->id,
        ]);

        ActivityLog::log('person_photo_updated', $user, $person);

        // Responder JSON para peticiones AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'photo' => asset('storage/' . $path),
                'thumbnail' => $person->photo_thumbnail_url,
                'message' => 'Foto actualizada.',
            ]);
        }

        return back()->with('success', 'Foto actualizada.');
    }

    /**
     * Elimina la foto de una persona.
     */
    public function deletePhoto(Person $person)
    {
        $this->authorizeEdit($person);

        $user = auth()->user();

        if ($person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
            app(\App\Services\ThumbnailService::class)->delete($person->photo_thumbnail_path);
            $person->update([
                'photo_path' => null,
                'photo_thumbnail_path' => null,
                'updated_by' => $user->id,
            ]);

            ActivityLog::log('person_photo_deleted', $user, $person);
        }

        return back()->with('success', 'Foto eliminada.');
    }

    /**
     * Muestra las relaciones familiares de una persona.
     */
    public function relationships(Person $person)
    {
        $this->authorizeView($person);

        $person->load([
            'familiesAsHusband.wife',
            'familiesAsHusband.children',
            'familiesAsWife.husband',
            'familiesAsWife.children',
            'familiesAsChild.husband',
            'familiesAsChild.wife',
        ]);

        $user = auth()->user();

        // Obtener IDs de familia si el usuario tiene persona asociada
        $familyIds = [];
        if ($user->person_id && $user->person) {
            $familyIds = $user->person->extendedFamilyIds;
        }

        // Obtener personas disponibles para agregar relacion
        // Incluye: creadas por el usuario, nivel community/public, y familia del usuario
        $availablePersons = Person::where('id', '!=', $person->id)
            ->where(function ($q) use ($user, $familyIds) {
                $q->where('created_by', $user->id)
                  ->orWhereIn('privacy_level', ['community', 'selected_users']);

                // Agregar personas de la familia del usuario
                if (!empty($familyIds)) {
                    $q->orWhereIn('id', $familyIds);
                }

                // Agregar personas del mismo árbol (mismo creador)
                if ($user->person_id && $user->person) {
                    $q->orWhere('created_by', $user->person->created_by);
                }
            })
            ->orderBy('first_name')
            ->get();

        return view('persons.relationships', compact('person', 'availablePersons'));
    }

    /**
     * Agrega una relacion familiar.
     */
    public function storeRelationship(Request $request, Person $person)
    {
        $this->authorizeEdit($person);

        $validated = $request->validate([
            'relationship_type' => ['required', 'in:spouse,parent,father,mother,child,sibling'],
            'related_person_id' => ['required', 'exists:persons,id', 'different:' . $person->id],
            'family_status' => ['nullable', 'in:married,divorced,widowed,separated,partners,annulled'],
            'marriage_date' => ['nullable', 'date'],
        ]);

        $relatedPerson = Person::findOrFail($validated['related_person_id']);
        $user = auth()->user();

        // Si la persona destino tiene cuenta registrada y no es editable por el usuario,
        // redirigir al flujo de autorización en lugar de vincular directamente.
        if ($relatedPerson->user_id
            && $relatedPerson->user_id !== $user->id
            && !$relatedPerson->canBeEditedBy($user->id)
            && !$user->is_admin
        ) {
            // Determinar destinatario: el usuario vinculado
            $recipientId = $relatedPerson->user_id;

            // Verificar si ya hay solicitud pendiente
            $pendingExists = \App\Models\Message::where('sender_id', $user->id)
                ->where('related_person_id', $relatedPerson->id)
                ->where('type', 'family_edit_request')
                ->where('action_status', 'pending')
                ->exists();

            if ($pendingExists) {
                return back()->with('info', __('Ya tienes una solicitud pendiente para vincular con :name.', [
                    'name' => $relatedPerson->full_name,
                ]));
            }

            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'type' => 'family_edit_request',
                'subject' => __(':name solicita vincular a :person como :relation de :target', [
                    'name' => $user->person?->full_name ?? $user->email,
                    'person' => $relatedPerson->full_name,
                    'relation' => __($validated['relationship_type']),
                    'target' => $person->full_name,
                ]),
                'body' => __('Solicito permiso para establecer que :person es :relation de :target.', [
                    'person' => $relatedPerson->full_name,
                    'relation' => __($validated['relationship_type']),
                    'target' => $person->full_name,
                ]),
                'related_person_id' => $relatedPerson->id,
                'action_required' => true,
                'action_status' => 'pending',
                'metadata' => json_encode([
                    'link_request' => true,
                    'source_person_id' => $person->id,
                    'relationship_type' => $validated['relationship_type'],
                    'family_status' => $validated['family_status'] ?? null,
                    'marriage_date' => $validated['marriage_date'] ?? null,
                ]),
            ]);

            ActivityLog::log('relationship_authorization_requested', $user, $person, [
                'related_person_id' => $relatedPerson->id,
                'relationship_type' => $validated['relationship_type'],
            ]);

            return back()->with('info', __('Se envio una solicitud de autorizacion a :name para vincular esta relacion.', [
                'name' => $relatedPerson->full_name,
            ]));
        }

        // Normalizar father/mother a parent para el switch
        $relationshipType = $validated['relationship_type'];
        $parentGender = null;
        if ($relationshipType === 'father') {
            $parentGender = 'M';
            $relationshipType = 'parent';
        } elseif ($relationshipType === 'mother') {
            $parentGender = 'F';
            $relationshipType = 'parent';
        }

        switch ($relationshipType) {
            case 'spouse':
                $this->relationships->addSpouseRelationship($person, $relatedPerson, $validated);
                break;
            case 'parent':
                $this->relationships->addParentRelationship($person, $relatedPerson, $parentGender);
                break;
            case 'child':
                $this->relationships->addChildRelationship($person, $relatedPerson);
                break;
            case 'sibling':
                $this->relationships->addSiblingRelationship($person, $relatedPerson);
                break;
        }

        ActivityLog::log('relationship_created', $user, $person, [
            'related_person_id' => $relatedPerson->id,
            'type' => $validated['relationship_type'],
        ]);

        return back()->with('success', 'Relacion agregada correctamente.');
    }

    /**
     * Elimina una relacion familiar.
     * IMPORTANTE: Solo elimina la relación específica, no la familia completa ni otras relaciones.
     */
    public function destroyRelationship(Person $person, string $type, Person $related)
    {
        $this->authorizeEdit($person);

        $user = auth()->user();

        switch ($type) {
            case 'spouse':
                // Encontrar la familia específica donde ambos son cónyuges
                // IMPORTANTE: Usar where() agrupado correctamente para evitar borrar familias incorrectas
                $family = Family::where(function ($q) use ($person, $related) {
                    $q->where(function ($inner) use ($person, $related) {
                        $inner->where('husband_id', $person->id)
                              ->where('wife_id', $related->id);
                    })->orWhere(function ($inner) use ($person, $related) {
                        $inner->where('husband_id', $related->id)
                              ->where('wife_id', $person->id);
                    });
                })->first();

                if ($family) {
                    // Si la familia tiene hijos, no eliminar la familia, solo desvincular al cónyuge
                    $hasChildren = FamilyChild::where('family_id', $family->id)->exists();

                    if ($hasChildren) {
                        // Desvincular el cónyuge pero mantener la familia para los hijos
                        // Determinar cuál cónyuge desvincular
                        if ($family->husband_id === $person->id || $family->husband_id === $related->id) {
                            // Si $person es el esposo, desvincular dependiendo de quién inició la acción
                            if ($family->husband_id === $related->id) {
                                $family->update(['husband_id' => null]);
                            } else {
                                $family->update(['wife_id' => null]);
                            }
                        }
                    } else {
                        // Si no tiene hijos, eliminar la familia completa
                        $family->delete();
                    }
                }
                break;

            case 'parent':
                // Eliminar SOLO la relación de este hijo con este padre específico
                // Buscar familias donde el 'related' es padre/madre
                $familyIds = Family::where(function ($q) use ($related) {
                    $q->where('husband_id', $related->id)
                      ->orWhere('wife_id', $related->id);
                })->pluck('id');

                // Eliminar solo la relación hijo-familia específica
                FamilyChild::where('person_id', $person->id)
                    ->whereIn('family_id', $familyIds)
                    ->delete();
                break;

            case 'child':
                // Eliminar SOLO este hijo de las familias donde $person es padre/madre
                $familyIds = Family::where(function ($q) use ($person) {
                    $q->where('husband_id', $person->id)
                      ->orWhere('wife_id', $person->id);
                })->pluck('id');

                FamilyChild::where('person_id', $related->id)
                    ->whereIn('family_id', $familyIds)
                    ->delete();
                break;

            case 'sibling':
                // Para hermanos, necesitamos encontrar la familia compartida
                // y remover a 'related' de esa familia si no tiene otros vínculos
                $personFamilyIds = $person->familiesAsChild()->pluck('family_id');
                $relatedFamilyIds = $related->familiesAsChild()->pluck('family_id');

                // Encontrar la familia compartida
                $sharedFamilyIds = $personFamilyIds->intersect($relatedFamilyIds);

                if ($sharedFamilyIds->isNotEmpty()) {
                    // Remover 'related' de la familia compartida
                    FamilyChild::where('person_id', $related->id)
                        ->whereIn('family_id', $sharedFamilyIds)
                        ->delete();
                }
                break;
        }

        ActivityLog::log('relationship_deleted', $user, $person, [
            'related_person_id' => $related->id,
            'type' => $type,
        ]);

        return back()->with('success', __('Relacion eliminada correctamente.'));
    }

    /**
     * Valida los datos de persona.
     */
    protected function validatePerson(Request $request, ?Person $person = null): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['required', 'string', 'max:100'],
            'matronymic' => ['nullable', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:M,F,U,O'],
            'marital_status' => ['nullable', 'in:single,married,common_law,divorced,widowed'],
            'birth_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_date_approx' => ['boolean'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_country' => ['nullable', 'string', 'max:100'],
            'death_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'death_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'death_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'death_date_approx' => ['boolean'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'death_country' => ['nullable', 'string', 'max:100'],
            'is_living' => ['boolean'],
            'is_minor' => ['boolean'],
            'residence_place' => ['nullable', 'string', 'max:255'],
            'residence_country' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'has_ethnic_heritage' => ['nullable', 'boolean'],
            'heritage_region' => ['nullable', 'string', 'max:100'],
            'origin_town' => ['nullable', 'string', 'max:255'],
            'migration_decade' => ['nullable', 'string', 'max:10'],
            'migration_destination' => ['nullable', 'string', 'max:255'],
            'privacy_level' => ['required', 'in:direct_family,extended_family,selected_users,community'],
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'patronymic.required' => 'El apellido paterno es obligatorio.',
            'gender.required' => 'El genero es obligatorio.',
        ]);

        // Construir birth_date si tenemos todos los componentes
        if (!empty($validated['birth_year']) && !empty($validated['birth_month']) && !empty($validated['birth_day'])) {
            $validated['birth_date'] = sprintf(
                '%04d-%02d-%02d',
                $validated['birth_year'],
                $validated['birth_month'],
                $validated['birth_day']
            );
        } else {
            $validated['birth_date'] = null;
        }

        // Construir death_date si tenemos todos los componentes
        if (!empty($validated['death_year']) && !empty($validated['death_month']) && !empty($validated['death_day'])) {
            $validated['death_date'] = sprintf(
                '%04d-%02d-%02d',
                $validated['death_year'],
                $validated['death_month'],
                $validated['death_day']
            );
        } else {
            $validated['death_date'] = null;
        }

        // Limpiar valores vacíos para campos numéricos
        foreach (['birth_year', 'birth_month', 'birth_day', 'death_year', 'death_month', 'death_day'] as $field) {
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }

    /**
     * Verifica si el usuario puede ver la persona.
     * Usa el método canBeViewedBy del modelo Person que implementa
     * los 4 niveles de privacidad.
     * Usa HttpResponseException para redirigir sin destruir la sesión.
     */
    protected function authorizeView(Person $person): void
    {
        $user = auth()->user();

        if (!$person->canBeViewedBy($user)) {
            $previousUrl = url()->previous();
            $currentUrl = url()->current();
            $redirectUrl = ($previousUrl && $previousUrl !== $currentUrl)
                ? $previousUrl
                : route('persons.index');

            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect($redirectUrl)->with('error', __('No tienes permiso para ver esta persona.'))
            );
        }
    }

    /**
     * Verifica si el usuario puede editar la persona.
     * Para menores de edad con padres registrados en el sistema,
     * solo los padres pueden editar (no el creador original).
     */
    protected function authorizeEdit(Person $person): void
    {
        $user = auth()->user();

        // Si es menor de edad y tiene padres registrados con cuenta,
        // solo los padres pueden editar (no el creador original)
        if ($person->is_minor_calculated && $this->relationships->minorHasRegisteredParents($person)) {
            // Los padres registrados pueden editar
            if ($user->person_id && $this->relationships->isParentOf($user->person_id, $person)) {
                return;
            }

            // El menor vinculado puede editar su propio perfil (si tiene cuenta)
            if ($user->person_id === $person->id) {
                return;
            }

            // Admin siempre puede
            if ($user->is_admin) {
                return;
            }

            abort(403, 'Solo los padres registrados pueden editar el perfil de un menor.');
        }

        // Para no-menores, lógica normal:

        // El creador puede editar
        if ($person->created_by === $user->id) {
            return;
        }

        // Usuario puede editar su propio perfil
        if ($user->person_id === $person->id) {
            return;
        }

        // Personas con consentimiento aprobado pueden editarse si estan vinculadas
        if ($person->consent_status === 'approved' && $person->user_id === $user->id) {
            return;
        }

        // Verificar permisos de edición otorgados (familia directa)
        if ($person->canBeEditedBy($user->id)) {
            return;
        }

        abort(403, 'No tienes permiso para editar esta persona.');
    }

    /**
     * Muestra formulario para reclamar una persona.
     */
    public function claimForm(Person $person)
    {
        $user = auth()->user();

        // Check suave de privacidad para flujo de claim
        if (!$person->canBeViewedForClaim($user)) {
            return back()->with('error', __('No tienes permiso para ver esta persona.'));
        }

        // Determinar si la persona actual del usuario es "dummy" (sin conexiones familiares)
        $currentPersonIsDummy = false;
        if ($user->person_id) {
            $currentPerson = $user->person;
            if ($currentPerson) {
                $hasFamily = $currentPerson->familiesAsChild()->exists()
                    || $currentPerson->familiesAsSpouse()->exists();
                $currentPersonIsDummy = !$hasFamily;
            }
        }

        // Admin o creador puede re-vincularse si su persona es dummy
        $canRelink = false;
        if ($user->person_id && !$person->user_id
            && ($user->is_admin || $person->created_by === $user->id)) {
            $canRelink = $currentPersonIsDummy;
        }

        // Usuario nuevo con persona dummy puede reclamar (envia solicitud)
        $canClaimWithDummy = $currentPersonIsDummy && !$person->user_id && !$canRelink;

        // Bloquear si tiene persona con relaciones familiares reales
        if ($user->person_id && !$currentPersonIsDummy && !$canRelink) {
            return back()->with('error', __('Ya tienes un perfil con relaciones familiares. Usa la opcion de fusionar perfiles si esta persona eres tu.'));
        }

        // No puede reclamar si la persona ya tiene usuario
        if ($person->user_id && !$canRelink) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Verificar si ya hay una solicitud pendiente
        $pendingClaim = Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            return back()->with('info', __('Ya tienes una solicitud pendiente para esta persona.'));
        }

        // Determinar quien revisara la solicitud
        $recipients = $this->resolveClaimRecipients($person);
        $reviewerLabel = $this->getReviewerLabel($person, $recipients);

        return view('persons.claim', compact('person', 'canRelink', 'canClaimWithDummy', 'reviewerLabel'));
    }

    /**
     * Envia solicitud para reclamar una persona.
     */
    public function sendClaimRequest(Request $request, Person $person)
    {
        $user = auth()->user();

        // La persona no debe tener usuario vinculado
        if ($person->user_id) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Determinar si persona actual es dummy
        $currentPersonIsDummy = false;
        $dummyPersonId = null;
        if ($user->person_id) {
            $currentPerson = $user->person;
            if ($currentPerson) {
                $hasFamily = $currentPerson->familiesAsChild()->exists()
                    || $currentPerson->familiesAsSpouse()->exists();
                $currentPersonIsDummy = !$hasFamily;
                if ($currentPersonIsDummy) {
                    $dummyPersonId = $currentPerson->id;
                }
            }
        }

        // Admin o creador puede re-vincularse si su persona es dummy
        $canRelink = false;
        if ($user->person_id && ($user->is_admin || $person->created_by === $user->id)) {
            $canRelink = $currentPersonIsDummy;
        }

        if ($canRelink) {
            // Desvincular persona anterior (dummy)
            $oldPerson = $user->person;
            if ($oldPerson) {
                $oldPerson->update(['user_id' => null]);
            }

            // Vincular nueva persona
            $user->update(['person_id' => $person->id]);
            $person->update([
                'user_id' => $user->id,
                'consent_status' => 'approved',
                'consent_responded_at' => now(),
            ]);

            // Eliminar persona dummy si no tiene datos importantes
            if ($oldPerson && !$oldPerson->familiesAsChild()->exists() && !$oldPerson->familiesAsSpouse()->exists()) {
                $oldPerson->delete();
            }

            ActivityLog::log('person_claimed', $user, $person);

            return redirect()->route('tree.view', $person)
                ->with('success', __('Tu cuenta ha sido vinculada a este perfil correctamente.'));
        }

        // Bloquear si tiene persona con relaciones familiares reales
        if ($user->person_id && !$currentPersonIsDummy) {
            return back()->with('error', __('Ya tienes un perfil con relaciones familiares. Usa la opcion de fusionar perfiles si esta persona eres tu.'));
        }

        // Verificar si ya hay solicitud pendiente
        $pendingClaim = Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            return back()->with('error', __('Ya tienes una solicitud pendiente para esta persona.'));
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        // Determinar destinatarios
        $recipientIds = $this->resolveClaimRecipients($person);

        if (empty($recipientIds)) {
            return back()->with('error', __('No se pudo determinar quien debe aprobar esta solicitud. Contacta al administrador.'));
        }

        // Crear mensaje de solicitud a cada destinatario
        foreach ($recipientIds as $recipientId) {
            Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'type' => 'person_claim',
                'subject' => __(':name solicita vincularse a :person', [
                    'name' => $user->person?->full_name ?? $user->email,
                    'person' => $person->full_name,
                ]),
                'body' => $request->input('message') ?? __('Solicito vincular mi cuenta con este perfil porque soy esta persona.'),
                'related_person_id' => $person->id,
                'claiming_person_id' => $dummyPersonId,
                'action_required' => true,
                'action_status' => 'pending',
                'created_at' => now(),
            ]);
        }

        ActivityLog::log('person_claim_requested', $user, $person);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Solicitud enviada. Sera revisada por el responsable del perfil.'));
    }

    /**
     * Verifica si el usuario puede reclamar una persona.
     */
    public function canClaim(Person $person): array
    {
        $user = auth()->user();

        $result = [
            'can_claim' => false,
            'reason' => null,
            'claim_type' => null,
        ];

        // La persona ya tiene usuario
        if ($person->user_id) {
            $result['reason'] = 'person_has_user';
            return $result;
        }

        // Verificar si la persona del usuario es dummy (sin conexiones familiares)
        $currentPersonIsDummy = false;
        if ($user->person_id) {
            $currentPerson = $user->person;
            if ($currentPerson) {
                $hasFamily = $currentPerson->familiesAsChild()->exists()
                    || $currentPerson->familiesAsSpouse()->exists();
                $currentPersonIsDummy = !$hasFamily;
            }
        }

        // Tiene persona con relaciones familiares reales
        if ($user->person_id && !$currentPersonIsDummy) {
            $result['reason'] = 'already_has_person_with_family';
            return $result;
        }

        // Hay solicitud pendiente
        $pendingClaim = Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            $result['reason'] = 'pending_claim';
            return $result;
        }

        $result['can_claim'] = true;
        $result['claim_type'] = $currentPersonIsDummy ? 'dummy_swap' : 'fresh_claim';
        return $result;
    }

    // ========================================================================
    // DECLARACION DE PARENTESCO (v2.6.0)
    // ========================================================================

    /**
     * Muestra formulario para declarar relacion familiar con una persona.
     */
    public function relationshipClaimForm(Person $person)
    {
        $user = auth()->user();

        if (!$user->person_id) {
            return redirect()->route('dashboard')
                ->with('error', __('Primero debes tener un perfil asociado.'));
        }

        if ($user->person_id === $person->id) {
            return back()->with('error', __('No puedes declarar relacion contigo mismo.'));
        }

        // Check suave de privacidad
        if (!$person->canBeViewedForClaim($user)) {
            return back()->with('error', __('No tienes permiso para ver esta persona.'));
        }

        // Verificar si ya hay solicitud pendiente
        $pendingClaim = Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'relationship_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            return back()->with('info', __('Ya tienes una solicitud de relacion pendiente para esta persona.'));
        }

        $userPerson = $user->person;
        $recipients = $this->resolveClaimRecipients($person);
        $reviewerLabel = $this->getReviewerLabel($person, $recipients);

        return view('persons.relationship-claim', compact('person', 'userPerson', 'reviewerLabel'));
    }

    /**
     * Envia solicitud de declaracion de parentesco.
     */
    public function sendRelationshipClaim(Request $request, Person $person)
    {
        $user = auth()->user();

        if (!$user->person_id) {
            return back()->with('error', __('Primero debes tener un perfil asociado.'));
        }

        if ($user->person_id === $person->id) {
            return back()->with('error', __('No puedes declarar relacion contigo mismo.'));
        }

        $validated = $request->validate([
            'relationship_type' => ['required', 'in:father,mother,child,sibling,spouse'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        // Verificar solicitud pendiente
        $pendingClaim = Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'relationship_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            return back()->with('error', __('Ya tienes una solicitud pendiente para esta persona.'));
        }

        $userPerson = $user->person;
        $relationLabels = [
            'father' => __('padre'),
            'mother' => __('madre'),
            'child' => __('hijo/a'),
            'sibling' => __('hermano/a'),
            'spouse' => __('conyuge'),
        ];
        $relationLabel = $relationLabels[$validated['relationship_type']] ?? $validated['relationship_type'];

        $recipientIds = $this->resolveClaimRecipients($person);

        if (empty($recipientIds)) {
            return back()->with('error', __('No se pudo determinar quien debe aprobar esta solicitud. Contacta al administrador.'));
        }

        foreach ($recipientIds as $recipientId) {
            Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'type' => 'relationship_claim',
                'subject' => __(':name declara ser :relation de :person', [
                    'name' => $userPerson->full_name,
                    'relation' => $relationLabel,
                    'person' => $person->full_name,
                ]),
                'body' => $validated['message'] ?? __('Declaro que :person es mi :relation.', [
                    'person' => $person->full_name,
                    'relation' => $relationLabel,
                ]),
                'related_person_id' => $person->id,
                'metadata' => [
                    'relationship_type' => $validated['relationship_type'],
                    'claiming_person_id' => $userPerson->id,
                ],
                'action_required' => true,
                'action_status' => 'pending',
                'created_at' => now(),
            ]);
        }

        ActivityLog::log('relationship_claim_requested', $user, $person, [
            'relationship_type' => $validated['relationship_type'],
        ]);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Solicitud de relacion enviada. Sera revisada por el responsable del perfil.'));
    }

    // ========================================================================
    // HELPERS PARA CLAIMS (v2.6.0)
    // ========================================================================

    /**
     * Determina quien debe recibir una notificacion de claim para una persona.
     * Prioridad: usuario vinculado > creador > todos los admins.
     */
    protected function resolveClaimRecipients(Person $person): array
    {
        // 1. Si la persona tiene usuario vinculado
        if ($person->user_id) {
            $linkedUser = User::find($person->user_id);
            if ($linkedUser) {
                return [$linkedUser->id];
            }
        }

        // 2. Si la persona tiene creador
        if ($person->created_by) {
            $creator = User::find($person->created_by);
            if ($creator) {
                return [$creator->id];
            }
        }

        // 3. Fallback: todos los administradores
        return User::where('is_admin', true)->pluck('id')->toArray();
    }

    /**
     * Retorna texto legible de quien revisara la solicitud.
     */
    protected function getReviewerLabel(Person $person, array $recipientIds): string
    {
        if ($person->user_id && in_array($person->user_id, $recipientIds)) {
            $linkedUser = User::find($person->user_id);
            return $linkedUser ? $linkedUser->email : __('el usuario vinculado');
        }

        if ($person->created_by && in_array($person->created_by, $recipientIds)) {
            $creator = User::find($person->created_by);
            return $creator ? $creator->email : __('el creador del perfil');
        }

        return __('los administradores del sitio');
    }

    /**
     * Muestra formulario para solicitar fusion de personas.
     */
    public function mergeForm(Person $person)
    {
        $user = auth()->user();

        // Verificar que el usuario puede ver la persona
        $this->authorizeView($person);

        // Debe tener una persona asociada para fusionar
        if (!$user->person_id) {
            return back()->with('error', __('Primero debes tener un perfil asociado a tu cuenta.'));
        }

        // No puede fusionar consigo mismo
        if ($user->person_id === $person->id) {
            return back()->with('error', __('No puedes fusionar tu propio perfil.'));
        }

        // La persona no debe tener usuario asociado
        if ($person->user_id) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Verificar si ya hay una solicitud pendiente
        $pendingMerge = \App\Models\Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_merge')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingMerge) {
            return back()->with('info', __('Ya tienes una solicitud de fusion pendiente para esta persona.'));
        }

        // Cargar el creador de la persona y la persona del usuario
        $creator = \App\Models\User::find($person->created_by);
        $userPerson = $user->person;

        // Validaciones adicionales para mostrar advertencias
        $warnings = [];
        $cannotMerge = false;
        $cannotMergeReason = null;

        // Verificar si la persona está marcada como difunta
        if (!$person->is_living) {
            $cannotMerge = true;
            $cannotMergeReason = __('No puedes fusionarte con una persona fallecida. Esta persona está registrada como difunta.');
        }

        // Verificar si nació hace más de 100 años
        if ($person->birth_date && $person->birth_date->diffInYears(now()) > 100) {
            $cannotMerge = true;
            $cannotMergeReason = __('No puedes fusionarte con esta persona. Su fecha de nacimiento indica que tendría más de 100 años.');
        }

        // Verificar similitud de nombres
        $namesSimilar = $this->relationships->areNamesSimilar($userPerson, $person);
        if (!$namesSimilar) {
            $warnings[] = [
                'type' => 'name_mismatch',
                'message' => __('Los nombres no coinciden. Tu nombre es ":user" y el de esta persona es ":person". ¿Estás seguro de que son la misma persona?', [
                    'user' => $userPerson->full_name,
                    'person' => $person->full_name,
                ]),
            ];
        }

        // Advertencia general sobre pérdida de datos
        $warnings[] = [
            'type' => 'data_loss',
            'message' => __('La fusión puede provocar pérdida de datos. Los datos del perfil eliminado que no puedan transferirse se perderán permanentemente.'),
        ];

        return view('persons.merge', compact('person', 'creator', 'userPerson', 'warnings', 'cannotMerge', 'cannotMergeReason', 'namesSimilar'));
    }

    /**
     * Envia solicitud para fusionar personas.
     */
    public function sendMergeRequest(Request $request, Person $person)
    {
        $user = auth()->user();

        // Validaciones
        if (!$user->person_id) {
            return back()->with('error', __('Primero debes tener un perfil asociado.'));
        }

        if ($user->person_id === $person->id) {
            return back()->with('error', __('No puedes fusionar tu propio perfil.'));
        }

        if ($person->user_id) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Verificar solicitud pendiente
        $pendingMerge = \App\Models\Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_merge')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingMerge) {
            return back()->with('error', __('Ya tienes una solicitud pendiente.'));
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $userPerson = $user->person;

        // Crear mensaje de solicitud al creador de la persona
        \App\Models\Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $person->created_by,
            'type' => 'person_merge',
            'subject' => __(':name solicita fusionar :person con :target', [
                'name' => $userPerson->full_name ?? $user->email,
                'person' => $person->full_name,
                'target' => $userPerson->full_name,
            ]),
            'body' => $request->input('message') ?? __('Solicito fusionar este perfil con el mio porque somos la misma persona.'),
            'related_person_id' => $person->id,
            'action_required' => true,
            'action_status' => 'pending',
            'created_at' => now(),
        ]);

        ActivityLog::log('person_merge_requested', $user, $person, [
            'target_person_id' => $user->person_id,
        ]);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Solicitud de fusion enviada. El creador del perfil debe aprobarla.'));
    }

    /**
     * Muestra formulario para agregar persona existente a mi árbol.
     */
    public function addToTreeForm(Person $person)
    {
        $user = auth()->user();

        // Validaciones
        if (!$user->person_id) {
            return redirect()->route('persons.index')
                ->with('error', __('Primero debes tener un perfil asociado para agregar personas a tu árbol.'));
        }

        if ($user->person_id === $person->id) {
            return redirect()->route('persons.show', $person)
                ->with('info', __('Esta persona ya eres tú.'));
        }

        $userPerson = $user->person;

        // Verificar si ya están relacionados
        $alreadyRelated = $this->relationships->arePersonsRelated($userPerson, $person);

        if ($alreadyRelated) {
            return redirect()->route('persons.show', $person)
                ->with('info', __('Esta persona ya está en tu árbol familiar.'));
        }

        return view('persons.add-to-tree', compact('person', 'userPerson'));
    }

    /**
     * Procesa el formulario para agregar persona existente a mi árbol.
     */
    public function addToTreeStore(Request $request, Person $person)
    {
        $user = auth()->user();

        // Validaciones
        if (!$user->person_id) {
            return redirect()->route('persons.index')
                ->with('error', __('Primero debes tener un perfil asociado.'));
        }

        if ($user->person_id === $person->id) {
            return back()->with('error', __('No puedes agregarte a ti mismo.'));
        }

        $validated = $request->validate([
            'relationship' => ['required', 'in:father,mother,child,sibling,spouse,other'],
        ]);

        $userPerson = $user->person;

        try {
            switch ($validated['relationship']) {
                case 'father':
                    if ($person->gender !== 'M') {
                        return back()->with('error', __('El padre debe ser de género masculino.'));
                    }
                    $this->relationships->addParentRelationship($userPerson, $person, 'M');
                    break;

                case 'mother':
                    if ($person->gender !== 'F') {
                        return back()->with('error', __('La madre debe ser de género femenino.'));
                    }
                    $this->relationships->addParentRelationship($userPerson, $person, 'F');
                    break;

                case 'child':
                    $this->relationships->addChildRelationship($userPerson, $person);
                    break;

                case 'sibling':
                    $this->relationships->addSiblingRelationship($userPerson, $person);
                    break;

                case 'spouse':
                    $this->relationships->addSpouseRelationship($userPerson, $person, [
                        'family_status' => 'married',
                    ]);
                    break;

                case 'other':
                    // Redirigir a la pantalla de relaciones para agregar relación específica
                    return redirect()->route('persons.relationships', $userPerson)
                        ->with('info', __('Selecciona el tipo de relación específica con :name.', ['name' => $person->full_name]));
            }

            ActivityLog::log('person_added_to_tree', $user, $person, [
                'relationship' => $validated['relationship'],
                'user_person_id' => $userPerson->id,
            ]);

            return redirect()->route('tree.view', $userPerson)
                ->with('success', __(':name ha sido agregado/a a tu árbol familiar.', ['name' => $person->full_name]));

        } catch (\Exception $e) {
            return back()->with('error', __('Error al agregar la relación: ') . $e->getMessage());
        }
    }

    /**
     * Muestra formulario para solicitar acceso a editar familia directa.
     */
    public function familyEditAccessForm()
    {
        $user = auth()->user();

        // Debe tener una persona asociada
        if (!$user->person_id) {
            return redirect()->route('dashboard')
                ->with('error', __('Primero debes vincular tu cuenta con un perfil del árbol genealógico.'));
        }

        $person = $user->person;
        $directFamily = $person->directFamily;

        // Filtrar miembros de familia que el usuario NO puede editar actualmente
        $editableIds = $user->editablePersonIds;
        $familyToRequest = $directFamily->filter(function ($member) use ($editableIds) {
            return !in_array($member['person']->id, $editableIds);
        });

        // Obtener solicitudes pendientes
        $pendingRequests = \App\Models\Message::where('sender_id', $user->id)
            ->where('type', 'family_edit_request')
            ->where('action_status', 'pending')
            ->pluck('related_person_id')
            ->toArray();

        return view('persons.family-edit-access', compact('person', 'familyToRequest', 'pendingRequests', 'editableIds'));
    }

    /**
     * Envía solicitudes para editar miembros de familia directa.
     */
    public function sendFamilyEditAccessRequest(Request $request)
    {
        $user = auth()->user();

        if (!$user->person_id) {
            return back()->with('error', __('Primero debes vincular tu cuenta con un perfil.'));
        }

        $request->validate([
            'person_ids' => ['required', 'array', 'min:1'],
            'person_ids.*' => ['required', 'integer', 'exists:persons,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $person = $user->person;
        $directFamilyIds = $person->directFamilyIds;
        $requestedIds = $request->input('person_ids');
        $customMessage = $request->input('message');

        $sentCount = 0;
        $errors = [];

        foreach ($requestedIds as $personId) {
            // Verificar que es miembro de familia directa
            if (!in_array($personId, $directFamilyIds)) {
                $errors[] = __('Persona :id no es parte de tu familia directa.', ['id' => $personId]);
                continue;
            }

            $targetPerson = Person::find($personId);
            if (!$targetPerson) {
                continue;
            }

            // Verificar que no puede editarla ya
            if ($targetPerson->canBeEditedBy($user->id)) {
                continue; // Ya tiene permiso, saltar
            }

            // Verificar si ya hay solicitud pendiente
            $pendingExists = \App\Models\Message::where('sender_id', $user->id)
                ->where('related_person_id', $personId)
                ->where('type', 'family_edit_request')
                ->where('action_status', 'pending')
                ->exists();

            if ($pendingExists) {
                continue; // Ya hay solicitud pendiente
            }

            // Determinar el tipo de relación
            $relationship = $this->relationships->determineRelationship($person, $targetPerson);

            // Crear mensaje de solicitud al creador de la persona
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $targetPerson->created_by,
                'type' => 'family_edit_request',
                'subject' => __(':name solicita permiso para editar a :person', [
                    'name' => $person->full_name,
                    'person' => $targetPerson->full_name,
                ]),
                'body' => $customMessage ?? __(':name es mi :relationship y solicito permiso para poder editar su información.', [
                    'name' => $targetPerson->full_name,
                    'relationship' => $relationship['label'],
                ]),
                'related_person_id' => $targetPerson->id,
                'action_required' => true,
                'action_status' => 'pending',
                'metadata' => json_encode([
                    'requester_person_id' => $person->id,
                    'relationship_type' => $relationship['type'],
                ]),
            ]);

            ActivityLog::log('family_edit_access_requested', $user, $targetPerson, [
                'relationship_type' => $relationship['type'],
            ]);

            $sentCount++;
        }

        if ($sentCount > 0) {
            return redirect()->route('persons.family-edit-access')
                ->with('success', __('Se enviaron :count solicitudes de acceso.', ['count' => $sentCount]));
        }

        return back()->with('info', __('No se enviaron nuevas solicitudes. Es posible que ya tengas solicitudes pendientes o permisos activos.'));
    }

    /**
     * Busca personas para agregar como familiar (AJAX).
     */
    public function searchForRelationship(Request $request)
    {
        $query = $request->input('q');
        $excludeId = $request->input('exclude');

        if (!$query || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = auth()->user();

        // Buscar personas que coincidan
        $persons = Person::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('patronymic', 'like', "%{$query}%")
                  ->orWhere('matronymic', 'like', "%{$query}%")
                  ->orWhere('nickname', 'like', "%{$query}%");
            })
            ->when($excludeId, function ($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            })
            ->orderBy('patronymic')
            ->orderBy('first_name')
            ->limit(15)
            ->get();

        $results = $persons->map(function ($person) use ($user) {
            // Determinar si el usuario puede editar esta persona
            $canEdit = $person->canBeEditedBy($user->id);
            $isOwn = $person->created_by === $user->id;
            $requiresAuth = !$canEdit && !$isOwn;

            return [
                'id' => $person->id,
                'name' => $person->full_name,
                'birth_year' => $person->birth_date ? $person->birth_date->format('Y') : null,
                'photo' => $person->photo_path ? asset('storage/' . $person->photo_path) : null,
                'gender' => $person->gender,
                'is_living' => $person->is_living,
                'can_edit' => $canEdit,
                'is_own' => $isOwn,
                'requires_authorization' => $requiresAuth,
                'creator_id' => $person->created_by,
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Agrega una relación familiar solicitando autorización si es necesario.
     */
    public function storeRelationshipWithAuth(Request $request, Person $person)
    {
        $this->authorizeEdit($person);

        $validated = $request->validate([
            'relationship_type' => ['required', 'in:spouse,parent,father,mother,child,sibling'],
            'related_person_id' => ['required', 'exists:persons,id', 'different:' . $person->id],
            'family_status' => ['nullable', 'in:married,divorced,widowed,separated,partners,annulled'],
            'marriage_date' => ['nullable', 'date'],
            'request_authorization' => ['boolean'],
        ]);

        $relatedPerson = Person::findOrFail($validated['related_person_id']);
        $user = auth()->user();

        // Verificar si el usuario puede vincular con esta persona
        $canEdit = $relatedPerson->canBeEditedBy($user->id);
        $isOwn = $relatedPerson->created_by === $user->id;

        // Si no puede editar y no es propia, verificar si solicita autorización
        if (!$canEdit && !$isOwn) {
            if (!($validated['request_authorization'] ?? false)) {
                return back()->with('error', __('No tienes permiso para vincular con esta persona. Solicita autorización.'));
            }

            // Crear solicitud de autorización para vincular
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $relatedPerson->created_by,
                'type' => 'family_edit_request',
                'subject' => __(':name solicita vincular a :person como :relation de :target', [
                    'name' => $user->person?->full_name ?? $user->email,
                    'person' => $relatedPerson->full_name,
                    'relation' => __($validated['relationship_type']),
                    'target' => $person->full_name,
                ]),
                'body' => __('Solicito permiso para establecer que :person es :relation de :target.', [
                    'person' => $relatedPerson->full_name,
                    'relation' => __($validated['relationship_type']),
                    'target' => $person->full_name,
                ]),
                'related_person_id' => $relatedPerson->id,
                'action_required' => true,
                'action_status' => 'pending',
                'metadata' => json_encode([
                    'link_request' => true,
                    'source_person_id' => $person->id,
                    'relationship_type' => $validated['relationship_type'],
                    'family_status' => $validated['family_status'] ?? null,
                    'marriage_date' => $validated['marriage_date'] ?? null,
                ]),
            ]);

            ActivityLog::log('relationship_authorization_requested', $user, $person, [
                'related_person_id' => $relatedPerson->id,
                'relationship_type' => $validated['relationship_type'],
            ]);

            return back()->with('info', __('Se envió una solicitud de autorización al creador de :name.', [
                'name' => $relatedPerson->full_name,
            ]));
        }

        // Si puede editar, proceder normalmente
        return $this->storeRelationship($request, $person);
    }

    /**
     * Solicitar permiso de edicion para una persona individual.
     */
    public function requestEditPermission(Person $person)
    {
        $user = auth()->user();

        if (!$user->person_id) {
            return back()->with('error', __('Primero debes vincular tu cuenta con un perfil del arbol.'));
        }

        // Si ya puede editar, no necesita solicitar
        if ($person->canBeEditedBy($user->id) || $user->is_admin) {
            return back()->with('info', __('Ya tienes permiso para editar a esta persona.'));
        }

        // Verificar si ya hay solicitud pendiente
        $pendingExists = \App\Models\Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'family_edit_request')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingExists) {
            return back()->with('info', __('Ya tienes una solicitud pendiente para esta persona.'));
        }

        // Determinar destinatario: usuario vinculado > creador
        $recipientId = $person->user_id ?? $person->created_by;

        // No puede solicitarse permiso a sí mismo
        if ($recipientId === $user->id) {
            return back()->with('error', __('No puedes solicitar permiso a ti mismo.'));
        }

        $userPerson = $user->person;
        $relationship = $this->relationships->determineRelationship($userPerson, $person);

        \App\Models\Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'type' => 'family_edit_request',
            'subject' => __(':name solicita permiso para editar a :person', [
                'name' => $userPerson->full_name,
                'person' => $person->full_name,
            ]),
            'body' => __(':name es mi :relationship y solicito permiso para poder editar su informacion.', [
                'name' => $person->full_name,
                'relationship' => $relationship['label'],
            ]),
            'related_person_id' => $person->id,
            'action_required' => true,
            'action_status' => 'pending',
            'metadata' => json_encode([
                'requester_person_id' => $userPerson->id,
                'relationship_type' => $relationship['type'],
            ]),
        ]);

        ActivityLog::log('family_edit_access_requested', $user, $person, [
            'relationship_type' => $relationship['type'],
        ]);

        return back()->with('success', __('Se ha enviado tu solicitud de permiso de edicion.'));
    }
}
