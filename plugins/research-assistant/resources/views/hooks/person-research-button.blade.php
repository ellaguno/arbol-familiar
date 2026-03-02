@if($person ?? null)
<a href="{{ route('research.person', $person) }}"
   class="btn-outline flex items-center gap-2"
   title="{{ __('Investigar esta persona') }}"
   x-data="{ clicked: false }"
   @click="if (clicked) { $event.preventDefault(); return; } clicked = true;"
   :class="{ 'opacity-50 pointer-events-none': clicked }">
    <svg x-show="!clicked" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <svg x-show="clicked" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span class="hidden sm:inline" x-text="clicked ? '{{ __("Cargando...") }}' : '{{ __("Investigar") }}'"></span>
</a>
@endif
