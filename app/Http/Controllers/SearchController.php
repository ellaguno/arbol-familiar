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
        $persons = $this->visiblePersons()
            ->searchByName($query)
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
        $families = $this->visibleFamilies()
            ->with(['husband', 'wife'])
            ->where(function ($q) use ($query) {
                $q->whereHas('husband', function ($sub) use ($query) {
                    $sub->searchByName($query);
                })
                ->orWhereHas('wife', function ($sub) use ($query) {
                    $sub->searchByName($query);
                });
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
        $places = $this->visiblePersons()
            ->select('birth_place')
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
        return $this->visiblePersons()
            ->where(function ($q) use ($query) {
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
        return $this->visibleFamilies()
            ->with(['husband', 'wife'])
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
        // Obtener lugares unicos de varias fuentes (solo de registros visibles)
        $birthPlaces = $this->visiblePersons()->select('birth_place as place')
            ->where('birth_place', 'like', "%{$query}%")
            ->whereNotNull('birth_place');

        $deathPlaces = $this->visiblePersons()->select('death_place as place')
            ->where('death_place', 'like', "%{$query}%")
            ->whereNotNull('death_place');

        $marriagePlaces = $this->visibleFamilies()->select('marriage_place as place')
            ->where('marriage_place', 'like', "%{$query}%")
            ->whereNotNull('marriage_place');

        $eventPlaces = $this->visibleEvents()->select('place')
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

        // Para cada lugar, obtener el conteo de personas y eventos (visibles)
        $placesWithCounts = $places->map(function ($place) {
            $personCount = $this->visiblePersons()
                ->where(function ($q) use ($place) {
                    $q->where('birth_place', $place)
                      ->orWhere('death_place', $place);
                })
                ->count();

            $familyCount = $this->visibleFamilies()->where('marriage_place', $place)->count();

            $eventCount = $this->visibleEvents()->where('place', $place)->count();

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
        return $this->visibleEvents()
            ->with('person')
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
        return $this->visibleMedia()
            ->with('mediable')
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
        $surnames = $this->visiblePersons()->select('patronymic')
            ->where('patronymic', 'like', "%{$query}%")
            ->whereNotNull('patronymic')
            ->distinct()
            ->orderBy('patronymic')
            ->get()
            ->map(function ($person) {
                $count = $this->visiblePersons()->where('patronymic', $person->patronymic)->count();
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
                $count = $this->visiblePersons()
                    ->where(function ($q) use ($variant) {
                        $q->where('patronymic', $variant->original_surname)
                          ->orWhere('patronymic', $variant->variant_1)
                          ->orWhere('patronymic', $variant->variant_2);
                    })
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
            'persons' => $this->visiblePersons()->searchByName($query)->count(),
            'families' => $this->visibleFamilies()
                ->where(function ($q) use ($query) {
                    $q->where('marriage_place', 'like', "%{$query}%")
                      ->orWhereHas('husband', function ($sub) use ($query) {
                          $sub->searchByName($query);
                      })
                      ->orWhereHas('wife', function ($sub) use ($query) {
                          $sub->searchByName($query);
                      });
                })
                ->count(),
            'places' => $this->visiblePersons()->select('birth_place')
                ->where('birth_place', 'like', "%{$query}%")
                ->distinct()
                ->count() +
                $this->visiblePersons()->select('death_place')
                ->where('death_place', 'like', "%{$query}%")
                ->distinct()
                ->count(),
            'events' => $this->visibleEvents()
                ->where(function ($q) use ($query) {
                    $q->where('place', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
                ->count(),
            'media' => $this->visibleMedia()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
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
            $query = Person::query()->visibleTo(Auth::user());

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

    /**
     * Consulta de personas restringida a las que el usuario puede ver
     * (mismos 4 niveles de privacidad que el listado de personas).
     */
    protected function visiblePersons()
    {
        return Person::query()->visibleTo(Auth::user());
    }

    /**
     * Consulta de familias restringida a las que tienen al menos un cónyuge
     * visible para el usuario.
     */
    protected function visibleFamilies()
    {
        $user = Auth::user();

        return Family::where(function ($q) use ($user) {
            $q->whereHas('husband', fn ($h) => $h->visibleTo($user))
              ->orWhereHas('wife', fn ($w) => $w->visibleTo($user));
        });
    }

    /**
     * Consulta de eventos restringida a los de personas visibles.
     */
    protected function visibleEvents()
    {
        $user = Auth::user();

        return Event::whereHas('person', fn ($q) => $q->visibleTo($user));
    }

    /**
     * Consulta de media restringida a la asociada a personas/familias visibles.
     */
    protected function visibleMedia()
    {
        $user = Auth::user();

        return Media::where(function ($q) use ($user) {
            $q->whereHasMorph('mediable', [Person::class], fn ($p) => $p->visibleTo($user))
              ->orWhereHasMorph('mediable', [Family::class], function ($f) use ($user) {
                  $f->whereHas('husband', fn ($h) => $h->visibleTo($user))
                    ->orWhereHas('wife', fn ($w) => $w->visibleTo($user));
              });
        });
    }
}
