<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Invitation;
use App\Models\Media;
use App\Models\Message;
use App\Models\Person;
use App\Models\PersonEditPermission;
use App\Models\SurnameVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    // Buscar y fusionar duplicados
    // ========================================================================

    public function duplicates()
    {
        $groups = $this->findDuplicateGroups();

        $byConfidence = ['alta' => [], 'media' => [], 'baja' => []];
        $totalDuplicates = 0;

        foreach ($groups as $group) {
            $byConfidence[$group['confidence']][] = $group;
            $totalDuplicates += count($group['persons']) - 1;
        }

        return view('admin.tools.duplicates', [
            'byConfidence' => $byConfidence,
            'totalGroups' => count($groups),
            'totalDuplicates' => $totalDuplicates,
            'totalPersons' => Person::count(),
        ]);
    }

    public function compareDuplicates(Person $personA, Person $personB)
    {
        $personA->load(['events', 'media', 'familiesAsChild', 'familiesAsHusband', 'familiesAsWife']);
        $personB->load(['events', 'media', 'familiesAsChild', 'familiesAsHusband', 'familiesAsWife']);

        $fields = $this->buildComparisonFields($personA, $personB);

        return view('admin.tools.duplicates-compare', [
            'personA' => $personA,
            'personB' => $personB,
            'fields' => $fields,
        ]);
    }

    public function mergeDuplicates(Request $request)
    {
        $request->validate([
            'primary_id' => 'required|exists:persons,id',
            'duplicate_id' => 'required|exists:persons,id|different:primary_id',
        ]);

        $primaryId = $request->input('primary_id');
        $duplicateId = $request->input('duplicate_id');
        $primary = Person::findOrFail($primaryId);
        $duplicate = Person::findOrFail($duplicateId);

        $summary = [];

        DB::transaction(function () use ($primaryId, $duplicateId, $primary, $duplicate, &$summary) {
            // 1. Transferir family_children (evitar duplicados en misma familia)
            $childRecords = FamilyChild::where('person_id', $duplicateId)->get();
            $transferred = 0;
            foreach ($childRecords as $record) {
                $exists = FamilyChild::where('family_id', $record->family_id)
                    ->where('person_id', $primaryId)->exists();
                if (!$exists) {
                    $record->update(['person_id' => $primaryId]);
                    $transferred++;
                } else {
                    $record->delete();
                }
            }
            if ($transferred) $summary[] = "{$transferred} relacion(es) hijo-familia transferidas";

            // 2. Transferir Family husband_id / wife_id
            $husbandUpdated = Family::where('husband_id', $duplicateId)->update(['husband_id' => $primaryId]);
            $wifeUpdated = Family::where('wife_id', $duplicateId)->update(['wife_id' => $primaryId]);
            if ($husbandUpdated) $summary[] = "{$husbandUpdated} familia(s) como padre transferidas";
            if ($wifeUpdated) $summary[] = "{$wifeUpdated} familia(s) como madre transferidas";

            // 3. Transferir eventos
            $eventsUpdated = Event::where('person_id', $duplicateId)->update(['person_id' => $primaryId]);
            if ($eventsUpdated) $summary[] = "{$eventsUpdated} evento(s) transferidos";

            // 4. Transferir media (polymorphic)
            $mediaUpdated = Media::where('mediable_type', 'App\\Models\\Person')
                ->where('mediable_id', $duplicateId)
                ->update(['mediable_id' => $primaryId]);
            if ($mediaUpdated) $summary[] = "{$mediaUpdated} archivo(s) multimedia transferidos";

            // 5. Transferir otros registros vinculados
            $surnameUpdated = SurnameVariant::where('person_id', $duplicateId)->update(['person_id' => $primaryId]);
            if ($surnameUpdated) $summary[] = "{$surnameUpdated} variante(s) de apellido transferidas";

            if (class_exists(Invitation::class)) {
                Invitation::where('person_id', $duplicateId)->update(['person_id' => $primaryId]);
            }
            if (class_exists(Message::class)) {
                Message::where('related_person_id', $duplicateId)->update(['related_person_id' => $primaryId]);
            }
            if (class_exists(PersonEditPermission::class)) {
                PersonEditPermission::where('person_id', $duplicateId)->update(['person_id' => $primaryId]);
            }

            // 6. Re-vincular cuenta de usuario si aplica
            $userRelinked = User::where('person_id', $duplicateId)->update(['person_id' => $primaryId]);
            if ($userRelinked) $summary[] = "Cuenta de usuario re-vinculada";

            // 7. Completar campos vacíos del principal con datos del duplicado
            $fillable = [
                'matronymic', 'nickname', 'birth_date', 'birth_place', 'birth_country',
                'death_date', 'death_place', 'death_country', 'occupation', 'email', 'phone',
                'residence_place', 'residence_country', 'biography', 'photo_path',
                'heritage_region', 'origin_town',
            ];
            $filled = [];
            foreach ($fillable as $field) {
                if (empty($primary->$field) && !empty($duplicate->$field)) {
                    $filled[$field] = $duplicate->$field;
                }
            }
            if (!empty($filled)) {
                $primary->update($filled);
                $summary[] = count($filled) . " campo(s) completados desde el duplicado";
            }

            // 8. Eliminar duplicado
            $duplicate->delete();
        });

        $name = "{$primary->first_name} {$primary->patronymic}";

        return redirect()->route('admin.tools.duplicates')
            ->with('success', "Persona #{$duplicateId} fusionada en #{$primaryId} ({$name}).")
            ->with('details', $summary);
    }

    public function deleteDuplicate(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
        ]);

        $person = Person::findOrFail($request->input('person_id'));
        $name = "#{$person->id} {$person->first_name} {$person->patronymic}";

        // Verificar que no tenga cuenta de usuario vinculada
        if (User::where('person_id', $person->id)->exists()) {
            return redirect()->route('admin.tools.duplicates')
                ->with('error', "No se puede eliminar {$name}: tiene una cuenta de usuario vinculada. Usa fusionar en su lugar.");
        }

        $person->delete();

        return redirect()->route('admin.tools.duplicates')
            ->with('success', "Persona {$name} eliminada.");
    }

    // ========================================================================
    // Logica de deteccion de duplicados
    // ========================================================================

    private function findDuplicateGroups(): array
    {
        // Encontrar nombres+apellidos que aparecen más de una vez
        $duplicateKeys = DB::table('persons')
            ->select(DB::raw('LOWER(TRIM(first_name)) as fn'), DB::raw('LOWER(TRIM(patronymic)) as pat'))
            ->whereNotNull('first_name')
            ->where('first_name', '!=', '')
            ->whereNotNull('patronymic')
            ->where('patronymic', '!=', '')
            ->groupBy('fn', 'pat')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $groups = [];

        foreach ($duplicateKeys as $key) {
            $persons = Person::whereRaw('LOWER(TRIM(first_name)) = ?', [$key->fn])
                ->whereRaw('LOWER(TRIM(patronymic)) = ?', [$key->pat])
                ->orderBy('id')
                ->get();

            if ($persons->count() < 2) continue;

            // Determinar confianza del grupo
            $confidence = $this->classifyGroupConfidence($persons);

            $groups[] = [
                'key' => ucfirst($key->fn) . ' ' . ucfirst($key->pat),
                'persons' => $persons,
                'confidence' => $confidence,
            ];
        }

        // Ordenar: alta primero, luego media, luego baja
        usort($groups, function ($a, $b) {
            $order = ['alta' => 0, 'media' => 1, 'baja' => 2];
            return ($order[$a['confidence']] ?? 3) <=> ($order[$b['confidence']] ?? 3);
        });

        return $groups;
    }

    private function classifyGroupConfidence($persons): string
    {
        $first = $persons->first();

        foreach ($persons->slice(1) as $other) {
            $sameMatronymic = !empty($first->matronymic) && !empty($other->matronymic)
                && mb_strtolower(trim($first->matronymic)) === mb_strtolower(trim($other->matronymic));
            $sameBirthDate = !empty($first->birth_date) && !empty($other->birth_date)
                && $first->birth_date === $other->birth_date;

            if ($sameMatronymic && $sameBirthDate) return 'alta';
            if ($sameBirthDate) return 'media';
        }

        return 'baja';
    }

    private function buildComparisonFields(Person $personA, Person $personB): array
    {
        $fields = [];
        $compare = [
            'first_name' => __('Nombre'),
            'patronymic' => __('Apellido paterno'),
            'matronymic' => __('Apellido materno'),
            'nickname' => __('Apodo'),
            'gender' => __('Genero'),
            'birth_date' => __('Fecha de nacimiento'),
            'birth_place' => __('Lugar de nacimiento'),
            'death_date' => __('Fecha de defuncion'),
            'death_place' => __('Lugar de defuncion'),
            'is_living' => __('Vivo/a'),
            'occupation' => __('Ocupacion'),
            'email' => __('Email'),
            'phone' => __('Telefono'),
            'residence_place' => __('Residencia'),
            'biography' => __('Biografia'),
        ];

        foreach ($compare as $field => $label) {
            $valA = $personA->$field;
            $valB = $personB->$field;

            if ($field === 'is_living') {
                $valA = $valA ? __('Si') : __('No');
                $valB = $valB ? __('Si') : __('No');
            }

            $fields[] = [
                'label' => $label,
                'value_a' => $valA ?? '',
                'value_b' => $valB ?? '',
                'different' => mb_strtolower(trim((string)($personA->$field ?? ''))) !== mb_strtolower(trim((string)($personB->$field ?? ''))),
            ];
        }

        return $fields;
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
