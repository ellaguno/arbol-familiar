<?php
/**
 * Script web para revisar y corregir apellidos
 * Equivale a: php artisan genealogy:fix-surnames
 * ELIMINAR DESPUÉS DE USAR
 */

$secretKey = 'mifamilia2026surnames';
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die('Acceso denegado');
}

$laravelPath = __DIR__ . '/../mi-familia';
require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Person;

// Modo: scan (default), apply (aplicar seleccionados)
$mode = $_GET['mode'] ?? 'scan';
$applyIds = $_POST['apply'] ?? [];

/**
 * Funciones de analisis (misma logica del comando FixSurnames)
 */
function findSplitByParents(array $parts, string $fatherSurname, string $motherSurname): ?array
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

function splitByKnownSurname(array $parts, string $knownSurname, string $which): ?array
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

function findSiblingWithSurnames(Person $person): ?Person
{
    $family = $person->familiesAsChild()->first();
    if (!$family) return null;
    return Person::whereHas('familiesAsChild', fn($q) => $q->where('families.id', $family->id))
        ->where('id', '!=', $person->id)
        ->whereNotNull('matronymic')
        ->where('matronymic', '!=', '')
        ->first();
}

function findChildMatch(Person $person, array $parts): ?Person
{
    foreach ($person->children as $child) {
        $childPat = trim($child->patronymic ?? '');
        if ($childPat !== '' && in_array(mb_strtolower($childPat), array_map('mb_strtolower', $parts))) {
            return $child;
        }
    }
    return null;
}

function analyzePerson(Person $person): ?array
{
    $patronymic = trim($person->patronymic ?? '');
    $matronymic = trim($person->matronymic ?? '');

    // Caso 1: patronymic compuesto con matronymic vacio
    if ($matronymic === '' && str_contains($patronymic, ' ')) {
        $parts = preg_split('/\s+/', $patronymic);
        if (count($parts) < 2) return null;

        $father = $person->father;
        $mother = $person->mother;

        // Estrategia 1: Ambos padres
        if ($father && $mother) {
            $fp = trim($father->patronymic ?? '');
            $mp = trim($mother->patronymic ?? '');
            if ($fp && $mp) {
                $r = findSplitByParents($parts, $fp, $mp);
                if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                    'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                    'reason' => "padre={$fp}, madre={$mp}", 'confidence' => 'alta'];
            }
        }

        // Estrategia 2: Solo padre
        if ($father && !$mother) {
            $fp = trim($father->patronymic ?? '');
            if ($fp) {
                $r = splitByKnownSurname($parts, $fp, 'father');
                if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                    'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                    'reason' => "padre={$fp}", 'confidence' => 'media'];
            }
        }

        // Estrategia 3: Solo madre
        if (!$father && $mother) {
            $mp = trim($mother->patronymic ?? '');
            if ($mp) {
                $r = splitByKnownSurname($parts, $mp, 'mother');
                if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                    'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                    'reason' => "madre={$mp}", 'confidence' => 'media'];
            }
        }

        // Estrategia 4: Hermano
        $sibling = findSiblingWithSurnames($person);
        if ($sibling) {
            $sp = trim($sibling->patronymic ?? '');
            $sm = trim($sibling->matronymic ?? '');
            if ($sp && $sm) {
                $r = findSplitByParents($parts, $sp, $sm);
                if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                    'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                    'reason' => "hermano/a {$sibling->first_name}: {$sp} {$sm}", 'confidence' => 'media'];
            }
        }

        // Estrategia 5: Hijos
        $child = findChildMatch($person, $parts);
        if ($child) {
            $cp = trim($child->patronymic ?? '');
            if ($cp && $person->gender === 'M') {
                $r = splitByKnownSurname($parts, $cp, 'father');
                if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                    'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                    'reason' => "hijo/a {$child->first_name} pat={$cp}", 'confidence' => 'media'];
            }
            if ($person->gender === 'F') {
                $cm = trim($child->matronymic ?? '');
                if ($cm) {
                    $r = splitByKnownSurname($parts, $cm, 'father');
                    if ($r) return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                        'new_pat' => $r['patronymic'], 'new_mat' => $r['matronymic'],
                        'reason' => "hijo/a {$child->first_name} mat={$cm}", 'confidence' => 'media'];
                }
            }
        }

        // Estrategia 6: Dos palabras simples
        if (count($parts) === 2) {
            return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
                'new_pat' => $parts[0], 'new_mat' => $parts[1],
                'reason' => 'sin padres, asumiendo 2 apellidos', 'confidence' => 'baja'];
        }

        return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => $matronymic,
            'new_pat' => null, 'new_mat' => null,
            'reason' => 'no se pudo separar automaticamente', 'confidence' => 'ninguna'];
    }

    // Caso 2: matronymic vacio, madre conocida
    if ($patronymic !== '' && $matronymic === '' && !str_contains($patronymic, ' ')) {
        $mother = $person->mother;
        if ($mother) {
            $mp = trim($mother->patronymic ?? '');
            if ($mp) {
                return ['person' => $person, 'old_pat' => $patronymic, 'old_mat' => '',
                    'new_pat' => $patronymic, 'new_mat' => $mp,
                    'reason' => "completar desde madre ({$mother->first_name} {$mp})", 'confidence' => 'alta'];
            }
        }
    }

    return null;
}

