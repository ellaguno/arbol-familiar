@props([
    'title' => '',
    'open' => false,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'border border-theme rounded-lg overflow-hidden']) }} x-data="{ open: {{ $open ? 'true' : 'false' }} }">
    <button
        @click="open = !open"
        type="button"
        class="collapsible-header"
    >
        <div class="flex items-center gap-3">
            @if($icon)
                <span class="text-mf-primary">
                    {!! $icon !!}
                </span>
            @endif
            <span class="font-medium text-theme">{{ $title }}</span>
        </div>
        <svg
            class="w-5 h-5 text-theme-muted transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="collapsible-content"
    >
        <div class="p-4 bg-theme-card">
            {{ $slot }}
        </div>
    </div>
</div>
