<?php
/**
 * Ejecutar seeders via web - Mi Familia
 * ELIMINAR ESTE ARCHIVO DESPUÉS DE USAR
 */

// Seguridad básica - cambiar este token
$secret_token = 'mifamilia2026seed';

if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    die('Acceso no autorizado. Usa: seed.php?token=' . $secret_token);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutos

echo "<pre style='font-family: monospace; background: #1a1a2e; color: #0f0; padding: 20px;'>";
echo "=== EJECUTANDO SEEDERS ===\n\n";

try {
    require __DIR__ . '/../mi-familia/vendor/autoload.php';
    $app = require_once __DIR__ . '/../mi-familia/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "1. Laravel inicializado correctamente\n\n";

    // Ejecutar seeders
    echo "2. Ejecutando seeders...\n";
    Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo Illuminate\Support\Facades\Artisan::output();

    echo "\n=== SEEDERS EJECUTADOS CORRECTAMENTE ===\n";
    echo "\nAHORA ELIMINA ESTE ARCHIVO DEL SERVIDOR\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
