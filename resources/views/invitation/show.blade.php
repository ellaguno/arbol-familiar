<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Invitacion') }} - {{ config('app.name') }}</title>

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
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('Invitacion de consentimiento') }}
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Información de la invitación -->
                <div class="mb-6">
                    <div class="flex items-center justify-center mb-4">
                        @if($invitation->person->photo_path)
                            <img src="{{ asset('storage/' . $invitation->person->photo_path) }}"
                                 alt="{{ $invitation->person->first_name }}"
                                 class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <span class="text-white text-2xl font-bold">{{ substr($invitation->person->first_name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-center text-gray-600 mb-4">
                        <strong>{{ $invitation->inviter->person?->full_name ?? $invitation->inviter->email }}</strong>
                        {{ __('te ha registrado en el arbol genealogico') }}
                        <strong>{{ config('app.name') }}</strong>.
                    </p>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold text-blue-900 mb-2">{{ __('Tu informacion registrada:') }}</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li><strong>{{ __('Nombre') }}:</strong> {{ $invitation->person->full_name }}</li>
                            @if($invitation->person->birth_year)
                                <li><strong>{{ __('Año de nacimiento') }}:</strong> {{ $invitation->person->birth_year }}</li>
                            @endif
                            @if($invitation->person->birth_place)
                                <li><strong>{{ __('Lugar de nacimiento') }}:</strong> {{ $invitation->person->birth_place }}</li>
                            @endif
                        </ul>
                    </div>

                    <p class="text-sm text-gray-500 text-center">
                        {{ __('Al aceptar, podras ver y editar tu informacion en el arbol genealogico.') }}
                    </p>
                </div>

                @if($existingUser)
                    <!-- Usuario ya existe - Solo necesita iniciar sesión -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <p class="text-amber-800 text-sm">
                            {{ __('Ya tienes una cuenta con este correo. Inicia sesion para aceptar la invitacion.') }}
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('invitation.show', $invitation->token)) }}"
                           class="flex-1 btn-primary text-center">
                            {{ __('Iniciar sesion') }}
                        </a>
                        <form action="{{ route('invitation.decline', $invitation->token) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full btn-outline text-red-600 border-red-300 hover:bg-red-50">
                                {{ __('Rechazar') }}
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Formulario de registro -->
                    <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                        @csrf

                        <div class="space-y-4">
                            <!-- Email (readonly) -->
                            <div>
                                <label for="email" class="form-label">{{ __('Correo electronico') }}</label>
                                <input type="email" id="email" value="{{ $invitation->email }}"
                                       class="form-input bg-gray-50" readonly>
                            </div>

                            <!-- Nombre -->
                            <div>
                                <label for="name" class="form-label">{{ __('Tu nombre') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name"
                                       value="{{ old('name', $invitation->person->full_name) }}"
                                       class="form-input @error('name') border-red-500 @enderror" required>
                                @error('name')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contraseña -->
                            <div>
                                <label for="password" class="form-label">{{ __('Contrasena') }} <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password"
                                       class="form-input @error('password') border-red-500 @enderror"
                                       required minlength="8">
                                @error('password')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-help">{{ __('Minimo 8 caracteres') }}</p>
                            </div>

                            <!-- Confirmar contraseña -->
                            <div>
                                <label for="password_confirmation" class="form-label">{{ __('Confirmar contrasena') }} <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-input" required>
                            </div>

                            <!-- Términos y condiciones -->
                            <div class="flex items-start">
                                <input type="checkbox" name="terms" id="terms"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-1 @error('terms') border-red-500 @enderror" required>
                                <label for="terms" class="ml-2 text-sm text-gray-600">
                                    {{ __('Acepto los') }}
                                    <a href="{{ route('terms') }}" target="_blank" class="text-blue-600 hover:underline">{{ __('terminos y condiciones') }}</a>
                                    {{ __('y la') }}
                                    <a href="{{ route('privacy') }}" target="_blank" class="text-blue-600 hover:underline">{{ __('politica de privacidad') }}</a>
                                </label>
                            </div>
                            @error('terms')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full btn-primary">
                                {{ __('Aceptar y crear cuenta') }}
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('invitation.decline', $invitation->token) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full btn-outline text-red-600 border-red-300 hover:bg-red-50">
                            {{ __('Rechazar') }}
                        </button>
                    </form>
                @endif

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center">
                        {{ __('Si rechazas la invitacion, se notificara a :name para que considere eliminar o anonimizar tus datos.', ['name' => $invitation->inviter->person?->full_name ?? $invitation->inviter->email]) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
