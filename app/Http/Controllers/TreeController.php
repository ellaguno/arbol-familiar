<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TreeController extends Controller
{
    /**
     * Muestra el arbol genealogico principal.
     */
    public function index()
    {
        $user = auth()->user();

        // Si el usuario tiene persona asociada, mostrar su arbol
        if ($user->person_id) {
            return redirect()->route('tree.view', $user->person_id);
        }

        // Si no, mostrar selector de persona raiz
        // Incluye: creadas por el usuario y nivel community/public
        $persons = Person::where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereIn('privacy_level', ['community', 'public']);
        })->orderBy('first_name')->get();

        return view('tree.index', compact('persons'));
    }

    /**
     * Muestra el arbol centrado en una persona.
     */
    public function view(Person $person = null)
    {
        $user = auth()->user();

        // Si no se especifica persona, usar la del usuario
        if (!$person && $user->person_id) {
            $person = Person::find($user->person_id);
        }

        if (!$person) {
            return redirect()->route('tree.index')
                ->with('info', 'Selecciona una persona para ver su arbol.');
        }

        // Verificar permiso
        $this->authorizeView($person);

        return view('tree.view', compact('person'));
    }

    /**
     * API: Obtiene datos del arbol para D3.js.
     */
    public function getData(Request $request, Person $person)
    {
        $this->authorizeView($person);

        $generations = $request->get('generations', 3);
        $direction = $request->get('direction', 'both'); // ancestors, descendants, both

        $cacheKey = "tree_data_{$person->id}_{$generations}_{$direction}";

        $data = Cache::remember($cacheKey, 600, function () use ($person, $generations, $direction) {
            $data = [
                'root' => $this->personToNode($person),
                'ancestors' => [],
                'descendants' => [],
            ];

            if ($direction === 'ancestors' || $direction === 'both') {
                $data['ancestors'] = $this->getAncestors($person, $generations);
            }

            if ($direction === 'descendants' || $direction === 'both') {
                $data['descendants'] = $this->getDescendants($person, $generations);
            }

            return $data;
        });

        return response()->json($data);
    }

    /**
     * API: Obtiene datos para vista de abanico.
     */
    public function getFanData(Request $request, Person $person)
    {
        $this->authorizeView($person);

        $generations = $request->get('generations', 4);

        $cacheKey = "tree_fan_{$person->id}_{$generations}";

        $data = Cache::remember($cacheKey, 600, function () use ($person, $generations) {
            return $this->buildFanData($person, $generations);
        });

        return response()->json($data);
    }

    /**
     * API: Obtiene timeline de una persona.
     */
    public function getTimeline(Person $person)
    {
        $this->authorizeView($person);

        $events = collect();

        // Nacimiento
        if ($person->birth_date) {
            $events->push([
                'date' => $person->birth_date->format('Y-m-d'),
                'year' => $person->birth_date->year,
                'type' => 'BIRT',
                'label' => 'Nacimiento',
                'place' => $person->birth_place,
                'description' => null,
            ]);
        }

        // Eventos registrados
        foreach ($person->events as $event) {
            $events->push([
                'date' => $event->date?->format('Y-m-d'),
                'year' => $event->date?->year,
                'type' => $event->type,
                'label' => $event->type_label,
                'place' => $event->place,
                'description' => $event->description,
            ]);
        }

        // Matrimonios
        foreach ($person->familiesAsSpouse()->get() as $family) {
            if ($family->marriage_date) {
                $spouse = $family->husband_id === $person->id ? $family->wife : $family->husband;
                $events->push([
                    'date' => $family->marriage_date->format('Y-m-d'),
                    'year' => $family->marriage_date->year,
                    'type' => 'MARR',
                    'label' => 'Matrimonio',
                    'place' => $family->marriage_place,
                    'description' => $spouse ? 'con ' . $spouse->full_name : null,
                ]);
            }
        }

        // Fallecimiento
        if (!$person->is_living && $person->death_date) {
            $events->push([
                'date' => $person->death_date->format('Y-m-d'),
                'year' => $person->death_date->year,
                'type' => 'DEAT',
                'label' => 'Fallecimiento',
                'place' => $person->death_place,
                'description' => null,
            ]);
        }

        // Ordenar por fecha
        $events = $events->filter(fn($e) => $e['date'] !== null)
            ->sortBy('date')
            ->values();

        return response()->json($events);
    }

    /**
     * Convierte persona a nodo para el arbol.
     */
    protected function personToNode(Person $person): array
    {
        // Verificar si tiene padre y/o madre
        $family = $person->familiesAsChild()->first();
        $hasFather = $family && $family->husband_id;
        $hasMother = $family && $family->wife_id;

        // Verificar si tiene conyuge
        $hasSpouse = $person->familiesAsSpouse()->whereNotNull('husband_id')->whereNotNull('wife_id')->exists();

        // Obtener hermanos
        $siblings = $person->siblings;
        $siblingsCount = $siblings->count();
        $siblingsInfo = [];

        foreach ($siblings as $sibling) {
            $siblingsInfo[] = [
                'id' => $sibling->id,
                'name' => $sibling->shouldProtectMinorData() ? $sibling->first_name : $sibling->full_name,
                'gender' => $sibling->gender,
            ];
        }

        // Proteger datos de menores
        $isProtectedMinor = $person->shouldProtectMinorData();

        return [
            'id' => $person->id,
            'name' => $isProtectedMinor ? $person->first_name : $person->full_name,
            'firstName' => $person->first_name,
            'lastName' => $isProtectedMinor ? '' : $person->patronymic,
            'gender' => $person->gender,
            'birthDate' => $isProtectedMinor ? null : ($person->birth_year ?? ($person->birth_date?->format('Y'))),
            'deathDate' => $isProtectedMinor ? null : ($person->death_year ?? ($person->death_date?->format('Y'))),
            'isLiving' => $person->is_living,
            'isMinor' => $person->is_minor_calculated,
            'isProtected' => $isProtectedMinor,
            'photo' => $isProtectedMinor ? null : ($person->photo_path ? asset('storage/' . $person->photo_path) : null),
            'hasEthnicHeritage' => $person->has_ethnic_heritage,
            'url' => route('persons.show', $person),
            'hasFather' => $hasFather,
            'hasMother' => $hasMother,
            'hasSpouse' => $hasSpouse,
            'siblingsCount' => $siblingsCount,
            'siblings' => $siblingsInfo,
        ];
    }

    /**
     * Obtiene ancestros recursivamente.
     */
    protected function getAncestors(Person $person, int $generations, int $level = 1): array
    {
        if ($level > $generations) {
            return [];
        }

        $ancestors = [];
        $family = $person->familiesAsChild()->with(['husband', 'wife'])->first();

        if ($family) {
            if ($family->husband) {
                $father = $this->personToNode($family->husband);
                $father['relation'] = 'father';
                $father['level'] = $level;
                $father['ancestors'] = $this->getAncestors($family->husband, $generations, $level + 1);
                $ancestors[] = $father;
            }

            if ($family->wife) {
                $mother = $this->personToNode($family->wife);
                $mother['relation'] = 'mother';
                $mother['level'] = $level;
                $mother['ancestors'] = $this->getAncestors($family->wife, $generations, $level + 1);
                $ancestors[] = $mother;
            }
        }

        return $ancestors;
    }

    /**
     * Obtiene descendientes recursivamente.
     */
    protected function getDescendants(Person $person, int $generations, int $level = 1): array
    {
        if ($level > $generations) {
            return [];
        }

        $descendants = [];
        $families = $person->familiesAsSpouse()->with(['husband', 'wife', 'children'])->get();

        foreach ($families as $family) {
            $spouse = $family->husband_id === $person->id ? $family->wife : $family->husband;

            $familyNode = [
                'familyId' => $family->id,
                'spouse' => $spouse ? $this->personToNode($spouse) : null,
                'marriageDate' => $family->marriage_date?->format('Y'),
                'status' => $family->status,
                'children' => [],
            ];

            foreach ($family->children as $child) {
                $childNode = $this->personToNode($child);
                $childNode['level'] = $level;
                $childNode['descendants'] = $this->getDescendants($child, $generations, $level + 1);
                $familyNode['children'][] = $childNode;
            }

            $descendants[] = $familyNode;
        }

        return $descendants;
    }

    /**
     * Construye datos para vista de abanico.
     */
    protected function buildFanData(Person $person, int $generations): array
    {
        $data = [
            'name' => $person->full_name,
            'data' => $this->personToNode($person),
        ];

        if ($generations > 0) {
            $family = $person->familiesAsChild()->with(['husband', 'wife'])->first();

            if ($family) {
                $children = [];

                if ($family->husband) {
                    $children[] = $this->buildFanData($family->husband, $generations - 1);
                }

                if ($family->wife) {
                    $children[] = $this->buildFanData($family->wife, $generations - 1);
                }

                if (!empty($children)) {
                    $data['children'] = $children;
                }
            }
        }

        return $data;
    }

    /**
     * Verifica permiso de visualizacion.
     * Usa el método canBeViewedBy del modelo Person que implementa
     * los 4 niveles de privacidad: private, family, community, public.
     */
    protected function authorizeView(Person $person): void
    {
        $user = auth()->user();

        if (!$person->canBeViewedBy($user)) {
            abort(403, __('No tienes permiso para ver este arbol.'));
        }
    }
}
