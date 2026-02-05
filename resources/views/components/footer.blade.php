@php
    $col1 = isset($siteSettings) ? $siteSettings->content('footer', 'footer_col_1', '') : '';
    $col2 = isset($siteSettings) ? $siteSettings->content('footer', 'footer_col_2', '') : '';
    $col3 = isset($siteSettings) ? $siteSettings->content('footer', 'footer_col_3', '') : '';
@endphp

<!-- Footer -->
<footer class="py-8" style="background-color: #e5e5e5;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 items-start">
            <!-- Columna 1 -->
            <div>
                @if($col1)
                    {!! $col1 !!}
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-20 object-contain"
                         onerror="this.outerHTML='<span class=&quot;text-2xl font-bold&quot; style=&quot;color: var(--mf-primary, #3b82f6);&quot;>{{ config('app.name') }}</span>'">
                @endif
            </div>

            <!-- Columna 2 -->
            <div class="text-sm space-y-0">
                @if($col2)
                    {!! $col2 !!}
                @else
                    @auth
                        <a href="{{ route('help') }}" class="block text-gray-600 hover:text-[#3b82f6]">{{ __('¿Cómo funciona Mi Familia?') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="block text-gray-600 hover:text-[#3b82f6]">{{ __('¿Cómo funciona Mi Familia?') }}</a>
                    @endauth
                    <a href="{{ route('ancestors-info') }}" class="block text-gray-600 hover:text-[#3b82f6]">{{ __('Donde encontrar más información de mis antepasados') }}</a>
                    <a href="{{ route('privacy') }}" class="block text-gray-600 hover:text-[#3b82f6]">{{ __('Privacidad') }}</a>
                    <a href="{{ route('terms') }}" class="block text-gray-600 hover:text-[#3b82f6]">{{ __('Términos y condiciones') }}</a>
                @endif
            </div>

            <!-- Columna 3 -->
            <div class="text-sm space-y-0">
                {!! $col3 !!}
            </div>
        </div>
    </div>
</footer>

<!-- Barra de copyright -->
<div class="py-3 px-4" style="background-color: #b6b8e2;">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
        <p class="text-white text-xs">
            v{{ config('mi-familia.version') }}
        </p>
        <p class="text-white text-xs">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('Todos los derechos reservados.') }}</p>
    </div>
</div>
