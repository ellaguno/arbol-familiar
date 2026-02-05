@props(['title' => null, 'hideLogo' => false])

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

    <!-- Dynamic colors and font -->
    <style>
        :root {
            --mf-primary: {{ $siteColors['primary'] ?? '#3b82f6' }};
            --mf-secondary: {{ $siteColors['secondary'] ?? '#2563eb' }};
            --mf-accent: {{ $siteColors['accent'] ?? '#f59e0b' }};
            --mf-light: {{ $siteColors['light'] ?? '#dbeafe' }};
            --mf-dark: {{ $siteColors['dark'] ?? '#1d4ed8' }};
            --mf-font: '{{ $siteFont ?? 'Ubuntu' }}', ui-sans-serif, system-ui, sans-serif;
        }
        body { font-family: var(--mf-font) !important; }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased min-h-screen" style="background-color: var(--mf-bg);">
    <div class="min-h-screen flex flex-col">
        <x-header :hideLogo="$hideLogo" />

        <!-- Contenido Principal -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
              @if($siteBgImage ?? '')
              style="background-image: url('{{ asset($siteBgImage) }}'); background-size: cover; background-position: center; background-attachment: fixed;"
              @elseif($siteBgColor ?? '')
              style="background-color: {{ $siteBgColor }};"
              @endif>
            <div class="w-full max-w-md">
                <!-- Alertas Flash -->
                @if (session('success'))
                    <div class="alert alert-success fade-in mb-6">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error fade-in mb-6">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <x-footer />
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- reCAPTCHA v3 (invisible) -->
    @if(config('mi-familia.recaptcha.enabled'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('mi-familia.recaptcha.site_key') }}"></script>
        <style>
            /* Ocultar badge de reCAPTCHA v3 (permitido por Google si muestras atribucion) */
            .grecaptcha-badge { visibility: hidden !important; }
        </style>
    @endif

    @stack('scripts')
</body>
</html>
