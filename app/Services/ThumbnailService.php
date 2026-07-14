<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ThumbnailService
{
    /** Lado del thumbnail cuadrado, en px. */
    public const SIZE = 300;

    /**
     * Genera un thumbnail cuadrado (JPG) a partir de una imagen del disco
     * 'public'. Devuelve la ruta relativa del thumbnail generado, o null si
     * la generacion falla; en ese caso el llamador puede continuar sin
     * thumbnail y usar la imagen original como fallback.
     *
     * @param  string  $sourceRelativePath  Ruta relativa en el disco 'public' (ej. media/images/xxx.jpg)
     * @param  string  $destDir             Directorio destino en el disco 'public' (ej. media/thumbnails)
     */
    public function generate(string $sourceRelativePath, string $destDir, int $size = self::SIZE): ?string
    {
        try {
            $disk = Storage::disk('public');

            if (!$disk->exists($sourceRelativePath)) {
                return null;
            }

            $destDir = trim($destDir, '/');
            $filename = pathinfo($sourceRelativePath, PATHINFO_FILENAME) . '_thumb.jpg';
            $destRelative = $destDir . '/' . $filename;

            $image = Image::make($disk->path($sourceRelativePath))
                ->fit($size, $size);

            $disk->put($destRelative, (string) $image->encode('jpg', 80));

            return $destRelative;
        } catch (\Throwable $e) {
            Log::warning("No se pudo generar thumbnail para {$sourceRelativePath}: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Elimina un thumbnail del disco 'public' si existe.
     */
    public function delete(?string $thumbnailRelativePath): void
    {
        if ($thumbnailRelativePath) {
            Storage::disk('public')->delete($thumbnailRelativePath);
        }
    }
}
