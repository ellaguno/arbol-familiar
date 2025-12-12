<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Family;
use App\Models\Media;
use App\Models\Person;
use App\Models\SurnameVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard de reportes y estadisticas.
     */
    public function index()
    {
        // Estadísticas principales para la vista
        $stats = [
            'users' => User::count(),
            'persons' => Person::count(),
            'families' => Family::count(),
            'media' => Media::count(),
        ];

        // Datos para gráficos
        $chartData = [
            'registrations' => Person::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
            'gender' => [
                'M' => Person::where('gender', 'M')->count(),
                'F' => Person::where('gender', 'F')->count(),
                'unknown' => Person::whereNull('gender')->orWhere('gender', 'U')->count(),
            ],
        ];

        return view('admin.reports.index', compact('stats', 'chartData'));
    }

    /**
     * Estadisticas demograficas.
     */
    public function demographics()
    {
        $totalPersons = Person::count();
        $livingCount = Person::where('is_living', true)->count();

        // Calcular edad promedio de personas vivas
        $avgAge = Person::where('is_living', true)
            ->whereNotNull('birth_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) as avg_age')
            ->value('avg_age');

        // Estadísticas principales
        $stats = [
            'total_persons' => $totalPersons,
            'living' => $livingCount,
            'average_age' => $avgAge ? round($avgAge) : null,
            'generations' => $this->estimateGenerations(),
        ];

        // Distribucion por genero (asegurar que existan todas las claves)
        $genderRaw = Person::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        $genderStats = [
            'M' => $genderRaw['M'] ?? 0,
            'F' => $genderRaw['F'] ?? 0,
            'U' => $genderRaw['U'] ?? ($genderRaw[''] ?? 0),
        ];

        // Piramide de edades
        $agePyramid = $this->calculateAgePyramid();

        // Estado vital
        $vitalStats = [
            'living' => $livingCount,
            'deceased' => Person::where('is_living', false)->count(),
        ];

        // Distribucion por decada de nacimiento
        $birthDecades = Person::selectRaw('FLOOR(YEAR(birth_date) / 10) * 10 as decade, count(*) as count')
            ->whereNotNull('birth_date')
            ->groupBy('decade')
            ->orderBy('decade')
            ->pluck('count', 'decade')
            ->toArray();

        // Renombrar para la vista
        $birthsByDecade = $birthDecades;

        return view('admin.reports.demographics', compact(
            'stats', 'genderStats', 'agePyramid', 'vitalStats', 'birthsByDecade'
        ));
    }

    /**
     * Estimar número de generaciones.
     */
    protected function estimateGenerations(): int
    {
        $oldestBirth = Person::whereNotNull('birth_date')->min('birth_date');
        $newestBirth = Person::whereNotNull('birth_date')->max('birth_date');

        if (!$oldestBirth || !$newestBirth) {
            return 1;
        }

        $yearSpan = date('Y', strtotime($newestBirth)) - date('Y', strtotime($oldestBirth));
        return max(1, (int) ceil($yearSpan / 25));
    }

    /**
     * Estadisticas geograficas.
     */
    public function geographic()
    {
        // Países de nacimiento
        $birthCountries = Person::select('birth_country', DB::raw('count(*) as total'))
            ->whereNotNull('birth_country')
            ->where('birth_country', '!=', '')
            ->groupBy('birth_country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Ciudades de nacimiento
        $birthCities = Person::select('birth_place as birth_city', DB::raw('count(*) as total'))
            ->whereNotNull('birth_place')
            ->where('birth_place', '!=', '')
            ->groupBy('birth_place')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Estadisticas de migracion
        $topCountries = Person::select('birth_country', DB::raw('count(*) as total'))
            ->whereNotNull('birth_country')
            ->where('birth_country', '!=', '')
            ->groupBy('birth_country')
            ->orderByDesc('total')
            ->limit(3)
            ->pluck('total', 'birth_country')
            ->toArray();

        $migrationStats = [
            'total_with_country' => Person::whereNotNull('birth_country')->where('birth_country', '!=', '')->count(),
            'unique_countries' => Person::select('birth_country')->whereNotNull('birth_country')->where('birth_country', '!=', '')->distinct()->count(),
            'top_countries' => $topCountries,
        ];

        // Regiones de herencia (usando heritage_region o origin_town)
        $heritageRegions = Person::select('heritage_region as birth_city', DB::raw('count(*) as total'))
            ->whereNotNull('heritage_region')
            ->where('heritage_region', '!=', '')
            ->groupBy('heritage_region')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        // Si no hay regiones de herencia, usar pueblos de origen
        if ($heritageRegions->isEmpty()) {
            $heritageRegions = Person::select('origin_town as birth_city', DB::raw('count(*) as total'))
                ->whereNotNull('origin_town')
                ->where('origin_town', '!=', '')
                ->groupBy('origin_town')
                ->orderByDesc('total')
                ->limit(12)
                ->get();
        }

        return view('admin.reports.geographic', compact(
            'birthCountries', 'birthCities', 'migrationStats', 'heritageRegions'
        ));
    }

    /**
     * Estadisticas de apellidos.
     */
    public function surnames()
    {
        $totalPersons = Person::count();
        $personsWithSurname = Person::whereNotNull('patronymic')->where('patronymic', '!=', '')->count();

        // Top apellidos (con alias para la vista)
        $topSurnames = Person::select('patronymic as last_name', DB::raw('count(*) as total'))
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->groupBy('patronymic')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        // Apellido más común
        $mostCommon = $topSurnames->first()?->last_name ?? '-';

        // Apellidos únicos
        $uniqueSurnames = Person::select('patronymic')
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->distinct()
            ->count();

        // Obtener apellidos de herencia usando múltiples criterios:
        // 1. Personas con has_ethnic_heritage = true
        // 2. Apellidos que terminan en sufijos típicos de herencia
        // 3. Apellidos registrados en surname_variants
        $heritageSurnames = $this->getHeritageSurnames();

        // Conteo de personas con apellidos de herencia
        $heritageSurnamesCount = $this->countHeritageSurnamePersons();

        // Apellidos por letra inicial
        $surnamesByLetter = Person::selectRaw('UPPER(LEFT(patronymic, 1)) as letter, count(*) as count')
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->groupBy('letter')
            ->orderBy('letter')
            ->pluck('count', 'letter')
            ->toArray();

        // Estadísticas para la vista
        $stats = [
            'unique_surnames' => $uniqueSurnames,
            'most_common' => $mostCommon,
            'heritage_surnames' => $heritageSurnamesCount,
            'persons_with_surname' => $totalPersons > 0 ? ($personsWithSurname / $totalPersons) * 100 : 0,
        ];

        return view('admin.reports.surnames', compact('stats', 'topSurnames', 'uniqueSurnames', 'surnamesByLetter', 'heritageSurnames'));
    }

    /**
     * Obtiene apellidos de herencia usando múltiples criterios.
     * Criterios:
     * 1. Personas marcadas con has_ethnic_heritage = true
     * 2. Apellidos con sufijos típicos de herencia (-ic, -ek, -ac, -ar, -an, -ov, -ev)
     * 3. Apellidos registrados en surname_variants (variantes históricas)
     */
    protected function getHeritageSurnames()
    {
        // Obtener apellidos de personas con herencia etnica marcada
        $ancestrySurnames = Person::select('patronymic as last_name', DB::raw('count(*) as total'))
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->where('has_ethnic_heritage', true)
            ->groupBy('patronymic')
            ->get()
            ->keyBy('last_name');

        // Obtener apellidos con sufijos típicos de herencia
        $suffixSurnames = Person::select('patronymic as last_name', DB::raw('count(*) as total'))
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->where(function($q) {
                // Sufijos de herencia más comunes
                $q->where('patronymic', 'like', '%ić')
                  ->orWhere('patronymic', 'like', '%ic')
                  ->orWhere('patronymic', 'like', '%ek')
                  ->orWhere('patronymic', 'like', '%ac')
                  ->orWhere('patronymic', 'like', '%ar')
                  ->orWhere('patronymic', 'like', '%an')
                  ->orWhere('patronymic', 'like', '%ov')
                  ->orWhere('patronymic', 'like', '%ev');
            })
            ->groupBy('patronymic')
            ->get()
            ->keyBy('last_name');

        // Obtener apellidos de surname_variants (variantes históricas registradas)
        $variantSurnames = collect();
        $variants = SurnameVariant::all();
        foreach ($variants as $variant) {
            foreach ($variant->all_variants as $surnameVariant) {
                if ($surnameVariant) {
                    $count = Person::where('patronymic', $surnameVariant)->count();
                    if ($count > 0 && !$variantSurnames->has($surnameVariant)) {
                        $variantSurnames->put($surnameVariant, (object)[
                            'last_name' => $surnameVariant,
                            'total' => $count
                        ]);
                    }
                }
            }
        }

        // Combinar todos los resultados, priorizando el conteo más alto
        $combined = collect();

        foreach ($ancestrySurnames as $surname => $data) {
            $combined[$surname] = $data;
        }

        foreach ($suffixSurnames as $surname => $data) {
            if (!isset($combined[$surname]) || $combined[$surname]->total < $data->total) {
                $combined[$surname] = $data;
            }
        }

        foreach ($variantSurnames as $surname => $data) {
            if (!isset($combined[$surname]) || $combined[$surname]->total < $data->total) {
                $combined[$surname] = $data;
            }
        }

        // Ordenar por total descendente y limitar a 16
        return $combined->sortByDesc('total')->take(16)->values();
    }

    /**
     * Cuenta personas con apellidos de herencia.
     */
    protected function countHeritageSurnamePersons(): int
    {
        // Personas con herencia etnica marcada
        $heritageCount = Person::where('has_ethnic_heritage', true)->count();

        // Si hay personas marcadas, usar ese conteo
        if ($heritageCount > 0) {
            return $heritageCount;
        }

        // Fallback: contar por sufijos de herencia
        return Person::whereNotNull('patronymic')
            ->where(function($q) {
                $q->where('patronymic', 'like', '%ić')
                  ->orWhere('patronymic', 'like', '%ic')
                  ->orWhere('patronymic', 'like', '%ek')
                  ->orWhere('patronymic', 'like', '%ac')
                  ->orWhere('patronymic', 'like', '%ar');
            })
            ->count();
    }

    /**
     * Estadisticas de familias.
     */
    public function families()
    {
        // Total de familias
        $totalFamilies = Family::count();

        // Familias con hijos
        $familiesWithChildren = DB::table('family_children')
            ->select('family_id')
            ->distinct()
            ->count();

        // Promedio de hijos
        $avgChildren = DB::table('family_children')
            ->select('family_id', DB::raw('count(*) as count'))
            ->groupBy('family_id')
            ->get()
            ->avg('count') ?? 0;

        // Familias casadas
        $marriedFamilies = Family::where('status', 'married')->count();

        // Estadísticas para la vista
        $stats = [
            'total_families' => $totalFamilies,
            'with_children' => $totalFamilies > 0 ? ($familiesWithChildren / $totalFamilies) * 100 : 0,
            'average_children' => $avgChildren,
            'married' => $marriedFamilies,
        ];

        // Distribucion de hijos por familia
        $childrenDistribution = DB::table('family_children')
            ->select('family_id', DB::raw('count(*) as children_count'))
            ->groupBy('family_id')
            ->get()
            ->groupBy('children_count')
            ->map(fn($items) => $items->count())
            ->toArray();

        // Matrimonios por decada
        $marriagesByDecade = Family::selectRaw('FLOOR(YEAR(marriage_date) / 10) * 10 as decade, count(*) as count')
            ->whereNotNull('marriage_date')
            ->groupBy('decade')
            ->orderBy('decade')
            ->pluck('count', 'decade')
            ->toArray();

        $familiesWithoutChildren = $totalFamilies - $familiesWithChildren;

        // Estado de matrimonios
        $marriageStatus = Family::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Estado de las familias (para la sección de la vista)
        $familyStatus = [
            'complete' => Family::whereNotNull('husband_id')->whereNotNull('wife_id')->count(),
            'single_parent' => Family::where(function($q) {
                $q->whereNull('husband_id')->whereNotNull('wife_id');
            })->orWhere(function($q) {
                $q->whereNotNull('husband_id')->whereNull('wife_id');
            })->count(),
            'with_marriage' => Family::whereNotNull('marriage_date')->count(),
            'with_children' => $familiesWithChildren,
        ];

        // Familias más numerosas
        $largestFamilies = Family::select('families.*')
            ->selectSub(
                DB::table('family_children')->selectRaw('count(*)')->whereColumn('family_id', 'families.id'),
                'children_count'
            )
            ->with(['husband', 'wife'])
            ->orderByDesc('children_count')
            ->limit(10)
            ->get();

        return view('admin.reports.families', compact(
            'stats', 'totalFamilies', 'childrenDistribution', 'avgChildren',
            'marriagesByDecade', 'familiesWithChildren', 'familiesWithoutChildren',
            'marriageStatus', 'largestFamilies', 'familyStatus'
        ));
    }

    /**
     * Estadisticas de completitud de datos.
     */
    public function dataQuality()
    {
        $totalPersons = Person::count();
        $totalFamilies = Family::count();

        // IDs de personas que son hijos en alguna familia
        $personsWithParents = DB::table('family_children')->distinct()->pluck('person_id')->toArray();

        // Campos completados para personas
        $completeness = [
            'birth_date' => Person::whereNotNull('birth_date')->count(),
            'birth_place' => Person::whereNotNull('birth_place')->count(),
            'death_date' => Person::where('is_living', false)->whereNotNull('death_date')->count(),
            'parents' => count($personsWithParents),
            'photo' => Person::whereNotNull('photo_path')->count(),
            'heritage_origin' => Person::whereNotNull('heritage_region')->count(),
            'origin_town' => Person::whereNotNull('origin_town')->count(),
        ];

        // Calcular porcentajes para personas
        $completenessPercent = [];
        foreach ($completeness as $field => $count) {
            $completenessPercent[$field] = $totalPersons > 0 ? round(($count / $totalPersons) * 100, 1) : 0;
        }

        // Alias para la vista
        $personCompleteness = $completenessPercent;

        // Completitud de familias
        $familyFields = [
            'husband' => Family::whereNotNull('husband_id')->count(),
            'wife' => Family::whereNotNull('wife_id')->count(),
            'marriage_date' => Family::whereNotNull('marriage_date')->count(),
            'marriage_place' => Family::whereNotNull('marriage_place')->count(),
            'children' => DB::table('family_children')->distinct('family_id')->count('family_id'),
        ];

        $familyCompleteness = [];
        foreach ($familyFields as $field => $count) {
            $familyCompleteness[$field] = $totalFamilies > 0 ? round(($count / $totalFamilies) * 100, 1) : 0;
        }

        // Personas sin padres conocidos (no están en family_children)
        $orphans = Person::whereNotIn('id', $personsWithParents)->count();

        // Personas sin familia (ni como hijo ni como esposo)
        $personsInFamilies = DB::table('families')
            ->select('husband_id as person_id')
            ->whereNotNull('husband_id')
            ->union(DB::table('families')->select('wife_id as person_id')->whereNotNull('wife_id'))
            ->union(DB::table('family_children')->select('person_id'))
            ->pluck('person_id')
            ->unique()
            ->toArray();

        $lonely = Person::whereNotIn('id', $personsInFamilies)->count();

        // Registros recientes
        $recentAdditions = [
            'persons_week' => Person::where('created_at', '>=', now()->subWeek())->count(),
            'persons_month' => Person::where('created_at', '>=', now()->subMonth())->count(),
            'families_week' => Family::where('created_at', '>=', now()->subWeek())->count(),
            'families_month' => Family::where('created_at', '>=', now()->subMonth())->count(),
        ];

        // Calcular score de calidad (promedio de campos completos)
        $qualityScore = $totalPersons > 0 ? array_sum($completenessPercent) / count($completenessPercent) : 0;

        $stats = [
            'quality_score' => round($qualityScore, 1),
        ];

        // Detectar problemas
        $issues = $this->detectDataIssues($totalPersons, $completenessPercent, $orphans, $lonely);

        // Generar recomendaciones
        $recommendations = $this->generateRecommendations($completenessPercent, $familyCompleteness);

        // Buscar posibles duplicados
        $duplicates = $this->findPotentialDuplicates();

        return view('admin.reports.data-quality', compact(
            'stats', 'totalPersons', 'completeness', 'completenessPercent',
            'personCompleteness', 'familyCompleteness', 'orphans', 'lonely',
            'recentAdditions', 'issues', 'recommendations', 'duplicates'
        ));
    }

    /**
     * Estadisticas de eventos.
     */
    public function events()
    {
        // Contar nacimientos (personas con fecha de nacimiento)
        $births = Person::whereNotNull('birth_date')->count();

        // Contar matrimonios
        $marriages = Family::whereNotNull('marriage_date')->count();

        // Contar defunciones
        $deaths = Person::where('is_living', false)->whereNotNull('death_date')->count();

        // Calcular promedio de vida (fallecidos con ambas fechas)
        $avgLifespan = Person::where('is_living', false)
            ->whereNotNull('birth_date')
            ->whereNotNull('death_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birth_date, death_date)) as avg')
            ->value('avg');

        $stats = [
            'births' => $births,
            'marriages' => $marriages,
            'deaths' => $deaths,
            'average_lifespan' => $avgLifespan ? round($avgLifespan) : null,
        ];

        // Eventos por tipo
        $eventsByType = Event::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->pluck('count', 'type')
            ->toArray();

        // Eventos por año
        $eventsByYear = Event::selectRaw('YEAR(date) as year, count(*) as count')
            ->whereNotNull('date')
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('count', 'year')
            ->toArray();

        // Lugares de eventos mas comunes
        $eventPlaces = Event::select('place', DB::raw('count(*) as count'))
            ->whereNotNull('place')
            ->groupBy('place')
            ->orderByDesc('count')
            ->limit(15)
            ->pluck('count', 'place')
            ->toArray();

        $totalEvents = Event::count();

        // Nacimientos por década
        $birthsByDecade = Person::selectRaw('FLOOR(YEAR(birth_date) / 10) * 10 as decade, count(*) as count')
            ->whereNotNull('birth_date')
            ->groupBy('decade')
            ->orderBy('decade')
            ->pluck('count', 'decade')
            ->toArray();

        // Defunciones por década
        $deathsByDecade = Person::selectRaw('FLOOR(YEAR(death_date) / 10) * 10 as decade, count(*) as count')
            ->whereNotNull('death_date')
            ->where('is_living', false)
            ->groupBy('decade')
            ->orderBy('decade')
            ->pluck('count', 'decade')
            ->toArray();

        // Distribución de edades al fallecer
        $deathAgeDistribution = $this->calculateDeathAgeDistribution();

        // Nacimientos por mes
        $birthsByMonth = Person::selectRaw('MONTH(birth_date) as month, count(*) as count')
            ->whereNotNull('birth_date')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Asegurar que todos los meses existan
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($birthsByMonth[$i])) {
                $birthsByMonth[$i] = 0;
            }
        }
        ksort($birthsByMonth);

        // Datos interesantes
        $interestingFacts = $this->getInterestingFacts();

        return view('admin.reports.events', compact(
            'stats', 'eventsByType', 'eventsByYear', 'eventPlaces', 'totalEvents',
            'birthsByDecade', 'deathsByDecade', 'deathAgeDistribution', 'birthsByMonth', 'interestingFacts'
        ));
    }

    /**
     * Exportar reporte a CSV.
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:persons,surnames,places,families',
        ]);

        $type = $request->input('type');
        $data = [];
        $filename = "reporte_{$type}_" . date('Y-m-d') . ".csv";

        switch ($type) {
            case 'persons':
                $data = Person::select('id', 'first_name', 'patronymic', 'gender', 'birth_date', 'birth_place', 'death_date', 'is_living', 'heritage_region', 'origin_town')
                    ->get()
                    ->toArray();
                break;

            case 'surnames':
                $data = Person::select('patronymic', DB::raw('count(*) as count'))
                    ->whereNotNull('patronymic')
                    ->groupBy('patronymic')
                    ->orderByDesc('count')
                    ->get()
                    ->toArray();
                break;

            case 'places':
                $data = Person::select('birth_place', DB::raw('count(*) as count'))
                    ->whereNotNull('birth_place')
                    ->groupBy('birth_place')
                    ->orderByDesc('count')
                    ->get()
                    ->toArray();
                break;

            case 'families':
                $data = Family::with(['husband', 'wife'])
                    ->get()
                    ->map(function($family) {
                        $childrenCount = DB::table('family_children')->where('family_id', $family->id)->count();
                        return [
                            'id' => $family->id,
                            'husband' => $family->husband?->full_name ?? '',
                            'wife' => $family->wife?->full_name ?? '',
                            'marriage_date' => $family->marriage_date?->format('Y-m-d') ?? '',
                            'marriage_place' => $family->marriage_place ?? '',
                            'status' => $family->status ?? '',
                            'children_count' => $childrenCount,
                        ];
                    })
                    ->toArray();
                break;

            default:
                abort(404);
        }

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Obtener estadisticas generales.
     */
    protected function getGeneralStats(): array
    {
        return [
            'total_persons' => Person::count(),
            'total_families' => Family::count(),
            'total_events' => Event::count(),
            'total_users' => User::count(),
            'male_count' => Person::where('gender', 'M')->count(),
            'female_count' => Person::where('gender', 'F')->count(),
            'living_count' => Person::where('is_living', true)->count(),
            'deceased_count' => Person::where('is_living', false)->count(),
            'with_photos' => Person::whereNotNull('photo_path')->count(),
            'heritage_origin' => Person::whereNotNull('heritage_region')->count(),
        ];
    }

    /**
     * Obtener datos para graficos.
     */
    protected function getChartData(): array
    {
        // Registros por mes (ultimos 12 meses)
        $monthlyPersons = Person::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Top 10 apellidos
        $topSurnames = Person::select('patronymic', DB::raw('count(*) as count'))
            ->whereNotNull('patronymic')
            ->groupBy('patronymic')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'patronymic')
            ->toArray();

        // Distribucion por region de herencia
        $regionDistribution = Person::select('heritage_region', DB::raw('count(*) as count'))
            ->whereNotNull('heritage_region')
            ->groupBy('heritage_region')
            ->pluck('count', 'heritage_region')
            ->toArray();

        return [
            'monthly_persons' => $monthlyPersons,
            'top_surnames' => $topSurnames,
            'region_distribution' => $regionDistribution,
        ];
    }

    /**
     * Calcular piramide de edades.
     */
    protected function calculateAgePyramid(): array
    {
        $pyramid = [];
        $ranges = [
            '0-9', '10-19', '20-29', '30-39', '40-49',
            '50-59', '60-69', '70-79', '80-89', '90+'
        ];

        foreach ($ranges as $index => $range) {
            $minAge = $index * 10;
            $maxAge = $index === 9 ? 200 : ($index + 1) * 10 - 1;

            // Solo personas vivas para la piramide
            $maleCount = Person::where('gender', 'M')
                ->where('is_living', true)
                ->whereNotNull('birth_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN ? AND ?', [$minAge, $maxAge])
                ->count();

            $femaleCount = Person::where('gender', 'F')
                ->where('is_living', true)
                ->whereNotNull('birth_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN ? AND ?', [$minAge, $maxAge])
                ->count();

            $pyramid[$range] = [
                'M' => $maleCount,
                'F' => $femaleCount,
            ];
        }

        return $pyramid;
    }

    /**
     * Calcular distribución de edades al fallecer.
     */
    protected function calculateDeathAgeDistribution(): array
    {
        $distribution = [];
        $ranges = [
            '0-9', '10-19', '20-29', '30-39', '40-49',
            '50-59', '60-69', '70-79', '80-89', '90+'
        ];

        foreach ($ranges as $index => $range) {
            $minAge = $index * 10;
            $maxAge = $index === 9 ? 200 : ($index + 1) * 10 - 1;

            $count = Person::where('is_living', false)
                ->whereNotNull('birth_date')
                ->whereNotNull('death_date')
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, death_date) BETWEEN ? AND ?', [$minAge, $maxAge])
                ->count();

            $distribution[$range] = $count;
        }

        return $distribution;
    }

    /**
     * Obtener datos interesantes para el reporte de eventos.
     */
    protected function getInterestingFacts(): array
    {
        // Persona más longeva (fallecida con ambas fechas)
        $oldestPerson = Person::where('is_living', false)
            ->whereNotNull('birth_date')
            ->whereNotNull('death_date')
            ->selectRaw('*, TIMESTAMPDIFF(YEAR, birth_date, death_date) as age')
            ->orderByDesc('age')
            ->first();

        $oldestPersonText = $oldestPerson
            ? "{$oldestPerson->full_name} ({$oldestPerson->age} años)"
            : null;

        // Matrimonio más largo
        $longestMarriage = Family::whereNotNull('marriage_date')
            ->whereHas('husband', function($q) {
                $q->where('is_living', false)->whereNotNull('death_date');
            })
            ->orWhereHas('wife', function($q) {
                $q->where('is_living', false)->whereNotNull('death_date');
            })
            ->with(['husband', 'wife'])
            ->get()
            ->map(function($family) {
                $endDate = null;
                if ($family->husband && !$family->husband->is_living && $family->husband->death_date) {
                    $endDate = $family->husband->death_date;
                }
                if ($family->wife && !$family->wife->is_living && $family->wife->death_date) {
                    if (!$endDate || $family->wife->death_date < $endDate) {
                        $endDate = $family->wife->death_date;
                    }
                }
                if ($endDate && $family->marriage_date) {
                    $years = $family->marriage_date->diffInYears($endDate);
                    return ['family' => $family, 'years' => $years];
                }
                return null;
            })
            ->filter()
            ->sortByDesc('years')
            ->first();

        $longestMarriageText = $longestMarriage
            ? "{$longestMarriage['years']} años"
            : null;

        // Mayor número de hijos
        $mostChildren = DB::table('family_children')
            ->select('family_id', DB::raw('count(*) as count'))
            ->groupBy('family_id')
            ->orderByDesc('count')
            ->first();

        $mostChildrenText = $mostChildren
            ? "{$mostChildren->count} hijos"
            : null;

        return [
            'oldest_person' => $oldestPersonText,
            'longest_marriage' => $longestMarriageText,
            'most_children' => $mostChildrenText,
        ];
    }

    /**
     * Detectar problemas en los datos.
     */
    protected function detectDataIssues(int $totalPersons, array $completeness, int $orphans, int $lonely): array
    {
        $issues = [];

        // Problema: Muchas personas sin fecha de nacimiento
        if (isset($completeness['birth_date']) && $completeness['birth_date'] < 50) {
            $count = $totalPersons - ($totalPersons * $completeness['birth_date'] / 100);
            $issues[] = [
                'severity' => 'high',
                'title' => __('Fechas de nacimiento faltantes'),
                'description' => __('Muchas personas no tienen fecha de nacimiento registrada.'),
                'count' => (int) $count,
            ];
        }

        // Problema: Muchos huérfanos (sin padres conocidos)
        if ($orphans > $totalPersons * 0.7 && $totalPersons > 10) {
            $issues[] = [
                'severity' => 'medium',
                'title' => __('Personas sin padres'),
                'description' => __('Alto porcentaje de personas sin padres registrados.'),
                'count' => $orphans,
            ];
        }

        // Problema: Personas aisladas
        if ($lonely > 10) {
            $issues[] = [
                'severity' => 'low',
                'title' => __('Personas sin conexiones familiares'),
                'description' => __('Personas que no están vinculadas a ninguna familia.'),
                'count' => $lonely,
            ];
        }

        // Problema: Pocas fotos
        if (isset($completeness['photo']) && $completeness['photo'] < 20 && $totalPersons > 20) {
            $count = $totalPersons - ($totalPersons * $completeness['photo'] / 100);
            $issues[] = [
                'severity' => 'low',
                'title' => __('Fotos faltantes'),
                'description' => __('La mayoría de las personas no tienen foto de perfil.'),
                'count' => (int) $count,
            ];
        }

        // Problema: Personas fallecidas sin fecha de defunción
        $deceasedNoDate = Person::where('is_living', false)->whereNull('death_date')->count();
        if ($deceasedNoDate > 0) {
            $issues[] = [
                'severity' => 'medium',
                'title' => __('Fallecidos sin fecha de defunción'),
                'description' => __('Personas marcadas como fallecidas pero sin fecha de defunción.'),
                'count' => $deceasedNoDate,
            ];
        }

        return $issues;
    }

    /**
     * Generar recomendaciones basadas en los datos.
     */
    protected function generateRecommendations(array $personCompleteness, array $familyCompleteness): array
    {
        $recommendations = [];

        // Recomendación: Completar fechas de nacimiento
        if (isset($personCompleteness['birth_date']) && $personCompleteness['birth_date'] < 80) {
            $recommendations[] = [
                'title' => __('Completar fechas de nacimiento'),
                'description' => __('Las fechas de nacimiento son esenciales para calcular relaciones y edades. Intente completar este campo para más personas.'),
            ];
        }

        // Recomendacion: Agregar origen de herencia
        if (isset($personCompleteness['heritage_origin']) && $personCompleteness['heritage_origin'] < 30) {
            $recommendations[] = [
                'title' => __('Registrar origen de herencia'),
                'description' => __('Agregue la region de origen de donde provienen las familias para preservar el patrimonio cultural.'),
            ];
        }

        // Recomendación: Agregar fotos
        if (isset($personCompleteness['photo']) && $personCompleteness['photo'] < 30) {
            $recommendations[] = [
                'title' => __('Agregar fotografías'),
                'description' => __('Las fotos ayudan a identificar a las personas y hacen el árbol más personal.'),
            ];
        }

        // Recomendación: Completar fechas de matrimonio
        if (isset($familyCompleteness['marriage_date']) && $familyCompleteness['marriage_date'] < 50) {
            $recommendations[] = [
                'title' => __('Registrar fechas de matrimonio'),
                'description' => __('Las fechas de matrimonio son importantes para la cronología familiar.'),
            ];
        }

        // Recomendación: Vincular hijos
        if (isset($familyCompleteness['children']) && $familyCompleteness['children'] < 40) {
            $recommendations[] = [
                'title' => __('Vincular hijos a familias'),
                'description' => __('Muchas familias no tienen hijos registrados. Verifique si hay personas que deberían estar vinculadas.'),
            ];
        }

        return $recommendations;
    }

    /**
     * Encontrar posibles registros duplicados.
     */
    protected function findPotentialDuplicates(): array
    {
        $duplicates = [];

        // Buscar personas con nombre y apellido idénticos
        $possibleDups = Person::select('first_name', 'patronymic', DB::raw('count(*) as count'))
            ->whereNotNull('first_name')
            ->whereNotNull('patronymic')
            ->where('first_name', '!=', '')
            ->where('patronymic', '!=', '')
            ->groupBy('first_name', 'patronymic')
            ->having('count', '>', 1)
            ->limit(10)
            ->get();

        foreach ($possibleDups as $dup) {
            $persons = Person::where('first_name', $dup->first_name)
                ->where('patronymic', $dup->patronymic)
                ->limit(2)
                ->get();

            if ($persons->count() >= 2) {
                // Calcular similitud basada en fecha de nacimiento
                $similarity = 70; // Nombre idéntico base
                $reason = __('Mismo nombre y apellido');

                if ($persons[0]->birth_date && $persons[1]->birth_date) {
                    if ($persons[0]->birth_date->format('Y-m-d') === $persons[1]->birth_date->format('Y-m-d')) {
                        $similarity = 95;
                        $reason = __('Nombre, apellido y fecha de nacimiento idénticos');
                    } elseif ($persons[0]->birth_date->format('Y') === $persons[1]->birth_date->format('Y')) {
                        $similarity = 85;
                        $reason = __('Mismo nombre, apellido y año de nacimiento');
                    }
                }

                $duplicates[] = [
                    'person1' => $persons[0],
                    'person2' => $persons[1],
                    'similarity' => $similarity,
                    'reason' => $reason,
                ];
            }
        }

        return $duplicates;
    }
}
