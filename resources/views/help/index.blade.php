<x-app-layout>
    <x-slot name="title">{{ __('Como usar') }} {{ config('app.name') }} - {{ config('app.name') }}</x-slot>

    <div class="bg-gray-100 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado -->
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-mf-primary mb-4">
                    {{ __('Como usar') }} {{ config('app.name') }}?
                </h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    {{ __('Guia rapida para comenzar a construir tu arbol genealogico y conectar con tu familia.') }}
                </p>
            </div>

            <!-- Indice -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="font-semibold text-gray-900 mb-4">{{ __('Contenido') }}</h2>
                <nav class="grid md:grid-cols-2 gap-2">
                    <a href="#primeros-pasos" class="text-mf-primary hover:underline">1. {{ __('Primeros pasos') }}</a>
                    <a href="#tu-perfil" class="text-mf-primary hover:underline">2. {{ __('Tu perfil') }}</a>
                    <a href="#arbol-genealogico" class="text-mf-primary hover:underline">3. {{ __('El arbol genealogico') }}</a>
                    <a href="#agregar-familiares" class="text-mf-primary hover:underline">4. {{ __('Agregar familiares') }}</a>
                    <a href="#privacidad" class="text-mf-primary hover:underline">5. {{ __('Privacidad y consentimiento') }}</a>
                    <a href="#mensajes" class="text-mf-primary hover:underline">6. {{ __('Sistema de mensajes') }}</a>
                    <a href="#gedcom" class="text-mf-primary hover:underline">7. {{ __('Importar y exportar datos') }}</a>
                    <a href="#busqueda" class="text-mf-primary hover:underline">8. {{ __('Buscar en la comunidad') }}</a>
                </nav>
            </div>

            <!-- Secciones de ayuda -->
            <div class="space-y-8">
                <!-- 1. Primeros pasos -->
                <section id="primeros-pasos" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">1</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Primeros pasos') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('Despues de crear tu cuenta y verificar tu correo electronico, seras recibido con una pantalla de bienvenida que te explica los conceptos basicos de la plataforma.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Que puedes hacer:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>{{ __('Crear y visualizar tu arbol genealogico familiar') }}</li>
                            <li>{{ __('Registrar informacion de tus ancestros y descendientes') }}</li>
                            <li>{{ __('Subir fotografias y documentos historicos') }}</li>
                            <li>{{ __('Conectar con otros miembros de la comunidad') }}</li>
                            <li>{{ __('Importar datos de otros programas genealogicos (formato GEDCOM)') }}</li>
                            <li>{{ __('Exportar tu arbol para respaldarlo o compartirlo') }}</li>
                        </ul>

                        <!-- Screenshot de bienvenida -->
                        <div class="mt-6">
                            <img src="{{ asset('images/help-welcome.jpg') }}" alt="{{ __('Pantalla de bienvenida') }}" class="rounded-lg shadow-md w-full">
                        </div>
                    </div>
                </section>

                <!-- 2. Tu perfil -->
                <section id="tu-perfil" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">2</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Tu perfil') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('Tu perfil es el punto de partida de tu arbol genealogico. Aqui defines tu informacion personal que sera el centro de tu arbol.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Informacion que puedes agregar:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Datos basicos:') }}</strong> {{ __('Nombre, apellidos, fecha y lugar de nacimiento') }}</li>
                            <li><strong>{{ __('Foto de perfil:') }}</strong> {{ __('Sube una imagen que te identifique') }}</li>
                            <li><strong>{{ __('Herencia cultural:') }}</strong> {{ __('Indica tu region de origen y herencia familiar') }}</li>
                            <li><strong>{{ __('Configuracion de privacidad:') }}</strong> {{ __('Define quien puede ver tu informacion') }}</li>
                        </ul>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-blue-800 text-sm">
                                <strong>{{ __('Consejo:') }}</strong> {{ __('Completa tu perfil lo mas posible para que otros familiares puedan reconocerte cuando busquen conexiones.') }}
                            </p>
                        </div>

                        <!-- Screenshot de perfil -->
                        <div class="mt-6">
                            <img src="{{ asset('images/help-profile.jpg') }}" alt="{{ __('Pagina de edicion de perfil') }}" class="rounded-lg shadow-md w-full">
                        </div>
                    </div>
                </section>

                <!-- 3. El arbol genealogico -->
                <section id="arbol-genealogico" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">3</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('El arbol genealogico') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('El arbol genealogico es una representacion visual de tu familia. Tu apareces en el centro, con tus ancestros hacia arriba y tus descendientes hacia abajo.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Como navegar el arbol:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Hacer clic en una persona:') }}</strong> {{ __('Centra el arbol en esa persona') }}</li>
                            <li><strong>{{ __('Boton + (mas):') }}</strong> {{ __('Agrega un nuevo familiar en esa posicion') }}</li>
                            <li><strong>{{ __('Arrastrar:') }}</strong> {{ __('Mueve la vista del arbol') }}</li>
                            <li><strong>{{ __('Zoom:') }}</strong> {{ __('Usa la rueda del raton o los controles para acercar/alejar') }}</li>
                        </ul>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Vistas disponibles:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Vista de arbol:') }}</strong> {{ __('Formato clasico con lineas de conexion') }}</li>
                            <li><strong>{{ __('Vista de abanico:') }}</strong> {{ __('Formato semicircular centrado en una persona') }}</li>
                            <li><strong>{{ __('Linea de tiempo:') }}</strong> {{ __('Eventos familiares ordenados cronologicamente') }}</li>
                        </ul>

                        <!-- Screenshot del arbol -->
                        <div class="mt-6">
                            <img src="{{ asset('images/help-tree.jpg') }}" alt="{{ __('Arbol genealogico') }}" class="rounded-lg shadow-md w-full">
                        </div>
                    </div>
                </section>

                <!-- 4. Agregar familiares -->
                <section id="agregar-familiares" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">4</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Agregar familiares') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('Puedes agregar familiares de dos formas: desde el arbol visual o desde el formulario de personas.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Desde el arbol:') }}</h3>
                        <ol class="list-decimal pl-6 space-y-2">
                            <li>{{ __('Haz clic en el boton + en la posicion deseada (padre, madre, hijo, etc.)') }}</li>
                            <li>{{ __('Completa el formulario con los datos del familiar') }}</li>
                            <li>{{ __('Si la persona esta viva, ingresa su correo electronico para solicitar su consentimiento') }}</li>
                            <li>{{ __('Guarda los cambios') }}</li>
                        </ol>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Tipos de relaciones:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Padres:') }}</strong> {{ __('Padre y madre biologicos o adoptivos') }}</li>
                            <li><strong>{{ __('Conyuges:') }}</strong> {{ __('Esposo/a o pareja') }}</li>
                            <li><strong>{{ __('Hijos:') }}</strong> {{ __('Hijos biologicos o adoptados') }}</li>
                        </ul>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                            <p class="text-yellow-800 text-sm">
                                <strong>{{ __('Importante:') }}</strong> {{ __('Para personas vivas, el sistema solicitara automaticamente su consentimiento antes de mostrar su informacion a otros usuarios.') }}
                            </p>
                        </div>

                        <!-- Screenshot de agregar persona -->
                        <div class="mt-6">
                            <img src="{{ asset('images/help-add-person.jpg') }}" alt="{{ __('Formulario de agregar persona') }}" class="rounded-lg shadow-md w-full">
                        </div>
                    </div>
                </section>

                <!-- 5. Privacidad y consentimiento -->
                <section id="privacidad" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">5</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Privacidad y consentimiento') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('Esta plataforma respeta la privacidad de todos. Por eso, antes de compartir informacion de personas vivas, se requiere su consentimiento.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Niveles de privacidad:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Familia directa:') }}</strong> {{ __('Solo padres, hijos y conyuges pueden ver tu informacion') }}</li>
                            <li><strong>{{ __('Familia extendida:') }}</strong> {{ __('Incluye abuelos, tios, primos, etc.') }}</li>
                            <li><strong>{{ __('Usuarios seleccionados:') }}</strong> {{ __('Tu eliges quien puede verte') }}</li>
                            <li><strong>{{ __('Comunidad:') }}</strong> {{ __('Todos los miembros verificados de la plataforma') }}</li>
                        </ul>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Como funciona el consentimiento:') }}</h3>
                        <ol class="list-decimal pl-6 space-y-2">
                            <li>{{ __('Agregas a una persona viva con su correo electronico') }}</li>
                            <li>{{ __('El sistema le envia un correo solicitando autorizacion') }}</li>
                            <li>{{ __('La persona puede aceptar, rechazar o registrarse en la plataforma') }}</li>
                            <li>{{ __('Si acepta, su informacion sera visible segun la configuracion de privacidad') }}</li>
                        </ol>
                    </div>
                </section>

                <!-- 6. Sistema de mensajes -->
                <section id="mensajes" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">6</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Sistema de mensajes') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('Puedes comunicarte con otros miembros de la comunidad a traves del sistema de mensajes interno.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Tipos de mensajes:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Mensajes directos:') }}</strong> {{ __('Conversaciones privadas con otros usuarios') }}</li>
                            <li><strong>{{ __('Invitaciones:') }}</strong> {{ __('Invita a familiares a unirse a tu arbol') }}</li>
                            <li><strong>{{ __('Solicitudes de consentimiento:') }}</strong> {{ __('Pide autorizacion para agregar a alguien') }}</li>
                            <li><strong>{{ __('Notificaciones del sistema:') }}</strong> {{ __('Avisos sobre tu cuenta y actividad') }}</li>
                        </ul>

                        <p class="mt-4">{{ __('Los mensajes no leidos se muestran con un indicador en el menu de navegacion.') }}</p>

                        <!-- Screenshot de mensajes -->
                        <div class="mt-6">
                            <img src="{{ asset('images/help-messages.jpg') }}" alt="{{ __('Bandeja de entrada de mensajes') }}" class="rounded-lg shadow-md w-full">
                        </div>
                    </div>
                </section>

                <!-- 7. Importar y exportar -->
                <section id="gedcom" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">7</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Importar y exportar datos') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('La plataforma soporta el formato GEDCOM, el estandar internacional para intercambio de datos genealogicos.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Importar datos:') }}</h3>
                        <ol class="list-decimal pl-6 space-y-2">
                            <li>{{ __('Ve a la seccion de importacion GEDCOM') }}</li>
                            <li>{{ __('Selecciona tu archivo .ged') }}</li>
                            <li>{{ __('Revisa la vista previa de los datos') }}</li>
                            <li>{{ __('Confirma la importacion') }}</li>
                        </ol>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Exportar datos:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Exportacion completa:') }}</strong> {{ __('Todos los datos de tu arbol') }}</li>
                            <li><strong>{{ __('Exportacion parcial:') }}</strong> {{ __('Solo una rama especifica') }}</li>
                            <li><strong>{{ __('Opciones de privacidad:') }}</strong> {{ __('Excluir informacion de personas vivas') }}</li>
                        </ul>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-blue-800 text-sm">
                                <strong>{{ __('Consejo:') }}</strong> {{ __('Exporta regularmente tu arbol como respaldo. Puedes importarlo en otros programas genealogicos como FamilySearch, Ancestry, o MyHeritage.') }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 8. Busqueda -->
                <section id="busqueda" class="bg-white rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">8</span>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Buscar en la comunidad') }}</h2>
                    </div>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ __('La funcion de busqueda te permite encontrar personas y posibles conexiones familiares dentro de la comunidad.') }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Tipos de busqueda:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Busqueda rapida:') }}</strong> {{ __('Busca por nombre desde cualquier pagina') }}</li>
                            <li><strong>{{ __('Busqueda avanzada:') }}</strong> {{ __('Filtra por apellido, lugar, fechas, etc.') }}</li>
                        </ul>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">{{ __('Que puedes encontrar:') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>{{ __('Personas con apellidos similares a los de tu familia') }}</li>
                            <li>{{ __('Familiares que otros usuarios han registrado') }}</li>
                            <li>{{ __('Posibles conexiones con tu arbol') }}</li>
                        </ul>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-blue-800 text-sm">
                                <strong>{{ __('Nota:') }}</strong> {{ __('Solo podras ver la informacion de personas segun su configuracion de privacidad y el consentimiento otorgado.') }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Seccion de contacto -->
                <section class="bg-mf-primary rounded-xl shadow-sm p-6 md:p-8 text-white text-center">
                    <h2 class="text-2xl font-bold mb-4">{{ __('Tienes mas preguntas?') }}</h2>
                    <p class="mb-6 opacity-90">
                        {{ __('Si necesitas ayuda adicional, no dudes en contactarnos.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('messages.compose') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white text-mf-primary font-semibold rounded-full hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Enviar mensaje') }}
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
