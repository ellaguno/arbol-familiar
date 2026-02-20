<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index()
    {
        return view('admin.tools.index');
    }

    public function fixSurnames()
    {
        $persons = Person::orderBy('id')->get();
        $proposals = [];
        $warnings = [];
        $skipped = 0;

        foreach ($persons as $person) {
            $result = $this->analyzePerson($person);
            if ($result) {
                if ($result['new_pat'] === null) {
                    $warnings[] = $result;
                } else {
                    $proposals[] = $result;
                }
            } else {
                $skipped++;
            }
        }

        $byConfidence = ['alta' => [], 'media' => [], 'baja' => []];
        foreach ($proposals as $p) {
            $byConfidence[$p['confidence']][] = $p;
        }

        return view('admin.tools.fix-surnames', [
            'byConfidence' => $byConfidence,
            'warnings' => $warnings,
            'total' => count($persons),
            'fixable' => count($proposals),
            'uncertain' => count($warnings),
            'skipped' => $skipped,
        ]);
    }

    public function applyFixSurnames(Request $request)
    {
        $applyIds = $request->input('apply', []);
        $applied = 0;
        $errors = 0;
        $details = [];

        foreach ($applyIds as $id => $data) {
            $person = Person::find($id);
            if (!$person) {
                $errors++;
                $details[] = ['error' => true, 'message' => "Persona #{$id} no encontrada"];
                continue;
            }

            $newPat = $data['pat'] ?? '';
            $newMat = $data['mat'] ?? '';

            if ($newPat === '' && $newMat === '') {
                continue;
            }

            $oldPat = $person->patronymic ?? '';
            $oldMat = $person->matronymic ?? '';

            $person->update(['patronymic' => $newPat, 'matronymic' => $newMat]);

            $details[] = [
                'error' => false,
                'message' => "#{$id} {$person->first_name}: \"{$oldPat}\" \"{$oldMat}\" → \"{$newPat}\" \"{$newMat}\"",
            ];
            $applied++;
        }

        return redirect()->route('admin.tools.fix-surnames')
            ->with('success', "Se corrigieron {$applied} apellidos." . ($errors ? " Errores: {$errors}." : ''))
            ->with('details', $details);
    }

    // ========================================================================
    // Logica de analisis de apellidos
    // ========================================================================

    private function analyzePerson(Person $person): ?array
    {
        $patronymic = trim($person->patronymic ?? '');
        $matronymic = trim($person->matronymic ?? '');

        // Caso 1: patronymic compuesto con matronymic vacio
        if ($matronymic === '' && str_contains($patronymic, ' ')) {
            $parts = preg_split('/\s+/', $patronymic);
            if (count($parts) < 2) {
                return null;
            }

            $father = $person->father;
            $mother = $person->mother;

            // Estrategia 1: Ambos padres
            if ($father && $mother) {
                $fp = trim($father->patronymic ?? '');
                $mp = trim($mother->patronymic ?? '');
                if ($fp && $mp) {
                    $r = $this->findSplitByParents($parts, $fp, $mp);
                    if ($r) {
                        return $this->buildResult($person, $patronymic, $matronymic,
                            $r['patronymic'], $r['matronymic'],
                            "padre={$fp}, madre={$mp}", 'alta');
                    }
                }
            }

            // Estrategia 2: Solo padre
            if ($father && !$mother) {
                $fp = trim($father->patronymic ?? '');
                if ($fp) {
                    $r = $this->splitByKnownSurname($parts, $fp, 'father');
                    if ($r) {
                        return $this->buildResult($person, $patronymic, $matronymic,
                            $r['patronymic'], $r['matronymic'],
                            "padre={$fp}", 'media');
                    }
                }
            }

            // Estrategia 3: Solo madre
            if (!$father && $mother) {
                $mp = trim($mother->patronymic ?? '');
                if ($mp) {
                    $r = $this->splitByKnownSurname($parts, $mp, 'mother');
                    if ($r) {
                        return $this->buildResult($person, $patronymic, $matronymic,
                            $r['patronymic'], $r['matronymic'],
                            "madre={$mp}", 'media');
                    }
                }
            }

            // Estrategia 4: Hermano
            $sibling = $this->findSiblingWithSurnames($person);
            if ($sibling) {
                $sp = trim($sibling->patronymic ?? '');
                $sm = trim($sibling->matronymic ?? '');
                if ($sp && $sm) {
                    $r = $this->findSplitByParents($parts, $sp, $sm);
                    if ($r) {
                        return $this->buildResult($person, $patronymic, $matronymic,
                            $r['patronymic'], $r['matronymic'],
                            "hermano/a {$sibling->first_name}: {$sp} {$sm}", 'media');
                    }
                }
            }

            // Estrategia 5: Hijos
            $child = $this->findChildMatch($person, $parts);
            if ($child) {
                $cp = trim($child->patronymic ?? '');
                if ($cp && $person->gender === 'M') {
                    $r = $this->splitByKnownSurname($parts, $cp, 'father');
                    if ($r) {
                        return $this->buildResult($person, $patronymic, $matronymic,
                            $r['patronymic'], $r['matronymic'],
                            "hijo/a {$child->first_name} pat={$cp}", 'media');
                    }
                }
                if ($person->gender === 'F') {
                    $cm = trim($child->matronymic ?? '');
                    if ($cm) {
                        $r = $this->splitByKnownSurname($parts, $cm, 'father');
                        if ($r) {
                            return $this->buildResult($person, $patronymic, $matronymic,
                                $r['patronymic'], $r['matronymic'],
                                "hijo/a {$child->first_name} mat={$cm}", 'media');
                        }
                    }
                }
            }

            // Estrategia 6: Dos palabras simples
            if (count($parts) === 2) {
                return $this->buildResult($person, $patronymic, $matronymic,
                    $parts[0], $parts[1],
                    'sin padres, asumiendo 2 apellidos', 'baja');
            }

            return $this->buildResult($person, $patronymic, $matronymic,
                null, null,
                'no se pudo separar automaticamente', 'ninguna');
        }

        // Caso 2: matronymic vacio, madre conocida
        if ($patronymic !== '' && $matronymic === '' && !str_contains($patronymic, ' ')) {
            $mother = $person->mother;
            if ($mother) {
                $mp = trim($mother->patronymic ?? '');
                if ($mp) {
                    return $this->buildResult($person, $patronymic, '',
                        $patronymic, $mp,
                        "completar desde madre ({$mother->first_name} {$mp})", 'alta');
                }
            }
        }

        return null;
    }

    private function buildResult(Person $person, string $oldPat, string $oldMat, ?string $newPat, ?string $newMat, string $reason, string $confidence): array
    {
        return [
            'person' => $person,
            'old_pat' => $oldPat,
            'old_mat' => $oldMat,
            'new_pat' => $newPat,
            'new_mat' => $newMat,
            'reason' => $reason,
            'confidence' => $confidence,
        ];
    }

    private function findSplitByParents(array $parts, string $fatherSurname, string $motherSurname): ?array
    {
        $fullString = implode(' ', $parts);
        $fatherLower = mb_strtolower($fatherSurname);
        $fullLower = mb_strtolower($fullString);

        if (str_starts_with($fullLower, $fatherLower)) {
            $rest = trim(mb_substr($fullString, mb_strlen($fatherSurname)));
            if ($rest !== '') {
                return ['patronymic' => mb_substr($fullString, 0, mb_strlen($fatherSurname)), 'matronymic' => $rest];
            }
        }

        return null;
    }

    private function splitByKnownSurname(array $parts, string $knownSurname, string $which): ?array
    {
        $fullString = implode(' ', $parts);
        $knownLower = mb_strtolower($knownSurname);
        $fullLower = mb_strtolower($fullString);

        if ($which === 'father' && str_starts_with($fullLower, $knownLower)) {
            $rest = trim(mb_substr($fullString, mb_strlen($knownSurname)));
            if ($rest !== '') {
                return ['patronymic' => mb_substr($fullString, 0, mb_strlen($knownSurname)), 'matronymic' => $rest];
            }
        } elseif ($which === 'mother' && str_ends_with($fullLower, $knownLower)) {
            $rest = trim(mb_substr($fullString, 0, mb_strlen($fullString) - mb_strlen($knownSurname)));
            if ($rest !== '') {
                return ['patronymic' => $rest, 'matronymic' => mb_substr($fullString, mb_strlen($fullString) - mb_strlen($knownSurname))];
            }
        }

        return null;
    }

    private function findSiblingWithSurnames(Person $person): ?Person
    {
        $family = $person->familiesAsChild()->first();
        if (!$family) {
            return null;
        }

        return Person::whereHas('familiesAsChild', fn ($q) => $q->where('families.id', $family->id))
            ->where('id', '!=', $person->id)
            ->whereNotNull('matronymic')
            ->where('matronymic', '!=', '')
            ->first();
    }

    private function findChildMatch(Person $person, array $parts): ?Person
    {
        foreach ($person->children as $child) {
            $childPat = trim($child->patronymic ?? '');
            if ($childPat !== '' && in_array(mb_strtolower($childPat), array_map('mb_strtolower', $parts))) {
                return $child;
            }
        }

        return null;
    }
}
