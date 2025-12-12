@props([
    'title' => null,
    'subtitle' => null,
    'footer' => false,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($header))
        <div class="card-header">
            @if(isset($header))
                {{ $header }}
            @else
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if($footer || isset($footerSlot))
        <div class="card-footer">
            {{ $footerSlot ?? '' }}
        </div>
    @endif
</div>
