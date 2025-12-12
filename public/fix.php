<?php
/**
 * Script de reparacion - Mi Familia
 * Crea directorios faltantes y limpia cache
 * ELIMINAR DESPUES DE USAR
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='font-family: monospace; background: #1a1a2e; color: #0f0; padding: 20px;'>\n";
echo "=== REPARACION MI FAMILIA ===\n\n";

$base_path = __DIR__ . '/../mi-familia';

// 1. Crear directorios faltantes
echo "1. Creando directorios faltantes:\n";

$directories = [
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/app/public',
    'storage/logs',
];

foreach ($directories as $dir) {
    $full_path = $base_path . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "   + Creado: $dir\n";
        } else {
            echo "   x Error creando: $dir\n";
        }
    } else {
        echo "   - Ya existe: $dir\n";
    }
}

// 2. Crear archivos .gitignore
echo "\n2. Creando archivos .gitignore:\n";

$gitignore_dirs = ['storage/framework/sessions', 'storage/framework/views', 'storage/framework/cache'];
foreach ($gitignore_dirs as $dir) {
    $gitignore_path = $base_path . '/' . $dir . '/.gitignore';
    if (!file_exists($gitignore_path)) {
        file_put_contents($gitignore_path, "*\n!.gitignore\n");
        echo "   + Creado: $dir/.gitignore\n";
    }
}

// 3. Eliminar archivos de cache problematicos
echo "\n3. Limpiando cache:\n";

$cache_files = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
];

foreach ($cache_files as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        if (unlink($full_path)) {
            echo "   + Eliminado: $file\n";
        } else {
            echo "   x Error eliminando: $file\n";
        }
    } else {
        echo "   - No existe: $file\n";
    }
}

// 4. Limpiar archivos de vista compilados
echo "\n4. Limpiando vistas compiladas:\n";
$views_path = $base_path . '/storage/framework/views';
if (is_dir($views_path)) {
    $files = glob($views_path . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $count++;
        }
    }
    echo "   + Eliminados $count archivos de vista compilados\n";
}

// 5. Verificar permisos
echo "\n5. Verificando permisos:\n";
$check_dirs = ['storage', 'storage/framework', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'];
foreach ($check_dirs as $dir) {
    $full_path = $base_path . '/' . $dir;
    if (is_dir($full_path)) {
        $perms = substr(sprintf('%o', fileperms($full_path)), -4);
        $writable = is_writable($full_path) ? '+' : 'x';
        echo "   $writable $dir (permisos: $perms)\n";
    }
}

// 6. Limpiar el log anterior
echo "\n6. Limpiando log de errores anteriores:\n";
$log_path = $base_path . '/storage/logs/laravel.log';
if (file_exists($log_path)) {
    file_put_contents($log_path, '');
    echo "   + Log limpiado\n";
} else {
    echo "   - No habia log\n";
}

echo "\n=== REPARACION COMPLETADA ===\n";
echo "\nAhora intenta acceder a tu sitio.\n";
echo "\nSi funciona, ELIMINA estos archivos:\n";
echo "  - fix.php\n";
echo "  - seed.php\n";
echo "  - create-storage-link.php\n";
echo "</pre>";
