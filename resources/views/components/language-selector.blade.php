@props(['class' => ''])

@php
    $currentLang = app()->getLocale();
    $languages = [
        'es' => 'Español',
        'en' => 'English',
    ];
    $flags = [
        'es' => '🇲🇽',
        'en' => '🇺🇸',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative ' . $class]) }} x-data="{ open: false }">
    <button @click="open = !open" type="button"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
        <span class="text-lg">{{ $flags[$currentLang] ?? '🇲🇽' }}</span>
        <span class="hidden sm:inline">{{ $languages[$currentLang] ?? 'Español' }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-transition
         class="absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
        <div class="py-1">
            @foreach($languages as $code => $name)
                <a href="?lang={{ $code }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currentLang === $code ? 'bg-mf-light text-mf-primary' : '' }}">
                    <span class="text-lg">{{ $flags[$code] }}</span>
                    <span>{{ $name }}</span>
                    @if($currentLang === $code)
                        <svg class="w-4 h-4 ml-auto text-mf-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
