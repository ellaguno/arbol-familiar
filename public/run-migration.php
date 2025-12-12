<?php
/**
 * Script temporal para ejecutar migraciones
 * ELIMINAR DESPUÉS DE USAR
 */

// Verificar clave de seguridad
$secretKey = 'mifamilia2026migrate';
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die('Acceso denegado');
}

// Ruta al proyecto Laravel (hosting compartido cPanel)
$laravelPath = '/home1/concurre/mi-familia';

require $laravelPath . '/vendor/autoload.php';

$app = require_once $laravelPath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "=== Ejecutando migraciones ===\n";
echo "Laravel path: $laravelPath\n\n";

try {
    // Ejecutar migraciones pendientes
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();

    if ($exitCode === 0) {
        echo "\n=== Migraciones completadas exitosamente ===\n";
    } else {
        echo "\n=== Error en migraciones (código: $exitCode) ===\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}

echo "</pre>";
echo "\n<p style='color:red; font-weight:bold;'>IMPORTANTE: Elimina este archivo después de usar.</p>";
