<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $siteThemeClass ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

    <title>{{ config('app.name', 'Mi Familia') }} - {{ __('Tu arbol genealogico') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $siteFontUrl ?? 'https://fonts.bunny.net/css?family=ubuntu:400,500,600,700&display=swap' }}" rel="stylesheet" />

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
        }
        body { font-family: var(--mf-font) !important; }
    </style>

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
<body class="font-sans antialiased" style="background-color: var(--mf-bg-card);">
    <x-header :hideLogo="true" />

    @php
        $sc = isset($siteSettings) ? $siteSettings : null;
    @endphp

    <!-- Hero con imagen -->
    @php
        $heroShow = $sc ? $sc->content('welcome', 'hero_show', '1') : '1';
    @endphp
    @if($heroShow)
        <section class="relative overflow-hidden">
            <!-- Imagen de fondo (usa su altura natural) -->
            <img src="{{ asset($sc ? $sc->content('welcome', 'hero_image', 'images/hero-beach.jpg') : 'images/hero-beach.jpg') }}" alt="{{ __('Familia') }}"
                 class="w-full h-auto object-cover max-h-[450px]">
            <!-- Overlay con gradiente -->
            <div class="absolute inset-0 bg-gradient-to-b from-sky-200/30 "></div>
            <!-- Diagrama genealogico decorativo -->
            <div class="genealogy-overlay relative z-10">
                <img src="{{ asset('images/portada_diagrama.svg') }}" alt="{{ __('Diagrama genealogico') }}" class="w-48 md:w-64 opacity-80">
            </div>
        </section>
    @endif

    <!-- Contenido principal -->
    <section class="bg-gradient-to-b from-theme to-theme-card py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <!-- Lado izquierdo - Contenido -->
                <div>
                    <!-- Logo -->
                    <div class="mb-6 flex justify-center lg:space-around">
                        <img src="{{ asset($sc ? $sc->content('welcome', 'logo_image', 'images/logo.png') : 'images/logo.png') }}" alt="{{ config('app.name') }}" class="h-24 md:h-32 object-contain"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<h1 class=&quot;text-5xl md:text-6xl font-bold&quot; style=&quot;color: var(--mf-primary, #3b82f6)&quot;>{{ config("app.name") }}</h1>'">
                    </div>

                    <h2 class="text-2xl md:text-3xl font-bold mb-2" style="color: var(--mf-accent, #f59e0b);">
                        {{ $sc ? $sc->content('welcome', 'hero_title', __('¡Conecta con tu familia!')) : __('¡Conecta con tu familia!') }}
                    </h2>
                    <p class="text-lg mb-6" style="color: var(--mf-accent, #f59e0b); opacity: 0.8;">
                        {{ $sc ? $sc->content('welcome', 'hero_subtitle', __('Construye tu árbol genealógico y descubre los momentos más importantes de tu historia.')) : __('Construye tu árbol genealógico y descubre los momentos más importantes de tu historia.') }}
                    </p>

                    <div class="text-theme-secondary space-y-4 text-sm leading-relaxed">
                        <p>
                            <strong style="color: var(--mf-primary, #3b82f6);">{{ config('app.name') }}</strong> {{ $sc ? $sc->content('welcome', 'description_1', __('es un espacio creado para reunir a las familias y sus descendientes. Nuestra intención es preservar la memoria de nuestras familias y fortalecer los lazos con nuestra comunidad y nuestros parientes en todas partes del mundo.')) : __('es un espacio creado para reunir a las familias y sus descendientes. Nuestra intención es preservar la memoria de nuestras familias y fortalecer los lazos con nuestra comunidad y nuestros parientes en todas partes del mundo.') }}
                        </p>
                        <p>
                            {{ $sc ? $sc->content('welcome', 'description_2', __('En este sitio podrás registrar tu historia, invitar a tus familiares, encontrar parientes y descubrir relaciones que no conocías y así formar parte de un legado vivo que une pasado, presente y futuro.')) : __('En este sitio podrás registrar tu historia, invitar a tus familiares, encontrar parientes y descubrir relaciones que no conocías y así formar parte de un legado vivo que une pasado, presente y futuro.') }}
                        </p>
                    </div>
                </div>

                <!-- Lado derecho - Login y Registro -->
                <div class="space-y-4">
                    <!-- Caja de Login -->
                    <div class="bg-theme-card rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-theme mb-4">{{ $sc ? $sc->content('welcome', 'login_title', __('¡Hola! Inicia tu sesión')) : __('¡Hola! Inicia tu sesión') }}</h3>

                        <form method="POST" action="{{ route('login') }}" class="space-y-4" id="welcome-login-form">
                            @csrf

                            <div>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="w-full px-4 py-2 border border-theme rounded focus:ring-2 focus:border-blue-500" style="--tw-ring-color: var(--mf-primary, #3b82f6);"
                                       placeholder="{{ __('Usuario') }}" required autofocus>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <input type="password" name="password" id="password"
                                       class="w-full px-4 py-2 border border-theme rounded focus:ring-2 focus:border-blue-500" style="--tw-ring-color: var(--mf-primary, #3b82f6);"
                                       placeholder="{{ __('Contraseña') }}" required>
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="text-right">
                                <a href="{{ route('password.request') }}" class="text-sm hover:underline" style="color: var(--mf-primary, #3b82f6);">
                                    {{ __('¿Olvidaste tu usuario o contraseña?') }}
                                </a>
                            </div>

                            <!-- reCAPTCHA v3 (invisible) -->
                            @if(config('mi-familia.recaptcha.enabled'))
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-response-welcome">
                                @error('g-recaptcha-response')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-theme-muted text-center">
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
                    <div class="rounded-lg p-6 text-center" style="background-color: var(--mf-primary, #3b82f6);">
                        <p class="text-white mb-3">{{ $sc ? $sc->content('welcome', 'register_question', __('¿Todavía no estás registrado?')) : __('¿Todavía no estás registrado?') }}</p>
                        <a href="{{ route('register') }}" class="w-full btn-accent">
                            {{ $sc ? $sc->content('welcome', 'register_cta', __('¡Da click aquí y únete!')) : __('¡Da click aquí y únete!') }}
                        </a>
                        <p class="text-blue-200 text-sm mt-4">
                            {{ $sc ? $sc->content('welcome', 'register_tagline', __('Disfruta tu origen y vive la historia. Compártelo con los miembros de tu familia.')) : __('Disfruta tu origen y vive la historia.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Espaciador para las imagenes que salen (solo desktop) -->
    <div class="hidden md:block h-20 bg-theme-card"></div>

    <!-- Seccion de tres columnas -->
    @php
        $featureShape = $sc ? $sc->content('welcome', 'feature_images_shape', 'round') : 'round';
        $featureRounded = $featureShape === 'round' ? 'rounded-full' : 'rounded-xl';
    @endphp
    <section class="py-16 md:pb-16 md:pt-0 bg-theme-secondary" style="margin-top: 50px;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Columna 1 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 {{ $featureRounded }} overflow-hidden bg-theme-secondary floating-image">
                        <img src="{{ asset($sc ? $sc->content('welcome', 'feature_1_image', 'images/feature-start.jpg') : 'images/feature-start.jpg') }}" alt="{{ __('Empezar') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>1</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold mb-3" style="color: var(--mf-primary, #3b82f6);">{{ $sc ? $sc->content('welcome', 'feature_1_title', __('¡Solo necesitas empezar!')) : __('¡Solo necesitas empezar!') }}</h3>
                        <p class="text-theme-secondary text-sm leading-relaxed">
                            {{ $sc ? $sc->content('welcome', 'feature_1_text', __('Es muy sencillo, ingresa primero tus datos y después podrás añadir a tus padres, abuelos, hermanos, hijos y demás familiares. Una vez agregados podrás invitarlos a participar en tu árbol y compartir información, imágenes y documentos de su historia.')) : __('Es muy sencillo, ingresa primero tus datos y después podrás añadir a tus padres, abuelos, hermanos, hijos y demás familiares. Una vez agregados podrás invitarlos a participar en tu árbol y compartir información, imágenes y documentos de su historia.') }}
                        </p>
                    </div>
                </div>
                <!-- Columna 2 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 {{ $featureRounded }} overflow-hidden bg-theme-secondary floating-image">
                        <img src="{{ asset($sc ? $sc->content('welcome', 'feature_2_image', 'images/feature-import.jpg') : 'images/feature-import.jpg') }}" alt="{{ __('Importar') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>2</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold mb-3" style="color: var(--mf-primary, #3b82f6);">{{ $sc ? $sc->content('welcome', 'feature_2_title', __('¿Tienes un árbol en otro sitio?')) : __('¿Tienes un árbol en otro sitio?') }}</h3>
                        <p class="text-theme-secondary text-sm leading-relaxed">
                            {{ $sc ? $sc->content('welcome', 'feature_2_text', __('¡Tráelo para acá! Esta plataforma trabaja con datos de clasificación Gedcom, el estándar compartido de las principales bases de datos genealógicas, así que si tienes registros en otras plataformas, puedes importar su información fácilmente.')) : __('¡Tráelo para acá! Esta plataforma trabaja con datos de clasificación Gedcom, el estándar compartido de las principales bases de datos genealógicas, así que si tienes registros en otras plataformas, puedes importar su información fácilmente.') }}
                        </p>
                    </div>
                </div>
                <!-- Columna 3 -->
                <div class="text-center">
                    <div class="w-40 h-40 mx-auto mb-6 {{ $featureRounded }} overflow-hidden bg-theme-secondary floating-image">
                        <img src="{{ asset($sc ? $sc->content('welcome', 'feature_3_image', 'images/feature-privacy.jpg') : 'images/feature-privacy.jpg') }}" alt="{{ __('Privacidad') }}" class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23e5e7eb%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%239ca3af%22 font-size=%2230%22>3</text></svg>'">
                    </div>
                    <div class="floating-text">
                        <h3 class="text-xl font-bold mb-3" style="color: var(--mf-primary, #3b82f6);">{{ $sc ? $sc->content('welcome', 'feature_3_title', __('Tú eliges con quien compartir.')) : __('Tú eliges con quien compartir.') }}</h3>
                        <p class="text-theme-secondary text-sm leading-relaxed">
                            {{ $sc ? $sc->content('welcome', 'feature_3_text', __('Tu información es tuya y no saldrá de este sitio. Podrás elegir compartirlo con tu familia y tu comunidad. Solo podrán consultarla quienes tú autorices.')) : __('Tu información es tuya y no saldrá de este sitio. Podrás elegir compartirlo con tu familia y tu comunidad. Solo podrán consultarla quienes tú autorices.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Espaciador inferior (solo desktop) -->
    <div class="hidden md:block h-20 bg-theme-secondary"></div>

    <!-- Seccion: Uso libre -->
    <section class="py-12 border-b-4 border-theme bg-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-4" style="color: var(--mf-primary, #3b82f6);">{{ config('app.name') }} {{ $sc ? $sc->content('welcome', 'free_title', __('es de uso libre.')) : __('es de uso libre.') }}</h2>
                    <p class="text-theme-secondary text-sm leading-relaxed mb-4">
                        {{ __('El ingreso y uso de') }} <strong style="color: var(--mf-primary, #3b82f6);">{{ config('app.name') }}</strong> {{ $sc ? $sc->content('welcome', 'free_text_1', __('es gratuito para todos los usuarios y sus familiares.')) : __('es gratuito para todos los usuarios y sus familiares.') }}
                    </p>
                    <p class="text-theme-secondary text-sm leading-relaxed">
                        {{ $sc ? $sc->content('welcome', 'free_text_2', __('Registra tu historia familiar, conecta con tus parientes y preserva la memoria de tu familia para las generaciones futuras.')) : __('Registra tu historia familiar, conecta con tus parientes y preserva la memoria de tu familia para las generaciones futuras.') }}
                    </p>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="w-110 h-64 rounded-lg overflow-hidden bg-theme-secondary">
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
