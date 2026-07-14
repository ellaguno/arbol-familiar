<?php

namespace Tests\Unit\Models;

use App\Models\Media;
use App\Models\Person;
use Tests\TestCase;

class ThumbnailAccessorTest extends TestCase
{
    /** Person: usa el thumbnail si existe, si no cae a la foto completa, o null. */
    public function test_person_photo_thumbnail_url_fallback(): void
    {
        $sinFoto = new Person();
        $this->assertNull($sinFoto->photo_thumbnail_url);

        $soloFoto = new Person(['photo_path' => 'photos/persons/a.jpg']);
        $this->assertStringContainsString('photos/persons/a.jpg', $soloFoto->photo_thumbnail_url);

        $conThumb = new Person([
            'photo_path' => 'photos/persons/a.jpg',
            'photo_thumbnail_path' => 'photos/persons/thumbnails/a_thumb.jpg',
        ]);
        $this->assertStringContainsString('a_thumb.jpg', $conThumb->photo_thumbnail_url);
    }

    /** Media: imagen con thumbnail devuelve el thumb; sin el, cae a la imagen. */
    public function test_media_thumbnail_url_fallback(): void
    {
        $sinThumb = new Media(['type' => 'image', 'file_path' => 'media/images/b.jpg']);
        $this->assertStringContainsString('media/images/b.jpg', $sinThumb->thumbnail_url);

        $conThumb = new Media([
            'type' => 'image',
            'file_path' => 'media/images/b.jpg',
            'thumbnail_path' => 'media/thumbnails/b_thumb.jpg',
        ]);
        $this->assertStringContainsString('b_thumb.jpg', $conThumb->thumbnail_url);
    }
}
