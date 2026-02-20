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

// Ruta al proyecto Laravel (relativa desde public_html)
$laravelPath = __DIR__ . '/../mi-familia';

require $laravelPath . '/vendor/autoload.php';

$app = require_once $laravelPath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "=== Ejecutando migraciones ===\n";
echo "Laravel path: $laravelPath\n\n";

try {
    // Ejecutar migraciones pendientes (directorio principal)
    echo "--- database/migrations ---\n";
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();

    // Ejecutar migraciones de plugins
    $pluginMigrationPaths = glob($laravelPath . '/plugins/*/database/migrations');
    foreach ($pluginMigrationPaths as $absPath) {
        $relativePath = str_replace($laravelPath . '/', '', $absPath);
        echo "\n--- $relativePath ---\n";
        $exitCode2 = Artisan::call('migrate', [
            '--path' => $relativePath,
            '--force' => true,
        ]);
        echo Artisan::output();
    }

    echo "\n=== Migraciones completadas ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}

echo "</pre>";
echo "\n<p style='color:red; font-weight:bold;'>IMPORTANTE: Elimina este archivo después de usar.</p>";
