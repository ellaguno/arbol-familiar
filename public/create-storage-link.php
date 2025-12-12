<?php
/**
 * Crear symlink de storage - Mi Familia
 * ELIMINAR ESTE ARCHIVO DESPUÉS DE USAR
 */

$target = __DIR__ . '/../mi-familia/storage/app/public';
$link = __DIR__ . '/storage';

echo "<pre style='font-family: monospace; background: #1a1a2e; color: #0f0; padding: 20px;'>";
echo "=== CREAR SYMLINK DE STORAGE ===\n\n";

if (file_exists($link)) {
    echo "El symlink ya existe en: $link\n";
} else {
    if (symlink($target, $link)) {
        echo "✓ Symlink creado exitosamente!\n";
        echo "  Origen: $target\n";
        echo "  Destino: $link\n";
    } else {
        echo "✗ Error al crear symlink\n";
        echo "  Intenta crear el symlink manualmente.\n";
    }
}

echo "\n=== ELIMINA ESTE ARCHIVO DESPUÉS DE EJECUTAR ===\n";
echo "</pre>";
