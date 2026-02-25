@php
    use App\Models\Media;
    use App\Models\Person;
    use App\Plugins\Models\Plugin;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;

    // Guard: si no hay usuario autenticado, no mostrar el banner
    if (!$user) {
        return;
    }

    $plugin = Plugin::where('slug', 'photo-banner')->where('status', 'enabled')->first();
    $settings = $plugin ? ($plugin->settings ?? []) : [];

    $height = (int) ($settings['banner_height'] ?? 120);
    $speed = (int) ($settings['scroll_speed'] ?? 30);
    $maxImages = (int) ($settings['max_images'] ?? 50);
    $gap = (int) ($settings['image_gap'] ?? 4);
    $minRealPhotos = (int) ($settings['min_real_photos'] ?? 10);
    $cacheVersion = (int) ($settings['cache_version'] ?? 0);

    $cacheKey = "photo_banner_v{$cacheVersion}_user_{$user->id}";

    $images = Cache::remember($cacheKey, 120, function () use ($maxImages, $user) {
        // Construir lista de person IDs accesibles para este usuario
        $accessibleIds = [];

        // 1. Personas creadas por este usuario (siempre accesibles)
        $createdIds = DB::table('persons')
            ->where('created_by', $user->id)
            ->pluck('id')
            ->toArray();
        $accessibleIds = array_merge($accessibleIds, $createdIds);

        // 2. La persona vinculada al usuario + toda su familia conectada via BFS
        if ($user->person_id) {
            $accessibleIds[] = $user->person_id;

            $userPerson = Person::find($user->person_id);
            if ($userPerson) {
                // BFS ya cubre familia directa, extendida, ancestros y descendientes
                $connectedIds = $userPerson->getAllConnectedPersonIds();
                $accessibleIds = array_merge($accessibleIds, $connectedIds);
            }
        }

        // 3. Personas con privacy_level 'community' o 'public' (visibles para todos)
        $communityIds = DB::table('persons')
            ->whereIn('privacy_level', ['community', 'public'])
            ->pluck('id')
            ->toArray();
        $accessibleIds = array_merge($accessibleIds, $communityIds);

        // Deduplicar eficientemente
        $accessibleIds = array_keys(array_flip($accessibleIds));

        if (empty($accessibleIds)) {
            return collect();
        }

        return Media::images()
            ->where('mediable_type', 'App\\Models\\Person')
            ->whereNotNull('file_path')
            ->whereIn('mediable_id', $accessibleIds)
            ->with('mediable')
            ->inRandomOrder()
            ->limit($maxImages)
            ->get()
            ->filter(fn($m) => $m->mediable !== null)
            ->values();
    });

    // Cargar imagenes genericas si las fotos reales son insuficientes
    $genericImages = collect();
    if ($images->count() < $minRealPhotos) {
        $genericCacheKey = "photo_banner_generics_v{$cacheVersion}";
        $genericImages = Cache::remember($genericCacheKey, 3600, function () {
            // Usar base_path del document root real (public_html en producción)
            // en vez de public_path() que puede apuntar a otro directorio
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? public_path();
            $bannerPath = rtrim($docRoot, '/') . '/images/banner';
            if (!is_dir($bannerPath)) {
                // Fallback a public_path() para desarrollo local
                $bannerPath = public_path('images/banner');
                if (!is_dir($bannerPath)) {
                    return collect();
                }
            }
            $files = [];
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                $files = array_merge($files, glob($bannerPath . '/*.' . $ext) ?: []);
            }
            return collect($files)->map(fn($f) => asset('images/banner/' . basename($f)))->values();
        });

        // Mezclar genericas para variedad
        $genericImages = $genericImages->shuffle();
    }

    // Combinar: primero reales, luego genericas hasta completar el minimo
    $needGeneric = max(0, $minRealPhotos - $images->count());
    $genericsToUse = $genericImages->take($needGeneric);
@endphp

@if($images->count() > 0 || $genericsToUse->count() > 0)
<div class="photo-banner-wrapper" style="width: 100%; overflow: hidden; height: {{ $height }}px; background: rgba(0,0,0,0.03);">
    <div class="photo-banner-track"
         style="display: flex; gap: {{ $gap }}px; height: 100%; width: max-content;
                animation: photoBannerScroll {{ $speed }}s linear infinite;
                will-change: transform;">
        {{-- Primera copia --}}
        @foreach($images as $media)
            <a href="{{ route('persons.show', $media->mediable) }}"
               title="{{ $media->mediable->full_name }}"
               class="photo-banner-item"
               style="flex-shrink: 0; height: 100%; aspect-ratio: 1; display: block;">
                <img src="{{ $media->url }}"
                     alt="{{ $media->mediable->full_name }}"
                     loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; display: block;"
                     onerror="this.parentElement.style.display=&quot;none&quot;">
            </a>
        @endforeach
        @foreach($genericsToUse as $genericUrl)
            <span class="photo-banner-item photo-banner-generic"
                  style="flex-shrink: 0; height: 100%; aspect-ratio: 1; display: block;">
                <img src="{{ $genericUrl }}"
                     alt=""
                     loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; display: block; opacity: 0.7;"
                     onerror="this.parentElement.style.display=&quot;none&quot;">
            </span>
        @endforeach
        {{-- Segunda copia para loop infinito --}}
        @foreach($images as $media)
            <a href="{{ route('persons.show', $media->mediable) }}"
               title="{{ $media->mediable->full_name }}"
               class="photo-banner-item"
               aria-hidden="true"
               style="flex-shrink: 0; height: 100%; aspect-ratio: 1; display: block;">
                <img src="{{ $media->url }}"
                     alt=""
                     loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; display: block;"
                     onerror="this.parentElement.style.display=&quot;none&quot;">
            </a>
        @endforeach
        @foreach($genericsToUse as $genericUrl)
            <span class="photo-banner-item photo-banner-generic"
                  aria-hidden="true"
                  style="flex-shrink: 0; height: 100%; aspect-ratio: 1; display: block;">
                <img src="{{ $genericUrl }}"
                     alt=""
                     loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; display: block; opacity: 0.7;"
                     onerror="this.parentElement.style.display=&quot;none&quot;">
            </span>
        @endforeach
    </div>
</div>

<style>
    @keyframes photoBannerScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .photo-banner-wrapper:hover .photo-banner-track {
        animation-play-state: paused;
    }

    .photo-banner-item {
        transition: opacity 0.2s ease;
    }

    .photo-banner-item:hover {
        opacity: 0.8;
    }
</style>
@endif
