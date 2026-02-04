<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Mi Familia') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic colors -->
    @if(isset($siteColors))
    <style>
        :root {
            --mf-primary: {{ $siteColors['primary'] ?? '#3b82f6' }};
            --mf-secondary: {{ $siteColors['secondary'] ?? '#2563eb' }};
            --mf-accent: {{ $siteColors['accent'] ?? '#f59e0b' }};
            --mf-light: {{ $siteColors['light'] ?? '#dbeafe' }};
            --mf-dark: {{ $siteColors['dark'] ?? '#1d4ed8' }};
        }
    </style>
    @endif

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 min-h-screen">
    {{-- Toast Notifications --}}
    <x-toast-notifications />

    <div class="min-h-screen flex flex-col">
        <x-header />

        <!-- Contenido Principal -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
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
