@if(isset($person))
<a href="{{ route('reports.fanchart', $person) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-theme-secondary hover:bg-theme-hover transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
    </svg>
    {{ __('Abanico') }}
</a>
@endif
