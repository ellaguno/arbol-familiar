<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $siteThemeClass ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Mi Familia') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $siteFontUrl ?? 'https://fonts.bunny.net/css?family=ubuntu:400,500,600,700&display=swap' }}" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic colors and font -->
    <style>
        :root {
            --mf-primary: {{ $siteColors['primary'] ?? '#3b82f6' }};
            --mf-secondary: {{ $siteColors['secondary'] ?? '#2563eb' }};
            --mf-accent: {{ $siteColors['accent'] ?? '#f59e0b' }};
            --mf-light: {{ $siteColors['light'] ?? '#dbeafe' }};
            --mf-dark: {{ $siteColors['dark'] ?? '#1d4ed8' }};
            --mf-font: '{{ $siteFont ?? 'Ubuntu' }}', ui-sans-serif, system-ui, sans-serif;
            @if($siteBgColor ?? '')--mf-bg: {{ $siteBgColor }};@endif
        }
        body { font-family: var(--mf-font) !important; }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased min-h-screen" style="background-color: var(--mf-bg);">
    {{-- Toast Notifications --}}
    <x-toast-notifications />

    <div class="min-h-screen flex flex-col">
        <x-header />

        <!-- Contenido Principal -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
              @if($siteBgImage ?? '')
              style="background-image: url('{{ asset($siteBgImage) }}'); background-size: cover; background-position: center; background-attachment: fixed;"
              @else
              style="background-color: var(--mf-bg);"
              @endif>
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <x-footer />
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>
