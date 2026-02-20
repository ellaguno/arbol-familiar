<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;

class FixSurnames extends Command
{
    protected $signature = 'genealogy:fix-surnames
                            {--dry-run : Solo mostrar cambios sin aplicarlos}
                            {--person= : ID de persona especifica para revisar}';

    protected $description = 'Revisa y corrige apellidos separando patronymic/matronymic usando datos de padres';

    protected int $reviewed = 0;
    protected int $fixed = 0;
    protected int $skipped = 0;
    protected array $changes = [];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $personId = $this->option('person');

        $this->info('');
        $this->info('=== Revision de apellidos ===');
        $this->info($dryRun ? '(Modo simulacion - no se aplicaran cambios)' : '(Modo correccion - se aplicaran cambios confirmados)');
        $this->info('');

        $query = Person::query();
        if ($personId) {
            $query->where('id', $personId);
        }

        $persons = $query->orderBy('id')->get();

        foreach ($persons as $person) {
            $this->reviewPerson($person, $dryRun);
        }

        $this->info('');
        $this->info('=== Resumen ===');
        $this->info("Personas revisadas: {$this->reviewed}");
        $this->info("Corregidas: {$this->fixed}");
        $this->info("Sin cambios necesarios: {$this->skipped}");

        if ($dryRun && $this->fixed > 0) {
            $this->info('');
            $this->warn('Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return Command::SUCCESS;
    }

    protected function reviewPerson(Person $person, bool $dryRun): void
    {
        $this->reviewed++;

        $patronymic = trim($person->patronymic ?? '');
        $matronymic = trim($person->matronymic ?? '');

        // Caso 1: patronymic tiene espacios (posibles dos apellidos juntos) y matronymic vacio
        if ($matronymic === '' && str_contains($patronymic, ' ')) {
            $this->handleCompoundPatronymic($person, $patronymic, $dryRun);
            return;
        }

        // Caso 2: tiene ambos apellidos pero podrian estar invertidos o mal asignados
        if ($patronymic !== '' && $matronymic !== '') {
            $this->validateAgainstParents($person, $patronymic, $matronymic, $dryRun);
            return;
        }

        // Caso 3: tiene patronymic simple y matronymic vacio - intentar completar desde madre
        if ($patronymic !== '' && $matronymic === '') {
            $this->tryFillMatronymic($person, $patronymic, $dryRun);
            return;
        }

        $this->skipped++;
    }

    /**
     * Caso principal: "Llaguno Velasco" en patronymic, matronymic vacio.
     * Intenta separar usando datos de los padres.
     */
    protected function handleCompoundPatronymic(Person $person, string $patronymic, bool $dryRun): void
    {
        $parts = preg_split('/\s+/', $patronymic);

        if (count($parts) < 2) {
            $this->skipped++;
            return;
        }

        $father = $person->father;
        $mother = $person->mother;

        // Estrategia 1: Buscar coincidencia con apellido del padre y la madre
        if ($father && $mother) {
            $fatherSurname = trim($father->patronymic ?? '');
            $motherSurname = trim($mother->patronymic ?? '');

            if ($fatherSurname !== '' && $motherSurname !== '') {
                // Buscar donde esta el apellido del padre y de la madre en la cadena
                $result = $this->findSplitByParents($parts, $fatherSurname, $motherSurname);
                if ($result) {
                    $this->proposeFix($person, $result['patronymic'], $result['matronymic'],
                        "padre={$fatherSurname}, madre={$motherSurname}", $dryRun);
                    return;
                }
            }
        }

        // Estrategia 2: Solo padre conocido - su apellido es el patronymic, el resto es matronymic
        if ($father && !$mother) {
            $fatherSurname = trim($father->patronymic ?? '');
            if ($fatherSurname !== '') {
                $result = $this->splitByKnownSurname($parts, $fatherSurname, 'father');
                if ($result) {
                    $this->proposeFix($person, $result['patronymic'], $result['matronymic'],
                        "padre={$fatherSurname}, madre desconocida", $dryRun);
                    return;
                }
            }
        }

        // Estrategia 3: Solo madre conocida - su apellido es el matronymic
        if (!$father && $mother) {
            $motherSurname = trim($mother->patronymic ?? '');
            if ($motherSurname !== '') {
                $result = $this->splitByKnownSurname($parts, $motherSurname, 'mother');
                if ($result) {
                    $this->proposeFix($person, $result['patronymic'], $result['matronymic'],
                        "padre desconocido, madre={$motherSurname}", $dryRun);
                    return;
                }
            }
        }

        // Estrategia 4: Buscar entre hermanos que ya tengan apellidos separados
        $sibling = $this->findSiblingWithSeparatedSurnames($person);
        if ($sibling) {
            $sibPat = trim($sibling->patronymic ?? '');
            $sibMat = trim($sibling->matronymic ?? '');
            if ($sibPat !== '' && $sibMat !== '') {
                $result = $this->findSplitByParents($parts, $sibPat, $sibMat);
                if ($result) {
                    $this->proposeFix($person, $result['patronymic'], $result['matronymic'],
                        "hermano/a {$sibling->first_name}: pat={$sibPat}, mat={$sibMat}", $dryRun);
                    return;
                }
            }
        }

        // Estrategia 5: Buscar entre hijos - si un hijo tiene patronymic que coincide
        // con parte de nuestro patronymic compuesto, eso confirma la separacion
        $child = $this->findChildWithMatchingSurname($person, $parts);
        if ($child) {
            $childPat = trim($child->patronymic ?? '');
            if ($childPat !== '' && $childPat !== $patronymic) {
                $result = $this->splitByKnownSurname($parts, $childPat, 'father');
                if ($result && $person->gender === 'M') {
                    $this->proposeFix($person, $result['patronymic'], $result['matronymic'],
                        "hijo/a {$child->first_name} tiene patronymic={$childPat}", $dryRun);
                    return;
                }
                // Si es mujer, su patronymic deberia ser el matronymic del hijo
                if ($result && $person->gender === 'F') {
                    $childMat = trim($child->matronymic ?? '');
                    if ($childMat !== '') {
                        $resultM = $this->splitByKnownSurname($parts, $childMat, 'father');
                        if ($resultM) {
                            $this->proposeFix($person, $resultM['patronymic'], $resultM['matronymic'],
                                "hijo/a {$child->first_name} tiene matronymic={$childMat}", $dryRun);
                            return;
                        }
                    }
                }
            }
        }

        // Estrategia 6: Sin padres ni hermanos - asumir mitad y mitad si son exactamente 2 partes
        if (count($parts) === 2) {
            $this->proposeFix($person, $parts[0], $parts[1],
                'sin padres conocidos, asumiendo 2 apellidos simples', $dryRun);
            return;
        }

        // No se pudo determinar automaticamente
        $this->warn("  [?] #{$person->id} {$person->first_name} \"{$patronymic}\" - No se pudo separar automaticamente (sin datos de padres)");
        $this->skipped++;
    }

    /**
     * Valida apellidos existentes contra datos de padres.
     */
    protected function validateAgainstParents(Person $person, string $patronymic, string $matronymic, bool $dryRun): void
    {
        $father = $person->father;
        $mother = $person->mother;

        $issues = [];

        if ($father) {
            $fatherSurname = trim($father->patronymic ?? '');
            if ($fatherSurname !== '' && mb_strtolower($fatherSurname) !== mb_strtolower($patronymic)) {
                $issues[] = "patronymic \"{$patronymic}\" no coincide con padre \"{$fatherSurname}\"";
            }
        }

        if ($mother) {
            $motherSurname = trim($mother->patronymic ?? '');
            if ($motherSurname !== '' && mb_strtolower($motherSurname) !== mb_strtolower($matronymic)) {
                $issues[] = "matronymic \"{$matronymic}\" no coincide con madre \"{$motherSurname}\"";
            }
        }

        if (!empty($issues)) {
            $this->warn("  [!] #{$person->id} {$person->first_name} {$patronymic} {$matronymic} - " . implode('; ', $issues));
            $this->skipped++;
        } else {
            $this->skipped++;
        }
    }

    /**
     * Intenta completar matronymic vacio desde la madre.
     */
    protected function tryFillMatronymic(Person $person, string $patronymic, bool $dryRun): void
    {
        $mother = $person->mother;

        if ($mother) {
            $motherSurname = trim($mother->patronymic ?? '');
            if ($motherSurname !== '') {
                $this->proposeFix($person, $patronymic, $motherSurname,
                    "completar matronymic desde madre ({$mother->first_name} {$motherSurname})", $dryRun);
                return;
            }
        }

        $this->skipped++;
    }

    /**
     * Busca la separacion correcta cuando se conocen ambos apellidos de padres.
     */
    protected function findSplitByParents(array $parts, string $fatherSurname, string $motherSurname): ?array
    {
        $fullString = implode(' ', $parts);
        $fatherLower = mb_strtolower($fatherSurname);
        $motherLower = mb_strtolower($motherSurname);
        $fullLower = mb_strtolower($fullString);

        // Buscar posicion del apellido del padre al inicio
        if (str_starts_with($fullLower, $fatherLower)) {
            $rest = trim(mb_substr($fullString, mb_strlen($fatherSurname)));
            // Verificar que el resto coincide con el apellido de la madre
            if (mb_strtolower($rest) === $motherLower || $motherLower === '') {
                return [
                    'patronymic' => mb_substr($fullString, 0, mb_strlen($fatherSurname)),
                    'matronymic' => $rest ?: $motherSurname,
                ];
            }
            // Si no coincide exactamente pero hay un resto, usarlo
            if ($rest !== '') {
                return [
                    'patronymic' => mb_substr($fullString, 0, mb_strlen($fatherSurname)),
                    'matronymic' => $rest,
                ];
            }
        }

        return null;
    }

    /**
     * Separa cuando se conoce un solo apellido (padre o madre).
     */
    protected function splitByKnownSurname(array $parts, string $knownSurname, string $which): ?array
    {
        $fullString = implode(' ', $parts);
        $knownLower = mb_strtolower($knownSurname);
        $fullLower = mb_strtolower($fullString);

        if ($which === 'father') {
            // El apellido del padre debe estar al inicio
            if (str_starts_with($fullLower, $knownLower)) {
                $rest = trim(mb_substr($fullString, mb_strlen($knownSurname)));
                if ($rest !== '') {
                    return [
                        'patronymic' => mb_substr($fullString, 0, mb_strlen($knownSurname)),
                        'matronymic' => $rest,
                    ];
                }
            }
        } else {
            // El apellido de la madre debe estar al final
            if (str_ends_with($fullLower, $knownLower)) {
                $rest = trim(mb_substr($fullString, 0, mb_strlen($fullString) - mb_strlen($knownSurname)));
                if ($rest !== '') {
                    return [
                        'patronymic' => $rest,
                        'matronymic' => mb_substr($fullString, mb_strlen($fullString) - mb_strlen($knownSurname)),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Busca un hermano que ya tenga los apellidos correctamente separados.
     */
    protected function findSiblingWithSeparatedSurnames(Person $person): ?Person
    {
        $family = $person->familiesAsChild()->first();
        if (!$family) {
            return null;
        }

        return Person::whereHas('familiesAsChild', function ($q) use ($family) {
            $q->where('families.id', $family->id);
        })
            ->where('id', '!=', $person->id)
            ->whereNotNull('matronymic')
            ->where('matronymic', '!=', '')
            ->first();
    }

    /**
     * Busca un hijo que tenga un patronymic que coincida con parte del patronymic compuesto.
     */
    protected function findChildWithMatchingSurname(Person $person, array $parts): ?Person
    {
        $children = $person->children;
        if ($children->isEmpty()) {
            return null;
        }

        foreach ($children as $child) {
            $childPat = trim($child->patronymic ?? '');
            if ($childPat !== '' && in_array(mb_strtolower($childPat), array_map('mb_strtolower', $parts))) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Propone y aplica (o simula) una correccion.
     */
    protected function proposeFix(Person $person, string $newPatronymic, string $newMatronymic, string $reason, bool $dryRun): void
    {
        $oldPatronymic = $person->patronymic ?? '';
        $oldMatronymic = $person->matronymic ?? '';

        // Si ya esta correcto, saltar
        if ($oldPatronymic === $newPatronymic && $oldMatronymic === $newMatronymic) {
            $this->skipped++;
            return;
        }

        $label = $dryRun ? 'SIMULACION' : 'CORREGIDO';

        $this->line("  [{$label}] #{$person->id} {$person->first_name}");
        $this->line("    Antes:   pat=\"{$oldPatronymic}\" mat=\"{$oldMatronymic}\"");
        $this->line("    Despues: pat=\"{$newPatronymic}\" mat=\"{$newMatronymic}\"");
        $this->line("    Razon:   {$reason}");

        if (!$dryRun) {
            if ($this->confirm("    Aplicar este cambio?", true)) {
                $person->update([
                    'patronymic' => $newPatronymic,
                    'matronymic' => $newMatronymic,
                ]);
                $this->info("    ✓ Aplicado");
                $this->fixed++;
            } else {
                $this->line("    - Omitido");
                $this->skipped++;
            }
        } else {
            $this->fixed++;
        }
    }
}
