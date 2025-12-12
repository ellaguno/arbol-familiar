<x-app-layout>
    <x-slot name="title">{{ __('Bienvenido') }} - {{ config('app.name') }}</x-slot>

    <!-- Seccion Superior - Fondo gris -->
    <section class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 items-start">
                <!-- Columna izquierda - Texto -->
                <div class="space-y-6 lg:pr-6">
                    <h1 class="text-4xl md:text-5xl font-bold">
                        <span class="text-amber-500">{{ __('¡Hola') }} {{ $user->person ? $user->person->first_name : '' }}!</span>
                    </h1>

                    <h2 class="text-xl md:text-2xl font-semibold text-[#3b82f6]">
                        {{ __('Estas a un paso de comenzar a construir tu arbol genealogico y conectar con tu historia familiar.') }}
                    </h2>

                    <div class="text-gray-600 space-y-4">
                        <p>
                            {{ __('Nos alegra que formes parte de esta comunidad dedicada a entender nuestras raices y fortalecer los lazos familiares.') }}
                        </p>

                        <p>
                            {{ __('En') }} <strong>{{ config('app.name') }}</strong> {{ __('la informacion que adjuntes a tu arbol es privada y solo sera compartida con quien tu elijas hacerlo. Por ello, la informacion que ingreses sobre parientes vivos requerira introducir un correo electronico de la persona agregada. El o ella recibira un correo automatico solicitando su autorizacion para compartir sus datos y una invitacion para participar en el sitio.') }}
                        </p>

                        <p>
                            {{ __('Si ya es un miembro recibira un mensaje en su seccion de mensajes invitandole a unirse a tu arbol. Asi de sencillo!') }}
                        </p>

                        <p>
                            {{ __('Si tienes alguna duda, visita la seccion') }}
                            <a href="{{ route('help') }}" class="text-[#3b82f6] underline hover:text-[#1d4ed8]">{{ __('Como usar Mi Familia?') }}</a>
                            {{ __('Donde encontraras respuestas a las dudas mas comunes.') }}
                        </p>
                    </div>

                    <p class="text-amber-500 font-semibold text-lg">
                        {{ __('Gracias por ser parte de este proyecto tan especial!') }}
                    </p>
                </div>

                <!-- Columna derecha - Imagenes -->
                <div class="relative">
                    <!-- Contenedor de imagenes -->
                    <div class="relative">
                        <!-- Imagen de fondo -->
                        <div class="relative rounded-t-lg overflow-hidden shadow-xl">
                            <img src="{{ asset('images/familia_fondo.jpg') }}"
                                 alt="{{ __('Familia historica') }}"
                                 class="w-full h-auto object-cover"
                                 onerror="this.src='https://placehold.co/600x400/3b82f6/white?text=Mi+Familia'">
                        </div>

                        <!-- Imagen circular superpuesta -->
                        <div style="left: -2rem;"class="absolute -bottom-16 w-52 h-52 md:w-48 md:h-48 rounded-full border-4 border-white shadow-lg overflow-hidden z-10">
                            <img src="{{ asset('images/familia_moderna.jpg') }}"
                                 alt="{{ __('Familia moderna') }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='https://placehold.co/200x200/3b82f6/white?text=Familia'">
                        </div>

                        <!-- Texto descriptivo -->
                        <div class="bg-[#3b82f6] text-white p-4 rounded-b-lg">
                            <p class="text-sm pl-40 md:pl-52">
                                {{ __('Mi Familia es una plataforma que busca ayudarnos a entender nuestras raices y fortalecer los lazos familiares y comunitarios.') }}
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
                        <img src="{{ asset('images/circulos2.jpg') }}"
                             alt=""
                             class="w-full max-w-md h-auto rounded-lg">
                    </div>

                    <!-- Columna derecha - Instrucciones -->
                    <div class="space-y-6">
                        <h3 class="text-xl md:text-2xl font-bold text-[#3b82f6]">
                            {{ __('Para iniciar, alimenta tu perfil con más información, esta servirá como punto de partida. A partir de ahí podrás agregar más familiares desde tu árbol.') }}
                        </h3>

                        <div class="text-gray-600 space-y-4">
                            <p>
                                {{ __('En el árbol verás un recuadro con tus datos. Da click sobre él y se desplegará una sección en el lado derecho, desde ahí podrás acceder a tu perfil o agregar familiares inmediatos: padres, cónyuge, hijos y hermanos.') }}
                            </p>

                            <p>
                                {{ __('Una vez agregado un nuevo familiar, podrás seleccionarlo y la sección lateral mostrará sus datos. Dando click sobre el botón Centrar en el árbol podrás agregar familiares directos a su posición: padres, cónyuge, hijos y hermanos, expandiendo de esta forma tu árbol. Para regresar a ti, solo debes hacer click en tu posición.') }}
                            </p>

                            <p>
                                {{ __('Podrás editar más información de cada usuario y sus relaciones desde su perfil individual. También podrás buscar más familiares en la opción Búsqueda desde el menú superior, o en la sección Agregar relación desde cada perfil.') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('welcome.complete') }}" class="pt-4">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-4 bg-[#3b82f6] text-white font-semibold rounded-full hover:bg-[#1d4ed8] transition-colors shadow-lg">
                                {{ __('Continua a editar tu perfil') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
