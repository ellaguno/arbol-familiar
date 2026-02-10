<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Person;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    /**
     * Muestra el listado de familias.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Family::with(['husband', 'wife', 'children']);

        // Filtrar por familias del usuario
        $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id);

            // Familias donde el usuario es parte
            if ($user->person_id) {
                $q->orWhere('husband_id', $user->person_id)
                  ->orWhere('wife_id', $user->person_id);

                // Familias donde es hijo
                $q->orWhereHas('children', function ($sq) use ($user) {
                    $sq->where('person_id', $user->person_id);
                });
            }
        });

        // Busqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('husband', function ($sq) use ($search) {
                    $sq->where('first_name', 'LIKE', "%{$search}%")
                       ->orWhere('patronymic', 'LIKE', "%{$search}%");
                })->orWhereHas('wife', function ($sq) use ($search) {
                    $sq->where('first_name', 'LIKE', "%{$search}%")
                       ->orWhere('patronymic', 'LIKE', "%{$search}%");
                });
            });
        }

        // Filtro por herencia etnica
        if ($request->filled('ethnic_heritage') && $request->ethnic_heritage === 'yes') {
            $query->where(function ($q) {
                $q->whereHas('husband', fn($sq) => $sq->where('has_ethnic_heritage', true))
                  ->orWhereHas('wife', fn($sq) => $sq->where('has_ethnic_heritage', true));
            });
        }

        $families = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('families.index', compact('families'));
    }

    /**
     * Muestra el formulario para crear una familia.
     */
    public function create()
    {
        $user = auth()->user();

        // Personas disponibles: creadas por el usuario, community, familia del usuario
        $familyPersonIds = [];
        if ($user->person_id && $user->person) {
            $familyPersonIds = $user->person->extendedFamilyIds;
        }

        $persons = Person::where(function ($q) use ($user, $familyPersonIds) {
            $q->where('created_by', $user->id)
              ->orWhere('privacy_level', 'community');
            if ($user->person_id) {
                $q->orWhere('id', $user->person_id);
            }
            if (!empty($familyPersonIds)) {
                $q->orWhereIn('id', $familyPersonIds);
            }
        })->orderBy('first_name')->get();

        return view('families.create', compact('persons'));
    }

    /**
     * Almacena una nueva familia.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'husband_id' => ['nullable', 'exists:persons,id'],
            'wife_id' => ['nullable', 'exists:persons,id'],
            'marriage_date' => ['nullable', 'date'],
            'marriage_date_approx' => ['boolean'],
            'marriage_place' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:married,divorced,widowed,separated,partners,annulled'],
            'children' => ['nullable', 'array'],
            'children.*' => ['exists:persons,id'],
        ], [
            'status.required' => 'El estado es obligatorio.',
        ]);

        // Debe haber al menos un conyuge
        if (empty($validated['husband_id']) && empty($validated['wife_id'])) {
            return back()->withErrors(['husband_id' => 'Debe seleccionar al menos un conyuge.'])->withInput();
        }

        // Verificar que no exista ya una familia con los mismos conyuges
        if (!empty($validated['husband_id']) && !empty($validated['wife_id'])) {
            $existingFamily = Family::where(function ($q) use ($validated) {
                $q->where(function ($inner) use ($validated) {
                    $inner->where('husband_id', $validated['husband_id'])
                          ->where('wife_id', $validated['wife_id']);
                })->orWhere(function ($inner) use ($validated) {
                    $inner->where('husband_id', $validated['wife_id'])
                          ->where('wife_id', $validated['husband_id']);
                });
            })->first();

            if ($existingFamily) {
                return redirect()->route('families.edit', $existingFamily)
                    ->with('info', __('Ya existe una familia con estos conyuges. Puedes editarla aqui.'));
            }
        }

        // Validar que los hijos no sean los mismos que los padres
        if (!empty($validated['children'])) {
            $invalidChildren = $this->validateChildren(
                $validated['children'],
                $validated['husband_id'] ?? null,
                $validated['wife_id'] ?? null
            );
            if (!empty($invalidChildren)) {
                return back()->withErrors(['children' => $invalidChildren])->withInput();
            }
        }

        $user = auth()->user();

        $family = Family::create([
            'husband_id' => $validated['husband_id'] ?? null,
            'wife_id' => $validated['wife_id'] ?? null,
            'marriage_date' => $validated['marriage_date'] ?? null,
            'marriage_date_approx' => $validated['marriage_date_approx'] ?? false,
            'marriage_place' => $validated['marriage_place'] ?? null,
            'status' => $validated['status'],
            'created_by' => $user->id,
        ]);

        // Agregar hijos
        if (!empty($validated['children'])) {
            $order = 1;
            foreach ($validated['children'] as $childId) {
                FamilyChild::create([
                    'family_id' => $family->id,
                    'person_id' => $childId,
                    'child_order' => $order++,
                    'relationship_type' => 'biological',
                ]);
            }
        }

        // Crear evento de matrimonio si hay fecha
        if ($family->marriage_date) {
            Event::create([
                'family_id' => $family->id,
                'type' => 'MARR',
                'date' => $family->marriage_date,
                'date_approx' => $family->marriage_date_approx,
                'place' => $family->marriage_place,
            ]);
        }

        ActivityLog::log('family_created', $user, null, ['family_id' => $family->id]);

        return redirect()->route('families.show', $family)
            ->with('success', 'Familia creada correctamente.');
    }

    /**
     * Muestra los detalles de una familia.
     */
    public function show(Family $family)
    {
        $this->authorizeView($family);

        $family->load([
            'husband.media',
            'wife.media',
            'children.media',
            'events',
        ]);

        return view('families.show', compact('family'));
    }

    /**
     * Muestra el formulario de edicion.
     */
    public function edit(Family $family)
    {
        $this->authorizeEdit($family);

        $user = auth()->user();

        // Incluir personas que ya estan en la familia (para que no desaparezcan del selector)
        $existingFamilyPersonIds = collect([$family->husband_id, $family->wife_id])
            ->filter()
            ->toArray();

        $familyPersonIds = [];
        if ($user->person_id && $user->person) {
            $familyPersonIds = $user->person->extendedFamilyIds;
        }

        $persons = Person::where(function ($q) use ($user, $familyPersonIds, $existingFamilyPersonIds) {
            $q->where('created_by', $user->id)
              ->orWhere('privacy_level', 'community');
            if ($user->person_id) {
                $q->orWhere('id', $user->person_id);
            }
            if (!empty($familyPersonIds)) {
                $q->orWhereIn('id', $familyPersonIds);
            }
            // Siempre incluir los conyuges actuales de la familia
            if (!empty($existingFamilyPersonIds)) {
                $q->orWhereIn('id', $existingFamilyPersonIds);
            }
        })->orderBy('first_name')->get();

        $childIds = $family->children->pluck('id')->toArray();

        return view('families.edit', compact('family', 'persons', 'childIds'));
    }

    /**
     * Actualiza una familia.
     * IMPORTANTE: Solo actualiza los campos de la familia (cónyuges, fechas, estado).
     * Los hijos se manejan por separado para evitar pérdida de datos accidental.
     */
    public function update(Request $request, Family $family)
    {
        $this->authorizeEdit($family);

        $validated = $request->validate([
            'husband_id' => ['nullable', 'exists:persons,id'],
            'wife_id' => ['nullable', 'exists:persons,id'],
            'marriage_date' => ['nullable', 'date'],
            'marriage_date_approx' => ['boolean'],
            'marriage_place' => ['nullable', 'string', 'max:255'],
            'divorce_date' => ['nullable', 'date'],
            'divorce_place' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:married,divorced,widowed,separated,partners,annulled'],
            // No incluir 'children' para evitar borrado accidental
            // Los hijos se manejan con addChild y removeChild
        ]);

        if (empty($validated['husband_id']) && empty($validated['wife_id'])) {
            return back()->withErrors(['husband_id' => __('Debe seleccionar al menos un conyuge.')])->withInput();
        }

        $user = auth()->user();

        // Guardar IDs anteriores para comparar
        $previousHusbandId = $family->husband_id;
        $previousWifeId = $family->wife_id;

        // Actualizar solo datos de la familia, NO los hijos
        $family->update([
            'husband_id' => $validated['husband_id'] ?? null,
            'wife_id' => $validated['wife_id'] ?? null,
            'marriage_date' => $validated['marriage_date'] ?? null,
            'marriage_date_approx' => $validated['marriage_date_approx'] ?? false,
            'marriage_place' => $validated['marriage_place'] ?? null,
            'divorce_date' => $validated['divorce_date'] ?? null,
            'divorce_place' => $validated['divorce_place'] ?? null,
            'status' => $validated['status'],
        ]);

        // Registrar cambios significativos
        $changes = [];
        if ($previousHusbandId !== ($validated['husband_id'] ?? null)) {
            $changes['husband_changed'] = true;
        }
        if ($previousWifeId !== ($validated['wife_id'] ?? null)) {
            $changes['wife_changed'] = true;
        }

        ActivityLog::log('family_updated', $user, null, array_merge(['family_id' => $family->id], $changes));

        return redirect()->route('families.show', $family)
            ->with('success', __('Familia actualizada correctamente.'));
    }

    /**
     * Elimina una familia.
     */
    public function destroy(Family $family)
    {
        $this->authorizeEdit($family);

        $user = auth()->user();

        // Eliminar relaciones con hijos
        FamilyChild::where('family_id', $family->id)->delete();

        // Eliminar eventos
        Event::where('family_id', $family->id)->delete();

        ActivityLog::log('family_deleted', $user, null, ['family_id' => $family->id]);

        $family->delete();

        return redirect()->route('families.index')
            ->with('success', 'Familia eliminada correctamente.');
    }

    /**
     * Agrega un hijo a la familia.
     */
    public function addChild(Request $request, Family $family)
    {
        $this->authorizeEdit($family);

        $validated = $request->validate([
            'person_id' => ['required', 'exists:persons,id'],
            'relationship_type' => ['required', 'in:biological,adopted,foster,step'],
        ]);

        // Verificar que no exista ya
        if (FamilyChild::where('family_id', $family->id)->where('person_id', $validated['person_id'])->exists()) {
            return back()->with('error', 'Esta persona ya es hijo de esta familia.');
        }

        // Validar que el hijo no sea uno de los padres
        $invalidChildren = $this->validateChildren(
            [$validated['person_id']],
            $family->husband_id,
            $family->wife_id
        );
        if (!empty($invalidChildren)) {
            return back()->with('error', $invalidChildren);
        }

        $maxOrder = FamilyChild::where('family_id', $family->id)->max('child_order') ?? 0;

        FamilyChild::create([
            'family_id' => $family->id,
            'person_id' => $validated['person_id'],
            'child_order' => $maxOrder + 1,
            'relationship_type' => $validated['relationship_type'],
        ]);

        return back()->with('success', 'Hijo agregado correctamente.');
    }

    /**
     * Remueve un hijo de la familia.
     */
    public function removeChild(Family $family, Person $child)
    {
        $this->authorizeEdit($family);

        FamilyChild::where('family_id', $family->id)
            ->where('person_id', $child->id)
            ->delete();

        return back()->with('success', 'Hijo removido de la familia.');
    }

    /**
     * Verifica permiso de visualizacion.
     * Usa el sistema de privacidad del modelo Person.
     */
    protected function authorizeView(Family $family): void
    {
        $user = auth()->user();

        // El creador siempre puede ver
        if ($family->created_by === $user->id) {
            return;
        }

        // Verificar si es miembro de la familia
        if ($user->person_id) {
            if ($family->husband_id === $user->person_id || $family->wife_id === $user->person_id) {
                return;
            }
            if ($family->children->contains('id', $user->person_id)) {
                return;
            }
        }

        // Verificar si puede ver alguno de los miembros de la familia
        $members = collect([$family->husband, $family->wife])->filter();
        foreach ($members as $member) {
            if ($member->canBeViewedBy($user)) {
                return;
            }
        }

        $previousUrl = url()->previous();
        $currentUrl = url()->current();
        $redirectUrl = ($previousUrl && $previousUrl !== $currentUrl)
            ? $previousUrl
            : route('families.index');

        abort(redirect($redirectUrl)->with('error', __('No tienes permiso para ver esta familia.')));
    }

    /**
     * Verifica permiso de edicion.
     */
    protected function authorizeEdit(Family $family): void
    {
        $user = auth()->user();

        if ($family->created_by === $user->id) {
            return;
        }

        if ($user->person_id) {
            if ($family->husband_id === $user->person_id || $family->wife_id === $user->person_id) {
                return;
            }
        }

        abort(403, 'No tienes permiso para editar esta familia.');
    }

    /**
     * Valida que los hijos no sean los mismos que los padres.
     * También verifica ciclos genealógicos básicos.
     *
     * @param array $childIds IDs de los hijos a validar
     * @param int|null $husbandId ID del esposo
     * @param int|null $wifeId ID de la esposa
     * @return string|null Mensaje de error o null si todo está bien
     */
    protected function validateChildren(array $childIds, ?int $husbandId, ?int $wifeId): ?string
    {
        foreach ($childIds as $childId) {
            // Un hijo no puede ser el mismo que el padre
            if ($husbandId && (int)$childId === $husbandId) {
                $person = Person::find($childId);
                $name = $person ? $person->full_name : "ID {$childId}";
                return __('No se puede agregar a :name como hijo porque es el padre en esta familia.', ['name' => $name]);
            }

            // Un hijo no puede ser el mismo que la madre
            if ($wifeId && (int)$childId === $wifeId) {
                $person = Person::find($childId);
                $name = $person ? $person->full_name : "ID {$childId}";
                return __('No se puede agregar a :name como hijo porque es la madre en esta familia.', ['name' => $name]);
            }

            // Verificar que el hijo no sea ancestro de los padres (ciclo genealógico)
            if ($husbandId && $this->isAncestorOf($childId, $husbandId)) {
                $child = Person::find($childId);
                $parent = Person::find($husbandId);
                return __('Ciclo genealógico: :child es ancestro de :parent y no puede ser su hijo.',
                    ['child' => $child->full_name ?? "ID {$childId}", 'parent' => $parent->full_name ?? "ID {$husbandId}"]);
            }

            if ($wifeId && $this->isAncestorOf($childId, $wifeId)) {
                $child = Person::find($childId);
                $parent = Person::find($wifeId);
                return __('Ciclo genealógico: :child es ancestro de :parent y no puede ser su hijo.',
                    ['child' => $child->full_name ?? "ID {$childId}", 'parent' => $parent->full_name ?? "ID {$wifeId}"]);
            }
        }

        return null;
    }

    /**
     * Verifica si una persona es ancestro de otra.
     *
     * @param int $possibleAncestorId ID de posible ancestro
     * @param int $personId ID de la persona
     * @param array $visited IDs ya visitados para evitar ciclos infinitos
     * @return bool True si es ancestro
     */
    protected function isAncestorOf(int $possibleAncestorId, int $personId, array $visited = []): bool
    {
        if (in_array($personId, $visited)) {
            return false; // Evitar ciclos infinitos
        }
        $visited[] = $personId;

        // Obtener familias donde la persona es hijo
        $familiesAsChild = Family::whereHas('children', function ($q) use ($personId) {
            $q->where('person_id', $personId);
        })->get();

        foreach ($familiesAsChild as $family) {
            // Verificar padres directos
            if ($family->husband_id === $possibleAncestorId || $family->wife_id === $possibleAncestorId) {
                return true;
            }

            // Buscar recursivamente en ancestros
            if ($family->husband_id && $this->isAncestorOf($possibleAncestorId, $family->husband_id, $visited)) {
                return true;
            }
            if ($family->wife_id && $this->isAncestorOf($possibleAncestorId, $family->wife_id, $visited)) {
                return true;
            }
        }

        return false;
    }
}