// ============================================================
// MODO APPLY: aplicar cambios seleccionados
// ============================================================
if ($mode === 'apply' && !empty($applyIds)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Aplicando correcciones</title>';
    echo '<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;max-width:900px;margin:0 auto}';
    echo '.ok{color:#4ade80}.err{color:#f87171}h1{color:#60a5fa}</style></head><body>';
    echo '<h1>Aplicando correcciones de apellidos</h1><pre>';

    $applied = 0;
    $errors = 0;

    foreach ($applyIds as $id => $data) {
        $person = Person::find($id);
        if (!$person) {
            echo "<span class='err'>Error: persona #{$id} no encontrada</span>\n";
            $errors++;
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

        echo "<span class='ok'>#{$id} {$person->first_name}: \"{$oldPat}\" \"{$oldMat}\" → \"{$newPat}\" \"{$newMat}\"</span>\n";
        $applied++;
    }

    echo "\n<span class='ok'>Aplicados: {$applied}</span>";
    if ($errors) echo "\n<span class='err'>Errores: {$errors}</span>";
    echo '</pre>';
    echo '<p><a href="?key=' . $secretKey . '" style="color:#60a5fa">Volver a escanear</a></p>';
    echo '<p style="color:#f87171;font-weight:bold">ELIMINA este archivo cuando termines.</p>';
    echo '</body></html>';
    exit;
}

// ============================================================
// MODO SCAN: escanear y mostrar propuestas
// ============================================================
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Revision de apellidos</title>
<style>
    body { font-family: -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; max-width: 1200px; margin: 0 auto; }
    h1 { color: #60a5fa; }
    h2 { color: #f59e0b; margin-top: 2em; }
    table { width: 100%; border-collapse: collapse; margin: 1em 0; }
    th { background: #1e293b; color: #94a3b8; text-align: left; padding: 8px 12px; font-size: 13px; }
    td { padding: 8px 12px; border-bottom: 1px solid #1e293b; font-size: 14px; }
    tr:hover { background: #1e293b; }
    .old { color: #f87171; text-decoration: line-through; }
    .new { color: #4ade80; font-weight: bold; }
    .reason { color: #94a3b8; font-size: 12px; }
    .conf-alta { color: #4ade80; }
    .conf-media { color: #f59e0b; }
    .conf-baja { color: #f87171; }
    .conf-ninguna { color: #64748b; }
    .btn { background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; }
    .btn:hover { background: #2563eb; }
    .btn-sm { padding: 4px 10px; font-size: 12px; border-radius: 4px; }
    .summary { background: #1e293b; padding: 16px; border-radius: 8px; margin: 1em 0; }
    .warn { color: #f87171; font-weight: bold; margin-top: 2em; }
    input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .selectbar { position: sticky; top: 0; background: #0f172a; padding: 12px 0; border-bottom: 2px solid #3b82f6; z-index: 10; display: flex; gap: 12px; align-items: center; }
    .selectbar button { background: #334155; color: #e2e8f0; border: 1px solid #475569; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    .selectbar button:hover { background: #475569; }
</style>
</head>
<body>
<h1>Revision de apellidos</h1>
<?php

$persons = Person::orderBy('id')->get();
$proposals = [];
$warnings = [];
$skipped = 0;

foreach ($persons as $person) {
    $result = analyzePerson($person);
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

$total = count($persons);
$fixable = count($proposals);
$uncertain = count($warnings);

echo '<div class="summary">';
echo "<strong>Total personas:</strong> {$total} | ";
echo "<strong>Con correcciones propuestas:</strong> {$fixable} | ";
echo "<strong>No se pudo separar:</strong> {$uncertain} | ";
echo "<strong>Sin cambios necesarios:</strong> {$skipped}";
echo '</div>';

if ($fixable > 0) {
    echo '<form method="POST" action="?key=' . $secretKey . '&mode=apply">';
    echo '<div class="selectbar">';
    echo '<button type="button" onclick="toggleAll(true)">Seleccionar todos</button>';
    echo '<button type="button" onclick="toggleAll(false)">Deseleccionar todos</button>';
    echo '<button type="button" onclick="toggleConf(\'alta\')">Solo confianza alta</button>';
    echo '<button type="submit" class="btn btn-sm" style="margin-left:auto">Aplicar seleccionados</button>';
    echo '</div>';

    // Agrupar por confianza
    $byConf = ['alta' => [], 'media' => [], 'baja' => []];
    foreach ($proposals as $p) {
        $byConf[$p['confidence']][] = $p;
    }

    foreach (['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'] as $conf => $label) {
        if (empty($byConf[$conf])) continue;

        echo "<h2>Confianza {$label} (" . count($byConf[$conf]) . ")</h2>";
        echo '<table><tr><th></th><th>#</th><th>Nombre</th><th>Actual</th><th>Propuesto</th><th>Razon</th></tr>';

        foreach ($byConf[$conf] as $p) {
            $pid = $p['person']->id;
            $name = htmlspecialchars($p['person']->first_name);
            $oldPat = htmlspecialchars($p['old_pat']);
            $oldMat = htmlspecialchars($p['old_mat']);
            $newPat = htmlspecialchars($p['new_pat']);
            $newMat = htmlspecialchars($p['new_mat']);
            $reason = htmlspecialchars($p['reason']);
            $checked = $conf === 'alta' ? 'checked' : '';

            echo "<tr data-conf='{$conf}'>";
            echo "<td><input type='checkbox' class='row-check' name='apply[{$pid}][pat]' value='{$newPat}' {$checked}>";
            echo "<input type='hidden' name='apply[{$pid}][mat]' value='{$newMat}' disabled></td>";
            echo "<td>{$pid}</td>";
            echo "<td>{$name}</td>";
            echo "<td><span class='old'>pat=\"{$oldPat}\"</span> <span class='old'>mat=\"{$oldMat}\"</span></td>";
            echo "<td><span class='new'>pat=\"{$newPat}\"</span> <span class='new'>mat=\"{$newMat}\"</span></td>";
            echo "<td><span class='reason'>{$reason}</span> <span class='conf-{$conf}'>[{$conf}]</span></td>";
            echo '</tr>';
        }
        echo '</table>';
    }

    echo '</form>';
}

if (!empty($warnings)) {
    echo "<h2>No se pudieron separar (" . count($warnings) . ")</h2>";
    echo '<table><tr><th>#</th><th>Nombre</th><th>Patronymic</th><th>Nota</th></tr>';
    foreach ($warnings as $w) {
        $pid = $w['person']->id;
        $name = htmlspecialchars($w['person']->first_name);
        $pat = htmlspecialchars($w['old_pat']);
        $reason = htmlspecialchars($w['reason']);
        echo "<tr><td>{$pid}</td><td>{$name}</td><td>\"{$pat}\"</td><td class='reason'>{$reason}</td></tr>";
    }
    echo '</table>';
}

if ($fixable === 0 && $uncertain === 0) {
    echo '<div class="summary" style="color:#4ade80">Todos los apellidos estan correctos. No hay cambios necesarios.</div>';
}
?>

<p class="warn">ELIMINA este archivo (fix-surnames.php) cuando termines.</p>

<script>
function toggleAll(state) {
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = state;
        cb.closest('tr').querySelector('input[type=hidden]').disabled = !state;
    });
}
function toggleConf(conf) {
    document.querySelectorAll('.row-check').forEach(cb => {
        const row = cb.closest('tr');
        const isConf = row.dataset.conf === conf;
        cb.checked = isConf;
        row.querySelector('input[type=hidden]').disabled = !isConf;
    });
}
// Sincronizar checkbox con hidden
document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', function() {
        this.closest('tr').querySelector('input[type=hidden]').disabled = !this.checked;
    });
});
</script>
</body>
</html>
