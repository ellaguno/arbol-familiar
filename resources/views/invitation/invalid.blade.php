<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('Invitacion no valida') }} - {{ config('app.name') }}</title>

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
                @switch($reason)
                    @case('expired')
                        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Invitacion expirada') }}</h2>
                        <p class="text-gray-600 mb-6">{{ $message }}</p>
                        <p class="text-sm text-gray-500">
                            {{ __('Contacta a la persona que te invito para que te envie una nueva invitacion.') }}
                        </p>
                        @break

                    @case('already_accepted')
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Invitacion ya aceptada') }}</h2>
                        <p class="text-gray-600 mb-6">{{ $message }}</p>
                        <a href="{{ route('login') }}" class="btn-primary inline-block">
                            {{ __('Iniciar sesion') }}
                        </a>
                        @break

                    @case('declined')
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Invitacion rechazada') }}</h2>
                        <p class="text-gray-600 mb-6">{{ $message }}</p>
                        @break

                    @default
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Invitacion no encontrada') }}</h2>
                        <p class="text-gray-600 mb-6">{{ $message }}</p>
                @endswitch

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
