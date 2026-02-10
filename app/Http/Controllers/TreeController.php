<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Plugins\Support\TreeTraversal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TreeController extends Controller
{
    protected TreeTraversal $traversal;

    public function __construct(TreeTraversal $traversal)
    {
        $this->traversal = $traversal;
    }

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
              ->orWhereIn('privacy_level', ['community', 'selected_users']);
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
                'root' => $this->traversal->personToNode($person),
                'ancestors' => [],
                'descendants' => [],
            ];

            if ($direction === 'ancestors' || $direction === 'both') {
                $data['ancestors'] = $this->traversal->getAncestors($person, $generations);
            }

            if ($direction === 'descendants' || $direction === 'both') {
                $data['descendants'] = $this->traversal->getDescendants($person, $generations);
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
            return $this->traversal->buildFanData($person, $generations);
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
     * Verifica permiso de visualizacion.
     * Usa el método canBeViewedBy del modelo Person que implementa
     * los 4 niveles de privacidad: private, family, community, public.
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

            abort(redirect($redirectUrl)->with('error', __('No tienes permiso para ver este arbol.')));
        }
    }
}
