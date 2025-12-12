<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Family;
use App\Models\Media;
use App\Models\Person;
use App\Models\SurnameVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Pagina principal de busqueda.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $results = null;
        $counts = [];

        if ($query && strlen($query) >= 2) {
            $results = $this->performSearch($query, $type);
            $counts = $this->getResultCounts($query);
        }

        // Busquedas recientes del usuario (almacenadas en sesion)
        $recentSearches = session('recent_searches', []);

        // Sugerencias de busqueda
        $suggestions = $this->getSearchSuggestions();

        return view('search.index', compact(
            'query', 'type', 'results', 'counts', 'recentSearches', 'suggestions'
        ));
    }

    /**
     * Busqueda rapida (AJAX).
     */
    public function quick(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Buscar personas (busqueda inteligente)
        $persons = Person::searchByName($query)
            ->limit(5)
            ->get();

        foreach ($persons as $person) {
            $isProtected = $person->shouldProtectMinorData();
            $results[] = [
                'type' => 'person',
                'id' => $person->id,
                'title' => $isProtected ? $person->first_name : $person->full_name,
                'subtitle' => $isProtected ? __('Menor protegido') : ($person->birth_year ? __('Nacido en :year', ['year' => $person->birth_year]) : null),
                'url' => route('persons.show', $person),
                'icon' => 'user',
                'isProtected' => $isProtected,
            ];
        }

        // Buscar familias (busqueda inteligente)
        $families = Family::with(['husband', 'wife'])
            ->whereHas('husband', function ($q) use ($query) {
                $q->searchByName($query);
            })
            ->orWhereHas('wife', function ($q) use ($query) {
                $q->searchByName($query);
            })
            ->limit(3)
            ->get();

        foreach ($families as $family) {
            $results[] = [
                'type' => 'family',
                'id' => $family->id,
                'title' => $family->display_name,
                'subtitle' => $family->marriage_date ? __('Casados en :year', ['year' => $family->marriage_date->format('Y')]) : null,
                'url' => route('families.show', $family),
                'icon' => 'users',
            ];
        }

        // Buscar lugares
        $places = Person::select('birth_place')
            ->where('birth_place', 'like', "%{$query}%")
            ->whereNotNull('birth_place')
            ->distinct()
            ->limit(3)
            ->pluck('birth_place');

        foreach ($places as $place) {
            $results[] = [
                'type' => 'place',
                'title' => $place,
                'subtitle' => __('Lugar'),
                'url' => route('search.index', ['q' => $place, 'type' => 'places']),
                'icon' => 'map-pin',
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Realizar busqueda completa.
     */
    protected function performSearch(string $query, string $type)
    {
        // Guardar en busquedas recientes
        $this->saveRecentSearch($query);

        $results = collect();

        if ($type === 'all' || $type === 'persons') {
            $results['persons'] = $this->searchPersons($query);
        }

        if ($type === 'all' || $type === 'families') {
            $results['families'] = $this->searchFamilies($query);
        }

        if ($type === 'all' || $type === 'places') {
            $results['places'] = $this->searchPlaces($query);
        }

        if ($type === 'all' || $type === 'events') {
            $results['events'] = $this->searchEvents($query);
        }

        if ($type === 'all' || $type === 'media') {
            $results['media'] = $this->searchMedia($query);
        }

        if ($type === 'all' || $type === 'surnames') {
            $results['surnames'] = $this->searchSurnames($query);
        }

        return $results;
    }

    /**
     * Buscar personas.
     */
    protected function searchPersons(string $query)
    {
        return Person::where(function ($q) use ($query) {
                // Busqueda inteligente por nombre
                $q->where(function ($nameQuery) use ($query) {
                    (new Person)->scopeSearchByName($nameQuery, $query);
                })
                // Tambien buscar en lugares
                ->orWhere('birth_place', 'like', "%{$query}%")
                ->orWhere('death_place', 'like', "%{$query}%")
                ->orWhere('origin_town', 'like', "%{$query}%");
            })
            ->orderBy('patronymic')
            ->orderBy('first_name')
            ->paginate(20);
    }

    /**
     * Buscar familias.
     */
    protected function searchFamilies(string $query)
    {
        return Family::with(['husband', 'wife'])
            ->where(function ($q) use ($query) {
                $q->where('marriage_place', 'like', "%{$query}%")
                  ->orWhereHas('husband', function ($sub) use ($query) {
                      $sub->searchByName($query);
                  })
                  ->orWhereHas('wife', function ($sub) use ($query) {
                      $sub->searchByName($query);
                  });
            })
            ->paginate(20);
    }

    /**
     * Buscar lugares.
     */
    protected function searchPlaces(string $query)
    {
        // Obtener lugares unicos de varias fuentes
        $birthPlaces = Person::select('birth_place as place')
            ->where('birth_place', 'like', "%{$query}%")
            ->whereNotNull('birth_place');

        $deathPlaces = Person::select('death_place as place')
            ->where('death_place', 'like', "%{$query}%")
            ->whereNotNull('death_place');

        $marriagePlaces = Family::select('marriage_place as place')
            ->where('marriage_place', 'like', "%{$query}%")
            ->whereNotNull('marriage_place');

        $eventPlaces = Event::select('place')
            ->where('place', 'like', "%{$query}%")
            ->whereNotNull('place');

        $places = $birthPlaces
            ->union($deathPlaces)
            ->union($marriagePlaces)
            ->union($eventPlaces)
            ->distinct()
            ->orderBy('place')
            ->get()
            ->pluck('place')
            ->unique()
            ->values();

        // Para cada lugar, obtener el conteo de personas y eventos
        $placesWithCounts = $places->map(function ($place) {
            $personCount = Person::where('birth_place', $place)
                ->orWhere('death_place', $place)
                ->count();

            $familyCount = Family::where('marriage_place', $place)->count();

            $eventCount = Event::where('place', $place)->count();

            return [
                'name' => $place,
                'person_count' => $personCount,
                'family_count' => $familyCount,
                'event_count' => $eventCount,
                'total_count' => $personCount + $familyCount + $eventCount,
            ];
        })->sortByDesc('total_count');

        return $placesWithCounts;
    }

    /**
     * Buscar eventos.
     */
    protected function searchEvents(string $query)
    {
        return Event::with('person')
            ->where(function ($q) use ($query) {
                $q->where('place', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('type', 'like', "%{$query}%");
            })
            ->orderBy('date', 'desc')
            ->paginate(20);
    }

    /**
     * Buscar media.
     */
    protected function searchMedia(string $query)
    {
        return Media::with('mediable')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Buscar apellidos y variantes.
     */
    protected function searchSurnames(string $query)
    {
        // Buscar apellidos en personas (usando patronymic como apellido principal)
        $surnames = Person::select('patronymic')
            ->where('patronymic', 'like', "%{$query}%")
            ->whereNotNull('patronymic')
            ->distinct()
            ->orderBy('patronymic')
            ->get()
            ->map(function ($person) {
                $count = Person::where('patronymic', $person->patronymic)->count();
                $variants = SurnameVariant::where('original_surname', $person->patronymic)
                    ->orWhere('variant_1', $person->patronymic)
                    ->orWhere('variant_2', $person->patronymic)
                    ->get();

                return [
                    'surname' => $person->patronymic,
                    'count' => $count,
                    'variants' => $variants,
                ];
            });

        // Tambien buscar en variantes
        $variantMatches = SurnameVariant::where('original_surname', 'like', "%{$query}%")
            ->orWhere('variant_1', 'like', "%{$query}%")
            ->orWhere('variant_2', 'like', "%{$query}%")
            ->get()
            ->map(function ($variant) {
                $count = Person::where('patronymic', $variant->original_surname)
                    ->orWhere('patronymic', $variant->variant_1)
                    ->orWhere('patronymic', $variant->variant_2)
                    ->count();

                return [
                    'surname' => $variant->original_surname,
                    'variant_1' => $variant->variant_1,
                    'variant_2' => $variant->variant_2,
                    'count' => $count,
                    'notes' => $variant->notes,
                ];
            });

        return collect([
            'surnames' => $surnames,
            'variants' => $variantMatches,
        ]);
    }

    /**
     * Obtener conteo de resultados por tipo.
     */
    protected function getResultCounts(string $query): array
    {
        return [
            'persons' => Person::searchByName($query)->count(),
            'families' => Family::where('marriage_place', 'like', "%{$query}%")
                ->orWhereHas('husband', function ($q) use ($query) {
                    $q->searchByName($query);
                })
                ->orWhereHas('wife', function ($q) use ($query) {
                    $q->searchByName($query);
                })
                ->count(),
            'places' => Person::select('birth_place')
                ->where('birth_place', 'like', "%{$query}%")
                ->distinct()
                ->count() +
                Person::select('death_place')
                ->where('death_place', 'like', "%{$query}%")
                ->distinct()
                ->count(),
            'events' => Event::where('place', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->count(),
            'media' => Media::where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->count(),
        ];
    }

    /**
     * Guardar busqueda reciente.
     */
    protected function saveRecentSearch(string $query): void
    {
        $recent = session('recent_searches', []);

        // Quitar si ya existe
        $recent = array_filter($recent, fn($s) => $s !== $query);

        // Agregar al inicio
        array_unshift($recent, $query);

        // Mantener solo las ultimas 10
        $recent = array_slice($recent, 0, 10);

        session(['recent_searches' => $recent]);
    }

    /**
     * Obtener sugerencias de busqueda.
     */
    protected function getSearchSuggestions(): array
    {
        return Cache::remember('search_suggestions', 1800, function () {
            // Apellidos mas comunes (usando patronymic)
            $topSurnames = Person::select('patronymic', DB::raw('count(*) as count'))
                ->whereNotNull('patronymic')
                ->groupBy('patronymic')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('patronymic')
                ->toArray();

            // Lugares mas comunes
            $topPlaces = Person::select('birth_place', DB::raw('count(*) as count'))
                ->whereNotNull('birth_place')
                ->groupBy('birth_place')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('birth_place')
                ->toArray();

            // Regiones de herencia
            $regions = Person::select('heritage_region')
                ->whereNotNull('heritage_region')
                ->distinct()
                ->pluck('heritage_region')
                ->toArray();

            return [
                'surnames' => $topSurnames,
                'places' => $topPlaces,
                'regions' => $regions,
            ];
        });
    }

    /**
     * Busqueda avanzada.
     */
    public function advanced(Request $request)
    {
        $results = null;

        if ($request->filled('search')) {
            $query = Person::query();

            // Nombre
            if ($request->filled('first_name')) {
                $query->where('first_name', 'like', "%{$request->first_name}%");
            }

            // Apellido paterno
            if ($request->filled('patronymic')) {
                $query->where('patronymic', 'like', "%{$request->patronymic}%");
            }

            // Apellido materno
            if ($request->filled('matronymic')) {
                $query->where('matronymic', 'like', "%{$request->matronymic}%");
            }

            // Genero
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Lugar de nacimiento
            if ($request->filled('birth_place')) {
                $query->where('birth_place', 'like', "%{$request->birth_place}%");
            }

            // Rango de anios de nacimiento
            if ($request->filled('birth_year_from')) {
                $query->whereYear('birth_date', '>=', $request->birth_year_from);
            }
            if ($request->filled('birth_year_to')) {
                $query->whereYear('birth_date', '<=', $request->birth_year_to);
            }

            // Region de herencia
            if ($request->filled('heritage_region')) {
                $query->where('heritage_region', $request->heritage_region);
            }

            // Pueblo de origen
            if ($request->filled('origin_town')) {
                $query->where('origin_town', 'like', "%{$request->origin_town}%");
            }

            // Estado de vida
            if ($request->filled('living_status')) {
                if ($request->living_status === 'living') {
                    $query->where('is_living', true);
                } elseif ($request->living_status === 'deceased') {
                    $query->where('is_living', false);
                }
            }

            $results = $query->orderBy('patronymic')
                ->orderBy('first_name')
                ->paginate(20)
                ->appends($request->query());
        }

        // Obtener regiones para el filtro
        $regions = Person::select('heritage_region')
            ->whereNotNull('heritage_region')
            ->distinct()
            ->orderBy('heritage_region')
            ->pluck('heritage_region');

        return view('search.advanced', compact('results', 'regions'));
    }

    /**
     * Limpiar busquedas recientes.
     */
    public function clearRecent()
    {
        session()->forget('recent_searches');

        return back()->with('success', __('Historial de busqueda limpiado.'));
    }
}
