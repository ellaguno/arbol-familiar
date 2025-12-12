<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <title>{{ config('app.name', 'Mi Familia') }} - {{ __('Tu arbol genealogico') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .genealogy-overlay {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }
        /* Efecto de imagenes flotantes solo en desktop */
        @media (min-width: 768px) {
            .floating-image {
                transform: translateY(-55%);
            }
            .floating-text {
                margin-top: -4.5rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-white">
    <x-header :hideLogo="true" />

    <!-- Hero con imagen -->
    <section class="relative min-h-[300px] md:min-h-[350px] overflow-hidden">
        <!-- Imagen de fondo -->
        <img src="{{ asset('images/hero-beach.jpg') }}" alt="{{ __('Familia') }}"
             class="absolute inset-0 w-full h-full object-cover">
        <!-- Overlay con gradiente -->
        <div class="absolute inset-0 bg-gradient-to-b from-sky-200/30 "></div>
        <!-- Diagrama genealogico decorativo -->
        <div class="genealogy-overlay relative z-10">
            <img src="{{ asset('images/portada_diagrama.svg') }}" alt="{{ __('Diagrama genealogico') }}" class="w-48 md:w-64 opacity-80">
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="bg-gradient-to-b from-gray-100 to-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <!-- Lado izquierdo - Contenido -->
                <div>
                    <!-- Logo -->
                    <div class="mb-6 flex justify-center lg:space-around">
                        <img src="/images/logo.png" alt="{{ config('app.name') }}" class="h-24 md:h-32 object-contain"
                             onerror="this.outerHTML='<h1 class=\'text-5xl md:text-6xl font-bold text-[#3b82f6]\'>Mi Familia</h1>'">
                    </div>

                    <h2 class="text-2xl md:text-3xl font-bold text-amber-500 mb-2">
                        {{ __('¡Conecta con tu familia!') }}
                    </h2>
                    <p class="text-amber-400 text-lg mb-6">
                        {{ __('Construye tu árbol genealógico y descubre los momentos más importantes de tu historia.') }}
                    </p>

                    <div class="text-gray-700 space-y-4 text-sm leading-relaxed">
                        <p>
                            <strong class="text-[#3b82f6]">{{ config('app.name') }}</strong> {{ __('es un espacio creado para reunir a las familias y sus descendientes. Nuestra intención es preservar la memoria de nuestras familias y fortalecer los lazos con nuestra comunidad y nuestros parientes en todas partes del mundo.') }}
                        </p>
                        <p>
                            {{ __('En este sitio podrás registrar tu historia, invitar a tus familiares, encontrar parientes y descubrir relaciones que no conocías y así formar parte de un legado vivo que une pasado, presente y futuro.') }}
                        </p>
                    </div>
                </div>

                <!-- Lado derecho - Login y Registro -->
                <div class="space-y-4">
                    <!-- Caja de Login -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('¡Hola! Inicia tu sesión') }}</h3>

                        <form method="POST" action="{{ route('login') }}" class="space-y-4" id="welcome-login-form">
                            @csrf

                            <div>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="{{ __('Usuario') }}" required autofocus>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <input type="password" name="password" id="password"
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="{{ __('Contraseña') }}" required>
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="text-right">
                                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                                    {{ __('¿Olvidaste tu usuario o contraseña?') }}
                                </a>
                            </div>

                            <!-- reCAPTCHA v3 (invisible) -->
                            @if(config('mi-familia.recaptcha.enabled'))
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-response-welcome">
                                @error('g-recaptcha-response')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 text-center">
                                    {{ __('Protegido por reCAPTCHA') }} -
                                    <a href="https://policies.google.com/privacy" target="_blank" class="underline">{{ __('Privacidad') }}</a> y
                                    <a href="https://policies.google.com/terms" target="_blank" class="underline">{{ __('Términos') }}</a>
                                </p>
                            @endif

                            @if(session('error'))
                                <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <button type="submit" class="w-full btn-primary">
                                {{ __('Ingresar') }}
                            </button>
                        </form>
                    </div>

                    <!-- Caja de Registro -->
                    <div class="bg-[#3b82f6] rounded-lg p-6 text-center">
                        <p class="text-white mb-3">{{ __('¿Todavía no estás registrado?') }}</p>
                        <a href="{{ route('register') }}" class="w-full btn-accent">
                            {{ __('¡Da click aquí y únete!') }}
                        </a>
                        <p class="text-blue-200 text-sm mt-4">
                            {{ __('Disfruta tu origen y vive la historia.') }}<br>
                            {{ __('Compártelo con los miembros de tu familia.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Espaciador para las imagenes que salen (solo desktop) -->
    <div class="hidden md:block h-20 bg-white"></div>

    <!-- Seccion de tres columnas -->
    <section class="py-16 md:pb-16 md:pt-0" style="margin-top: 50px; background-color:#eae8e4;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Columna 1 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 rounded-full overflow-hidden bg-gray-200 floating-image">
                        <img src="/images/feature-start.jpg" alt="{{ __('Empezar') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>1</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold text-[#3b82f6] mb-3">{{ __('¡Solo necesitas empezar!') }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            {{ __('Es muy sencillo, ingresa primero tus datos y después podrás añadir a tus padres, abuelos, hermanos, hijos y demás familiares. Una vez agregados podrás invitarlos a participar en tu árbol y compartir información, imágenes y documentos de su historia.') }}
                        </p>
                    </div>
                </div>
                <!-- Columna 2 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 rounded-full overflow-hidden bg-gray-200 floating-image">
                        <img src="/images/feature-import.jpg" alt="{{ __('Importar') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>2</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold text-[#3b82f6] mb-3">{{ __('¿Tienes un árbol en otro sitio?') }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            {{ __('¡Tráelo para acá! Esta plataforma trabaja con datos de clasificación Gedcom, el estándar compartido de las principales bases de datos genealógicas, así que si tienes registros en otras plataformas, puedes importar su información fácilmente.') }}
                        </p>
                    </div>
                </div>
                <!-- Columna 3 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 rounded-full overflow-hidden bg-gray-200 floating-image">
                        <img src="/images/feature-privacy.jpg" alt="{{ __('Privacidad') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>3</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold text-[#3b82f6] mb-3">{{ __('Tú eliges con quien compartir.') }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            {{ __('Tu información es tuya y no saldrá de este sitio. Podrás elegir compartirlo con tu familia y tu comunidad. Solo podrán consultarla quienes tú autorices.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Espaciador inferior (solo desktop) -->
    <div class="hidden md:block h-20" style="background-color:#eae8e4;"></div>

    <!-- Seccion: Uso libre -->
    <section class="py-12 border-b-4 border-white" style="background-color: #cfcfcf;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-[#3b82f6] mb-4">{{ config('app.name') }} {{ __('es de uso libre.') }}</h2>
                    <p class="text-gray-700 text-sm leading-relaxed mb-4">
                        {{ __('El ingreso y uso de') }} <strong class="text-[#3b82f6]">{{ config('app.name') }}</strong> {{ __('es gratuito para todos los usuarios y sus familiares.') }}
                    </p>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        {{ __('Registra tu historia familiar, conecta con tus parientes y preserva la memoria de tu familia para las generaciones futuras.') }}
                    </p>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="w-110 h-64 rounded-lg overflow-hidden bg-gray-300">
                        <img src="/images/feature-privacy.jpg" alt="{{ __('Familia') }}" class="w-full h-full object-cover"
                             onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-footer />

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- reCAPTCHA v3 (invisible) -->
    @if(config('mi-familia.recaptcha.enabled'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('mi-familia.recaptcha.site_key') }}"></script>
        <style>
            /* Ocultar badge de reCAPTCHA v3 (permitido por Google si muestras atribucion) */
            .grecaptcha-badge { visibility: hidden !important; }
        </style>
        <script>
            document.getElementById('welcome-login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('mi-familia.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                        document.getElementById('recaptcha-response-welcome').value = token;
                        form.submit();
                    });
                });
            });
        </script>
    @endif
</body>
</html>
