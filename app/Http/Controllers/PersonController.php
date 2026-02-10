<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Controllers\InvitationController;

class PersonController extends Controller
{
    /**
     * Muestra el listado de personas.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Person::query();

        // Obtener IDs de familia directa y extendida si el usuario tiene persona asociada
        $directFamilyIds = [];
        $extendedFamilyIds = [];
        if ($user->person_id && $user->person) {
            $directFamilyIds = $user->person->directFamilyIds;
            $extendedFamilyIds = $user->person->extendedFamilyIds;
        }

        // Filtrar por privacidad según los 4 niveles
        $query->where(function ($q) use ($user, $directFamilyIds, $extendedFamilyIds) {
            // Personas creadas por el usuario (siempre visibles)
            $q->where('created_by', $user->id);

            // Usuario puede ver su propio perfil
            if ($user->person_id) {
                $q->orWhere('id', $user->person_id);
            }

            // Personas con nivel 'community' (visibles para todos los registrados)
            $q->orWhere('privacy_level', 'community');

            // Personas con nivel 'selected_users' o 'extended_family' que son familia extendida
            if (!empty($extendedFamilyIds)) {
                $q->orWhere(function ($subQ) use ($extendedFamilyIds) {
                    $subQ->whereIn('privacy_level', ['extended_family', 'selected_users'])
                         ->whereIn('id', $extendedFamilyIds);
                });
            }

            // Personas con nivel 'direct_family' que son familia directa del usuario
            if (!empty($directFamilyIds)) {
                $q->orWhere(function ($subQ) use ($directFamilyIds) {
                    $subQ->where('privacy_level', 'direct_family')
                         ->whereIn('id', $directFamilyIds);
                });
            }

            // Personas con nivel 'selected_users' aparecen en la lista (nombre visible)
            // para permitir que envien solicitudes de acceso
            $q->orWhere('privacy_level', 'selected_users');
        });

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
                        $this->addChildRelationship($relatedPerson, $person, $familyId);
                        break;

                    case 'father':
                        // La nueva persona es el padre del relacionado
                        $this->addParentRelationship($relatedPerson, $person, 'M');
                        break;

                    case 'mother':
                        // La nueva persona es la madre del relacionado
                        $this->addParentRelationship($relatedPerson, $person, 'F');
                        break;

                    case 'parent':
                        // La nueva persona es padre/madre del relacionado (usa genero de la persona)
                        $this->addParentRelationship($relatedPerson, $person);
                        break;

                    case 'sibling':
                        // La nueva persona es hermano del relacionado
                        $this->addSiblingRelationship($relatedPerson, $person);
                        break;

                    case 'spouse':
                        // La nueva persona es conyuge del relacionado
                        $this->addSpouseRelationship($relatedPerson, $person, [
                            'family_status' => 'married',
                        ]);
                        break;
                }

                ActivityLog::log('relationship_created', $user, $person, [
                    'related_person_id' => $relatedPerson->id,
                    'type' => $relation,
                ]);

                // Redirigir al arbol del familiar relacionado
                $successMessage = __('Persona creada y agregada al arbol correctamente.');
                if ($invitationMessage) {
                    $successMessage .= ' ' . $invitationMessage;
                }
                return redirect()->route('tree.view', $relatedPerson)
                    ->with('success', $successMessage);
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
        $this->authorizeView($person);

        $person->load([
            'familiesAsHusband.wife',
            'familiesAsHusband.children',
            'familiesAsWife.husband',
            'familiesAsWife.children',
            'familiesAsChild.husband',
            'familiesAsChild.wife',
            'media',
            'events',
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

        // Eliminar foto si existe
        if ($person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
        }

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

        // Eliminar foto anterior
        if ($person->photo_path) {
            Storage::disk('public')->delete($person->photo_path);
        }

        $path = $request->file('photo')->store('photos/persons', 'public');
        $person->update(['photo_path' => $path, 'updated_by' => $user->id]);

        ActivityLog::log('person_photo_updated', $user, $person);

        // Responder JSON para peticiones AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'photo' => asset('storage/' . $path),
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
            $person->update(['photo_path' => null, 'updated_by' => $user->id]);

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
                $this->addSpouseRelationship($person, $relatedPerson, $validated);
                break;
            case 'parent':
                $this->addParentRelationship($person, $relatedPerson, $parentGender);
                break;
            case 'child':
                $this->addChildRelationship($person, $relatedPerson);
                break;
            case 'sibling':
                $this->addSiblingRelationship($person, $relatedPerson);
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
     * los 4 niveles de privacidad: private, family, community, public.
     * Usa redirect en lugar de abort(403) para evitar que el middleware
     * de autenticacion redirija al login (bug de logout).
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

            abort(redirect($redirectUrl)->with('error', __('No tienes permiso para ver esta persona.')));
        }
    }

    /**
     * Verifica si el usuario puede editar la persona.
     */
    protected function authorizeEdit(Person $person): void
    {
        $user = auth()->user();

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
     * Agrega relacion de conyuge.
     * Verifica que no exista ya una familia con los mismos conyuges para evitar duplicados.
     */
    protected function addSpouseRelationship(Person $person, Person $related, array $data): void
    {
        // Determinar quien es esposo/esposa basado en genero
        $husband = $person->gender === 'M' ? $person : $related;
        $wife = $person->gender === 'M' ? $related : $person;

        // Si ambos son del mismo genero, usar el orden de parametros
        if ($person->gender === $related->gender) {
            $husband = $person;
            $wife = $related;
        }

        // Verificar que no exista ya una familia con estos conyuges
        $existingFamily = Family::where(function ($q) use ($husband, $wife) {
            $q->where(function ($inner) use ($husband, $wife) {
                $inner->where('husband_id', $husband->id)
                      ->where('wife_id', $wife->id);
            })->orWhere(function ($inner) use ($husband, $wife) {
                $inner->where('husband_id', $wife->id)
                      ->where('wife_id', $husband->id);
            });
        })->first();

        if ($existingFamily) {
            // Si ya existe, actualizar datos si se proporcionaron
            $updateData = [];
            if (!empty($data['marriage_date']) && !$existingFamily->marriage_date) {
                $updateData['marriage_date'] = $data['marriage_date'];
            }
            if (!empty($data['family_status'])) {
                $updateData['status'] = $data['family_status'];
            }
            if (!empty($updateData)) {
                $existingFamily->update($updateData);
            }
            return;
        }

        // Verificar si alguno tiene una familia sin conyuge (familia incompleta)
        $incompleteFamilyHusband = Family::where('husband_id', $husband->id)
            ->whereNull('wife_id')
            ->first();

        if ($incompleteFamilyHusband) {
            $incompleteFamilyHusband->update([
                'wife_id' => $wife->id,
                'marriage_date' => $data['marriage_date'] ?? $incompleteFamilyHusband->marriage_date,
                'status' => $data['family_status'] ?? $incompleteFamilyHusband->status,
            ]);
            return;
        }

        $incompleteFamilyWife = Family::where('wife_id', $wife->id)
            ->whereNull('husband_id')
            ->first();

        if ($incompleteFamilyWife) {
            $incompleteFamilyWife->update([
                'husband_id' => $husband->id,
                'marriage_date' => $data['marriage_date'] ?? $incompleteFamilyWife->marriage_date,
                'status' => $data['family_status'] ?? $incompleteFamilyWife->status,
            ]);
            return;
        }

        Family::create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'marriage_date' => $data['marriage_date'] ?? null,
            'status' => $data['family_status'] ?? 'married',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Agrega relacion de padre/madre.
     *
     * @param Person $child La persona que sera el hijo
     * @param Person $parent La persona que sera el padre/madre
     * @param string|null $forceGender Si se especifica ('M' o 'F'), usa este genero en lugar del genero de la persona
     */
    protected function addParentRelationship(Person $child, Person $parent, ?string $forceGender = null): void
    {
        // Usar el genero forzado si se proporciona, si no usar el de la persona
        $gender = $forceGender ?? $parent->gender;

        // Buscar si ya existe una familia para el hijo
        $family = $child->familiesAsChild()->first();

        if ($family) {
            // Agregar al padre/madre existente
            if ($gender === 'M' && !$family->husband_id) {
                $family->update(['husband_id' => $parent->id]);
            } elseif ($gender === 'F' && !$family->wife_id) {
                $family->update(['wife_id' => $parent->id]);
            }
        } else {
            // Crear nueva familia
            $family = Family::create([
                'husband_id' => $gender === 'M' ? $parent->id : null,
                'wife_id' => $gender === 'F' ? $parent->id : null,
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $child->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Agrega relacion de hijo.
     */
    protected function addChildRelationship(Person $parent, Person $child, ?int $familyId = null): void
    {
        // Si se especifica una familia, usarla
        if ($familyId) {
            $family = Family::find($familyId);
        }

        // Si no hay familia especificada o no se encontro, buscar una existente
        if (!isset($family) || !$family) {
            $family = Family::where(function ($q) use ($parent) {
                $q->where('husband_id', $parent->id)->orWhere('wife_id', $parent->id);
            })->first();
        }

        if (!$family) {
            // Crear nueva familia
            $family = Family::create([
                'husband_id' => $parent->gender === 'M' ? $parent->id : null,
                'wife_id' => $parent->gender === 'F' ? $parent->id : null,
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);
        }

        // Agregar hijo si no existe
        if (!FamilyChild::where('family_id', $family->id)->where('person_id', $child->id)->exists()) {
            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $child->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Agrega relacion de hermano.
     */
    protected function addSiblingRelationship(Person $person, Person $sibling): void
    {
        // Buscar familia del primer hermano
        $family = $person->familiesAsChild()->first();

        if ($family) {
            // Agregar hermano a la misma familia
            if (!FamilyChild::where('family_id', $family->id)->where('person_id', $sibling->id)->exists()) {
                FamilyChild::create([
                    'family_id' => $family->id,
                    'person_id' => $sibling->id,
                    'relationship_type' => 'biological',
                ]);
            }
        } else {
            // Crear familia con ambos como hijos
            $family = Family::create([
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $person->id,
                'relationship_type' => 'biological',
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $sibling->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Muestra formulario para reclamar una persona.
     */
    public function claimForm(Person $person)
    {
        $user = auth()->user();

        // Verificar que el usuario puede ver la persona
        $this->authorizeView($person);

        // No puede reclamar si ya tiene persona asociada
        if ($user->person_id) {
            return back()->with('error', __('Ya tienes un perfil asociado a tu cuenta.'));
        }

        // No puede reclamar si la persona ya tiene usuario
        if ($person->user_id) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Verificar si ya hay una solicitud pendiente
        $pendingClaim = \App\Models\Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            return back()->with('info', __('Ya tienes una solicitud pendiente para esta persona.'));
        }

        // Cargar el creador de la persona
        $creator = \App\Models\User::find($person->created_by);

        return view('persons.claim', compact('person', 'creator'));
    }

    /**
     * Envia solicitud para reclamar una persona.
     */
    public function sendClaimRequest(Request $request, Person $person)
    {
        $user = auth()->user();

        // Validaciones
        if ($user->person_id) {
            return back()->with('error', __('Ya tienes un perfil asociado a tu cuenta.'));
        }

        if ($person->user_id) {
            return back()->with('error', __('Esta persona ya esta vinculada a otra cuenta.'));
        }

        // Verificar si ya hay solicitud pendiente
        $pendingClaim = \App\Models\Message::where('sender_id', $user->id)
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

        // Crear mensaje de solicitud al creador de la persona
        \App\Models\Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $person->created_by,
            'type' => 'person_claim',
            'subject' => __(':name solicita vincularse a :person', [
                'name' => $user->person?->full_name ?? $user->email,
                'person' => $person->full_name,
            ]),
            'body' => $request->input('message') ?? __('Solicito vincular mi cuenta con este perfil porque soy esta persona.'),
            'related_person_id' => $person->id,
            'action_required' => true,
            'action_status' => 'pending',
            'created_at' => now(),
        ]);

        ActivityLog::log('person_claim_requested', $user, $person);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Solicitud enviada. El creador del perfil debe aprobarla.'));
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
        ];

        // Ya tiene persona asociada
        if ($user->person_id) {
            $result['reason'] = 'already_has_person';
            return $result;
        }

        // La persona ya tiene usuario
        if ($person->user_id) {
            $result['reason'] = 'person_has_user';
            return $result;
        }

        // Hay solicitud pendiente
        $pendingClaim = \App\Models\Message::where('sender_id', $user->id)
            ->where('related_person_id', $person->id)
            ->where('type', 'person_claim')
            ->where('action_status', 'pending')
            ->exists();

        if ($pendingClaim) {
            $result['reason'] = 'pending_claim';
            return $result;
        }

        $result['can_claim'] = true;
        return $result;
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
        $namesSimilar = $this->areNamesSimilar($userPerson, $person);
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
     * Verifica si dos personas tienen nombres similares.
     */
    protected function areNamesSimilar(Person $person1, Person $person2): bool
    {
        // Comparar nombres (case insensitive)
        $name1 = mb_strtolower(trim($person1->first_name));
        $name2 = mb_strtolower(trim($person2->first_name));

        // Nombres iguales
        if ($name1 === $name2) {
            return true;
        }

        // Similitud de Levenshtein (tolerancia de 2 caracteres)
        if (levenshtein($name1, $name2) <= 2) {
            return true;
        }

        // Uno contiene al otro (para nombres compuestos)
        if (str_contains($name1, $name2) || str_contains($name2, $name1)) {
            return true;
        }

        // Comparar apellidos
        $surname1 = mb_strtolower(trim($person1->patronymic));
        $surname2 = mb_strtolower(trim($person2->patronymic));

        if ($surname1 !== $surname2 && levenshtein($surname1, $surname2) > 2) {
            return false;
        }

        return false;
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
        $alreadyRelated = $this->arePersonsRelated($userPerson, $person);

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
                    $this->addParentRelationship($userPerson, $person, 'M');
                    break;

                case 'mother':
                    if ($person->gender !== 'F') {
                        return back()->with('error', __('La madre debe ser de género femenino.'));
                    }
                    $this->addParentRelationship($userPerson, $person, 'F');
                    break;

                case 'child':
                    $this->addChildRelationship($userPerson, $person);
                    break;

                case 'sibling':
                    $this->addSiblingRelationship($userPerson, $person);
                    break;

                case 'spouse':
                    $this->addSpouseRelationship($userPerson, $person, [
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
     * Verifica si dos personas ya están relacionadas en el árbol.
     */
    protected function arePersonsRelated(Person $person1, Person $person2): bool
    {
        // Verificar si person2 es padre de person1
        if ($person1->father && $person1->father->id === $person2->id) return true;
        if ($person1->mother && $person1->mother->id === $person2->id) return true;

        // Verificar si person2 es hijo de person1
        if ($person1->children->contains('id', $person2->id)) return true;

        // Verificar si son hermanos
        if ($person1->siblings->contains('id', $person2->id)) return true;

        // Verificar si son cónyuges
        if ($person1->spouses->contains('id', $person2->id)) return true;

        return false;
    }

    /**
     * Ejecuta la fusion de dos personas.
     * La persona $source se fusiona EN $target (target conserva su ID).
     */
    public static function mergePersons(Person $source, Person $target): array
    {
        $merged = [
            'relationships' => 0,
            'media' => 0,
            'events' => 0,
            'fields' => [],
        ];

        // 1. Transferir relaciones familiares donde source es padre/madre
        $familiesAsHusband = Family::where('husband_id', $source->id)->get();
        foreach ($familiesAsHusband as $family) {
            // Verificar que target no sea ya el esposo de esa familia
            if ($family->husband_id !== $target->id) {
                $family->update(['husband_id' => $target->id]);
                $merged['relationships']++;
            }
        }

        $familiesAsWife = Family::where('wife_id', $source->id)->get();
        foreach ($familiesAsWife as $family) {
            if ($family->wife_id !== $target->id) {
                $family->update(['wife_id' => $target->id]);
                $merged['relationships']++;
            }
        }

        // 2. Transferir relaciones como hijo
        $familyChildren = FamilyChild::where('person_id', $source->id)->get();
        foreach ($familyChildren as $fc) {
            // Verificar que target no sea ya hijo de esa familia
            $exists = FamilyChild::where('family_id', $fc->family_id)
                ->where('person_id', $target->id)
                ->exists();
            if (!$exists) {
                $fc->update(['person_id' => $target->id]);
                $merged['relationships']++;
            } else {
                $fc->delete(); // Eliminar duplicado
            }
        }

        // 3. Transferir media (usa relacion polimorfica mediable_type/mediable_id)
        if (class_exists(\App\Models\Media::class)) {
            $mediaCount = \App\Models\Media::where('mediable_type', 'App\\Models\\Person')
                ->where('mediable_id', $source->id)
                ->update(['mediable_id' => $target->id]);
            $merged['media'] = $mediaCount;
        }

        // 4. Transferir eventos
        if (class_exists(\App\Models\Event::class)) {
            // Verificar si la tabla events tiene person_id o usa polimorfico
            try {
                $eventCount = \App\Models\Event::where('person_id', $source->id)
                    ->update(['person_id' => $target->id]);
                $merged['events'] = $eventCount;
            } catch (\Exception $e) {
                // Si falla, ignorar - la tabla puede tener estructura diferente
                $merged['events'] = 0;
            }
        }

        // 5. Completar campos vacios en target con datos de source
        $fieldsToMerge = [
            'nickname', 'birth_date', 'birth_place', 'birth_country',
            'death_date', 'death_place', 'death_country',
            'residence_place', 'residence_country', 'occupation',
            'email', 'phone', 'heritage_region', 'origin_town',
            'migration_decade', 'migration_destination', 'notes',
        ];

        foreach ($fieldsToMerge as $field) {
            if (empty($target->$field) && !empty($source->$field)) {
                $target->$field = $source->$field;
                $merged['fields'][] = $field;
            }
        }

        // Si source tiene foto y target no, transferir
        if (empty($target->photo_path) && !empty($source->photo_path)) {
            $target->photo_path = $source->photo_path;
            $merged['fields'][] = 'photo_path';
        }

        // Si source tiene herencia etnica y target no
        if (!$target->has_ethnic_heritage && $source->has_ethnic_heritage) {
            $target->has_ethnic_heritage = true;
            $merged['fields'][] = 'has_ethnic_heritage';
        }

        $target->save();

        // 6. Eliminar persona source (ya transferimos todo)
        // Limpiar foto de source si target ya la tiene
        if ($source->photo_path && $source->photo_path !== $target->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($source->photo_path);
        }

        $source->delete();

        return $merged;
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
            $relationship = $this->determineRelationship($person, $targetPerson);

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
     * Determina el tipo de relación entre dos personas.
     */
    protected function determineRelationship(Person $person, Person $related): array
    {
        // Verificar si es padre
        if ($person->father && $person->father->id === $related->id) {
            return ['type' => 'father', 'label' => __('padre')];
        }

        // Verificar si es madre
        if ($person->mother && $person->mother->id === $related->id) {
            return ['type' => 'mother', 'label' => __('madre')];
        }

        // Verificar si es cónyuge
        if ($person->currentSpouse && $person->currentSpouse->id === $related->id) {
            return ['type' => 'spouse', 'label' => __('cónyuge')];
        }

        // Verificar si es hermano/a
        foreach ($person->siblings as $sibling) {
            if ($sibling->id === $related->id) {
                return ['type' => 'sibling', 'label' => $related->gender === 'F' ? __('hermana') : __('hermano')];
            }
        }

        // Verificar si es hijo/a
        foreach ($person->children as $child) {
            if ($child->id === $related->id) {
                return ['type' => 'child', 'label' => $related->gender === 'F' ? __('hija') : __('hijo')];
            }
        }

        return ['type' => 'other', 'label' => __('familiar')];
    }
}
