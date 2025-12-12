<?php

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditGenealogyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'genealogy:audit
                            {--fix : Automatically fix issues where possible}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit genealogy data for inconsistencies like cycles, self-references, etc.';

    protected int $issuesFound = 0;
    protected int $issuesFixed = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Auditoría de Datos Genealógicos ===');
        $this->newLine();

        $fix = $this->option('fix');

        // 1. Personas que son padre/madre e hijo en la misma familia
        $this->checkSelfParentChild($fix);

        // 2. Familias sin ningún padre
        $this->checkFamiliesWithoutParents($fix);

        // 3. Ciclos genealógicos
        $this->checkGenealogicalCycles($fix);

        // 4. Hijos huérfanos (en family_children pero persona no existe)
        $this->checkOrphanChildren($fix);

        // 5. Referencias a personas inexistentes en familias
        $this->checkInvalidPersonReferences($fix);

        // Resumen
        $this->newLine();
        $this->info('=== Resumen ===');
        $this->line("Problemas encontrados: {$this->issuesFound}");
        if ($fix) {
            $this->line("Problemas corregidos: {$this->issuesFixed}");
        }

        if ($this->issuesFound > 0 && !$fix) {
            $this->warn('Ejecuta con --fix para corregir los problemas automáticamente.');
        }

        return $this->issuesFound > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Verificar personas que son padre/madre e hijo en la misma familia.
     */
    protected function checkSelfParentChild(bool $fix): void
    {
        $this->info('1. Verificando personas que son padre e hijo en la misma familia...');

        $problems = DB::select("
            SELECT fc.family_id, fc.person_id, f.husband_id, f.wife_id,
                   p.first_name, p.patronymic
            FROM family_children fc
            JOIN families f ON fc.family_id = f.id
            JOIN persons p ON fc.person_id = p.id
            WHERE fc.person_id = f.husband_id OR fc.person_id = f.wife_id
        ");

        if (empty($problems)) {
            $this->line('   ✓ No se encontraron problemas.');
            return;
        }

        foreach ($problems as $problem) {
            $this->issuesFound++;
            $role = $problem->person_id == $problem->husband_id ? 'padre' : 'madre';
            $name = trim("{$problem->first_name} {$problem->patronymic}");

            $this->error("   ✗ Familia #{$problem->family_id}: {$name} (ID:{$problem->person_id}) es {$role} e hijo.");

            if ($fix) {
                // Eliminar la relación hijo
                FamilyChild::where('family_id', $problem->family_id)
                    ->where('person_id', $problem->person_id)
                    ->delete();
                $this->issuesFixed++;
                $this->line("     → Eliminada relación de hijo.");
            }
        }
    }

    /**
     * Verificar familias sin padres.
     */
    protected function checkFamiliesWithoutParents(bool $fix): void
    {
        $this->newLine();
        $this->info('2. Verificando familias sin padres definidos...');

        $families = Family::whereNull('husband_id')
            ->whereNull('wife_id')
            ->get();

        if ($families->isEmpty()) {
            $this->line('   ✓ No se encontraron problemas.');
            return;
        }

        foreach ($families as $family) {
            $this->issuesFound++;
            $childCount = $family->children()->count();

            $this->warn("   ⚠ Familia #{$family->id}: Sin padres definidos ({$childCount} hijos).");

            if ($fix && $childCount === 0) {
                // Eliminar familia vacía
                $family->delete();
                $this->issuesFixed++;
                $this->line("     → Familia vacía eliminada.");
            }
        }
    }

    /**
     * Verificar ciclos genealógicos.
     */
    protected function checkGenealogicalCycles(bool $fix): void
    {
        $this->newLine();
        $this->info('3. Verificando ciclos genealógicos...');

        $personsWithCycles = [];

        // Construir mapa de padres
        $parentMap = $this->buildParentMap();

        // Verificar cada persona
        $persons = Person::select('id', 'first_name', 'patronymic')->get();

        foreach ($persons as $person) {
            $ancestors = $this->collectAncestors($person->id, $parentMap, []);
            if (in_array($person->id, $ancestors)) {
                $personsWithCycles[] = $person;
            }
        }

        if (empty($personsWithCycles)) {
            $this->line('   ✓ No se encontraron ciclos genealógicos.');
            return;
        }

        foreach ($personsWithCycles as $person) {
            $this->issuesFound++;
            $name = trim("{$person->first_name} {$person->patronymic}");
            $this->error("   ✗ {$name} (ID:{$person->id}) es ancestro de sí mismo.");

            if ($fix) {
                // Identificar y eliminar la relación problemática
                $this->fixCycle($person->id, $parentMap);
            }
        }
    }

    /**
     * Verificar hijos huérfanos (relación existe pero persona no).
     */
    protected function checkOrphanChildren(bool $fix): void
    {
        $this->newLine();
        $this->info('4. Verificando relaciones huérfanas...');

        $orphans = DB::select("
            SELECT fc.id, fc.family_id, fc.person_id
            FROM family_children fc
            LEFT JOIN persons p ON fc.person_id = p.id
            WHERE p.id IS NULL
        ");

        if (empty($orphans)) {
            $this->line('   ✓ No se encontraron relaciones huérfanas.');
            return;
        }

        foreach ($orphans as $orphan) {
            $this->issuesFound++;
            $this->error("   ✗ Relación #{$orphan->id}: Persona {$orphan->person_id} no existe.");

            if ($fix) {
                FamilyChild::where('id', $orphan->id)->delete();
                $this->issuesFixed++;
                $this->line("     → Relación eliminada.");
            }
        }
    }

    /**
     * Verificar referencias a personas inexistentes.
     */
    protected function checkInvalidPersonReferences(bool $fix): void
    {
        $this->newLine();
        $this->info('5. Verificando referencias inválidas en familias...');

        // Esposos inexistentes
        $invalidHusbands = DB::select("
            SELECT f.id, f.husband_id
            FROM families f
            LEFT JOIN persons p ON f.husband_id = p.id
            WHERE f.husband_id IS NOT NULL AND p.id IS NULL
        ");

        // Esposas inexistentes
        $invalidWives = DB::select("
            SELECT f.id, f.wife_id
            FROM families f
            LEFT JOIN persons p ON f.wife_id = p.id
            WHERE f.wife_id IS NOT NULL AND p.id IS NULL
        ");

        if (empty($invalidHusbands) && empty($invalidWives)) {
            $this->line('   ✓ No se encontraron referencias inválidas.');
            return;
        }

        foreach ($invalidHusbands as $invalid) {
            $this->issuesFound++;
            $this->error("   ✗ Familia #{$invalid->id}: husband_id {$invalid->husband_id} no existe.");

            if ($fix) {
                Family::where('id', $invalid->id)->update(['husband_id' => null]);
                $this->issuesFixed++;
                $this->line("     → husband_id establecido a NULL.");
            }
        }

        foreach ($invalidWives as $invalid) {
            $this->issuesFound++;
            $this->error("   ✗ Familia #{$invalid->id}: wife_id {$invalid->wife_id} no existe.");

            if ($fix) {
                Family::where('id', $invalid->id)->update(['wife_id' => null]);
                $this->issuesFixed++;
                $this->line("     → wife_id establecido a NULL.");
            }
        }
    }

    /**
     * Construir mapa de padres.
     */
    protected function buildParentMap(): array
    {
        $parentMap = [];

        $families = Family::with('children')->get();

        foreach ($families as $family) {
            foreach ($family->children as $child) {
                if (!isset($parentMap[$child->id])) {
                    $parentMap[$child->id] = ['fathers' => [], 'mothers' => []];
                }
                if ($family->husband_id) {
                    $parentMap[$child->id]['fathers'][] = $family->husband_id;
                }
                if ($family->wife_id) {
                    $parentMap[$child->id]['mothers'][] = $family->wife_id;
                }
            }
        }

        return $parentMap;
    }

    /**
     * Recolectar ancestros recursivamente.
     */
    protected function collectAncestors(int $personId, array $parentMap, array $visited): array
    {
        if (in_array($personId, $visited)) {
            return [];
        }
        $visited[] = $personId;
        $ancestors = [];

        if (isset($parentMap[$personId])) {
            foreach ($parentMap[$personId]['fathers'] as $fatherId) {
                $ancestors[] = $fatherId;
                $ancestors = array_merge($ancestors, $this->collectAncestors($fatherId, $parentMap, $visited));
            }
            foreach ($parentMap[$personId]['mothers'] as $motherId) {
                $ancestors[] = $motherId;
                $ancestors = array_merge($ancestors, $this->collectAncestors($motherId, $parentMap, $visited));
            }
        }

        return array_unique($ancestors);
    }

    /**
     * Intentar arreglar un ciclo eliminando la relación más reciente.
     */
    protected function fixCycle(int $personId, array $parentMap): void
    {
        // Encontrar qué familia causa el ciclo y eliminar esa relación
        $familyChild = FamilyChild::where('person_id', $personId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($familyChild) {
            $familyChild->delete();
            $this->issuesFixed++;
            $this->line("     → Eliminada relación de hijo más reciente (Familia #{$familyChild->family_id}).");
        }
    }
}
