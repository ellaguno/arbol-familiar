<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Person;
use App\Services\ThumbnailService;
use Illuminate\Console\Command;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:generate
                            {--force : Regenerar aunque ya exista thumbnail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera thumbnails para fotos de persona y media de imagen ya existentes';

    public function handle(ThumbnailService $thumbnails): int
    {
        $force = (bool) $this->option('force');

        // Fotos de persona
        $personQuery = Person::whereNotNull('photo_path');
        if (!$force) {
            $personQuery->whereNull('photo_thumbnail_path');
        }

        $persons = $personQuery->get();
        $this->info("Fotos de persona a procesar: {$persons->count()}");
        $personOk = 0;

        foreach ($persons as $person) {
            $thumb = $thumbnails->generate($person->photo_path, 'photos/persons/thumbnails');
            if ($thumb) {
                $person->updateQuietly(['photo_thumbnail_path' => $thumb]);
                $personOk++;
            }
        }

        // Media de imagen
        $mediaQuery = Media::where('type', 'image')->whereNotNull('file_path');
        if (!$force) {
            $mediaQuery->whereNull('thumbnail_path');
        }

        $media = $mediaQuery->get();
        $this->info("Media de imagen a procesar: {$media->count()}");
        $mediaOk = 0;

        foreach ($media as $item) {
            $thumb = $thumbnails->generate($item->file_path, 'media/thumbnails');
            if ($thumb) {
                $item->update(['thumbnail_path' => $thumb]);
                $mediaOk++;
            }
        }

        $this->info("Thumbnails generados: {$personOk} fotos de persona, {$mediaOk} media.");

        return self::SUCCESS;
    }
}
