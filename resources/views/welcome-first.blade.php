<x-app-layout>
    <x-slot name="title">{{ __('Bienvenido') }} - {{ config('app.name') }}</x-slot>

    @php
        $sc = isset($siteSettings) ? $siteSettings : null;
    @endphp

    <!-- Seccion Superior - Fondo gris -->
    <section class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 items-start">
                <!-- Columna izquierda - Texto -->
                <div class="space-y-6 lg:pr-6">
                    <h1 class="text-4xl md:text-5xl font-bold">
                        <span style="color: var(--mf-accent, #f59e0b);">{{ __('¡Hola') }} {{ $user->person ? $user->person->first_name : '' }}!</span>
                    </h1>

                    <h2 class="text-xl md:text-2xl font-semibold" style="color: var(--mf-primary, #3b82f6);">
                        {{ $sc ? $sc->content('welcome_first', 'greeting_text', __('Estas a un paso de comenzar a construir tu arbol genealogico y conectar con tu historia familiar.')) : __('Estas a un paso de comenzar a construir tu arbol genealogico y conectar con tu historia familiar.') }}
                    </h2>

                    <div class="text-gray-600 space-y-4">
                        <p>
                            {{ $sc ? $sc->content('welcome_first', 'description_1', __('Nos alegra que formes parte de esta comunidad dedicada a entender nuestras raices y fortalecer los lazos familiares.')) : __('Nos alegra que formes parte de esta comunidad dedicada a entender nuestras raices y fortalecer los lazos familiares.') }}
                        </p>

                        <p>
                            {{ __('En') }} <strong>{{ config('app.name') }}</strong> {{ $sc ? $sc->content('welcome_first', 'description_2', __('la informacion que adjuntes a tu arbol es privada y solo sera compartida con quien tu elijas hacerlo. Por ello, la informacion que ingreses sobre parientes vivos requerira introducir un correo electronico de la persona agregada. El o ella recibira un correo automatico solicitando su autorizacion para compartir sus datos y una invitacion para participar en el sitio.')) : __('la informacion que adjuntes a tu arbol es privada y solo sera compartida con quien tu elijas hacerlo. Por ello, la informacion que ingreses sobre parientes vivos requerira introducir un correo electronico de la persona agregada. El o ella recibira un correo automatico solicitando su autorizacion para compartir sus datos y una invitacion para participar en el sitio.') }}
                        </p>

                        <p>
                            {{ $sc ? $sc->content('welcome_first', 'description_3', __('Si ya es un miembro recibira un mensaje en su seccion de mensajes invitandole a unirse a tu arbol. Asi de sencillo!')) : __('Si ya es un miembro recibira un mensaje en su seccion de mensajes invitandole a unirse a tu arbol. Asi de sencillo!') }}
                        </p>

                        <p>
                            {{ __('Si tienes alguna duda, visita la seccion') }}
                            <a href="{{ route('help') }}" class="underline" style="color: var(--mf-primary, #3b82f6);">{{ __('Como usar Mi Familia?') }}</a>
                            {{ __('Donde encontraras respuestas a las dudas mas comunes.') }}
                        </p>
                    </div>

                    <p class="font-semibold text-lg" style="color: var(--mf-accent, #f59e0b);">
                        {{ $sc ? $sc->content('welcome_first', 'thanks_text', __('Gracias por ser parte de este proyecto tan especial!')) : __('Gracias por ser parte de este proyecto tan especial!') }}
                    </p>
                </div>

                <!-- Columna derecha - Imagenes -->
                <div class="relative">
                    <!-- Contenedor de imagenes -->
                    <div class="relative">
                        <!-- Imagen de fondo -->
                        <div class="relative rounded-t-lg overflow-hidden shadow-xl">
                            <img src="{{ asset($sc ? $sc->content('welcome_first', 'main_image', 'images/familia_fondo.jpg') : 'images/familia_fondo.jpg') }}"
                                 alt="{{ __('Familia historica') }}"
                                 class="w-full h-auto object-cover"
                                 onerror="this.src='https://placehold.co/600x400/3b82f6/white?text=Mi+Familia'">
                        </div>

                        <!-- Imagen circular superpuesta -->
                        <div style="left: -2rem;"class="absolute -bottom-16 w-52 h-52 md:w-48 md:h-48 rounded-full border-4 border-white shadow-lg overflow-hidden z-10">
                            <img src="{{ asset($sc ? $sc->content('welcome_first', 'circle_image', 'images/familia_moderna.jpg') : 'images/familia_moderna.jpg') }}"
                                 alt="{{ __('Familia moderna') }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='https://placehold.co/200x200/3b82f6/white?text=Familia'">
                        </div>

                        <!-- Texto descriptivo -->
                        <div class="text-white p-4 rounded-b-lg" style="background-color: var(--mf-primary, #3b82f6);">
                            <p class="text-sm pl-40 md:pl-52">
                                {{ $sc ? $sc->content('welcome_first', 'banner_text', __('Mi Familia es una plataforma que busca ayudarnos a entender nuestras raices y fortalecer los lazos familiares y comunitarios.')) : __('Mi Familia es una plataforma que busca ayudarnos a entender nuestras raices y fortalecer los lazos familiares y comunitarios.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seccion Inferior - Como empezar -->
    <section class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Contenedor con fondo beige y margen blanco -->
            <div class="bg-[#eae8e4] rounded-xl p-8 md:p-12">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Columna izquierda - Circulos decorativos -->
                    <div class="flex justify-center lg:justify-start">
                        <img src="{{ asset($sc ? $sc->content('welcome_first', 'decorative_image', 'images/circulos2.jpg') : 'images/circulos2.jpg') }}"
                             alt=""
                             class="w-full max-w-md h-auto rounded-lg">
                    </div>

                    <!-- Columna derecha - Instrucciones -->
                    <div class="space-y-6">
                        <h3 class="text-xl md:text-2xl font-bold" style="color: var(--mf-primary, #3b82f6);">
                            {{ $sc ? $sc->content('welcome_first', 'instructions_title', __('Para iniciar, alimenta tu perfil con más información, esta servirá como punto de partida. A partir de ahí podrás agregar más familiares desde tu árbol.')) : __('Para iniciar, alimenta tu perfil con más información, esta servirá como punto de partida. A partir de ahí podrás agregar más familiares desde tu árbol.') }}
                        </h3>

                        <div class="text-gray-600 space-y-4">
                            <p>
                                {{ $sc ? $sc->content('welcome_first', 'instructions_1', __('En el árbol verás un recuadro con tus datos. Da click sobre él y se desplegará una sección en el lado derecho, desde ahí podrás acceder a tu perfil o agregar familiares inmediatos: padres, cónyuge, hijos y hermanos.')) : __('En el árbol verás un recuadro con tus datos. Da click sobre él y se desplegará una sección en el lado derecho, desde ahí podrás acceder a tu perfil o agregar familiares inmediatos: padres, cónyuge, hijos y hermanos.') }}
                            </p>

                            <p>
                                {{ $sc ? $sc->content('welcome_first', 'instructions_2', __('Una vez agregado un nuevo familiar, podrás seleccionarlo y la sección lateral mostrará sus datos. Dando click sobre el botón Centrar en el árbol podrás agregar familiares directos a su posición: padres, cónyuge, hijos y hermanos, expandiendo de esta forma tu árbol. Para regresar a ti, solo debes hacer click en tu posición.')) : __('Una vez agregado un nuevo familiar, podrás seleccionarlo y la sección lateral mostrará sus datos. Dando click sobre el botón Centrar en el árbol podrás agregar familiares directos a su posición: padres, cónyuge, hijos y hermanos, expandiendo de esta forma tu árbol. Para regresar a ti, solo debes hacer click en tu posición.') }}
                            </p>

                            <p>
                                {{ $sc ? $sc->content('welcome_first', 'instructions_3', __('Podrás editar más información de cada usuario y sus relaciones desde su perfil individual. También podrás buscar más familiares en la opción Búsqueda desde el menú superior, o en la sección Agregar relación desde cada perfil.')) : __('Podrás editar más información de cada usuario y sus relaciones desde su perfil individual. También podrás buscar más familiares en la opción Búsqueda desde el menú superior, o en la sección Agregar relación desde cada perfil.') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('welcome.complete') }}" class="pt-4">
                            @csrf
                            <button type="submit" class="btn-primary btn-lg shadow-lg">
                                {{ $sc ? $sc->content('welcome_first', 'cta_text', __('Continua a editar tu perfil')) : __('Continua a editar tu perfil') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
