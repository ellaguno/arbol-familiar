@php
    use App\Models\Media;
    use App\Models\Person;
    use App\Plugins\Models\Plugin;
    use Illuminate\Support\Facades\Cache;

    $plugin = Plugin::where('slug', 'photo-banner')->where('status', 'enabled')->first();
    $settings = $plugin ? ($plugin->settings ?? []) : [];

    $height = (int) ($settings['banner_height'] ?? 120);
    $speed = (int) ($settings['scroll_speed'] ?? 30);
    $maxImages = (int) ($settings['max_images'] ?? 50);
    $gap = (int) ($settings['image_gap'] ?? 4);

    $images = Cache::remember('photo_banner_images', 300, function () use ($maxImages) {
        return Media::images()
            ->where('mediable_type', 'App\\Models\\Person')
            ->whereNotNull('file_path')
            ->with('mediable')
            ->inRandomOrder()
            ->limit($maxImages)
            ->get()
            ->filter(fn($m) => $m->mediable !== null)
            ->values();
    });
@endphp

@if($images->count() > 0)
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
