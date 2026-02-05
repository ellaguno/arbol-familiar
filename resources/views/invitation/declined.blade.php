<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('Invitacion rechazada') }} - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $siteFontUrl ?? 'https://fonts.bunny.net/css?family=ubuntu:400,500,600,700&display=swap' }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic font -->
    <style>
        :root {
            --mf-font: '{{ $siteFont ?? 'Ubuntu' }}', ui-sans-serif, system-ui, sans-serif;
        }
        body { font-family: var(--mf-font) !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo -->
            <a href="{{ route('home') }}">
                <img class="mx-auto h-16 w-auto" src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 text-center">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Has rechazado la invitacion') }}</h2>

                <p class="text-gray-600 mb-4">
                    {{ __('Hemos notificado a :name sobre tu decision.', ['name' => $invitation->inviter->person?->full_name ?? $invitation->inviter->email]) }}
                </p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left text-sm">
                    <h3 class="font-semibold text-blue-900 mb-2">{{ __('Tus derechos:') }}</h3>
                    <ul class="text-blue-800 space-y-1 list-disc list-inside">
                        <li>{{ __('Puedes solicitar que eliminen tu informacion') }}</li>
                        <li>{{ __('Puedes contactar al administrador si tienes dudas') }}</li>
                        <li>{{ __('Esta decision se puede cambiar contactando a quien te invito') }}</li>
                    </ul>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('home') }}" class="text-blue-600 hover:underline text-sm">
                        {{ __('Ir a la pagina principal') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
