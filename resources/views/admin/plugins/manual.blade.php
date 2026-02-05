<x-app-layout>
    <x-slot name="title">{{ __('Manual de Plugins') }} - {{ __('Administracion') }}</x-slot>

    <div class="bg-theme py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Encabezado --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-mf-primary">{{ __('Manual de Plugins') }}</h1>
                    <p class="text-theme-secondary mt-1">{{ __('Documentacion del sistema de plugins de') }} {{ config('app.name') }}</p>
                </div>
                <a href="{{ route('admin.plugins') }}" class="btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver a Plugins') }}
                </a>
            </div>

            {{-- Indice --}}
            <div class="bg-theme-card rounded-xl shadow-sm p-6 mb-8">
                <h2 class="font-semibold text-theme mb-4">{{ __('Contenido') }}</h2>
                <nav class="grid md:grid-cols-2 gap-2">
                    <a href="#plugins-incluidos" class="text-mf-primary hover:underline">1. {{ __('Plugins incluidos') }}</a>
                    <a href="#ciclo-vida" class="text-mf-primary hover:underline">2. {{ __('Ciclo de vida') }}</a>
                    <a href="#configurar-pusher" class="text-mf-primary hover:underline">3. {{ __('Configurar Pusher (chat)') }}</a>
                    <a href="#compatibilidad" class="text-mf-primary hover:underline">4. {{ __('Compatibilidad') }}</a>
                    <a href="#subir-plugin" class="text-mf-primary hover:underline">5. {{ __('Subir y eliminar plugins') }}</a>
                    <a href="#crear-plugin" class="text-mf-primary hover:underline">6. {{ __('Crear un plugin personalizado') }}</a>
                </nav>
            </div>

            <div class="space-y-8">
                {{-- 1. Plugins incluidos --}}
                <section id="plugins-incluidos" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">1</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Plugins incluidos') }}</h2>
                    </div>

                    <p class="text-theme-secondary mb-6">{{ __('Mi Familia incluye 5 plugins listos para usar:') }}</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-theme">
                                    <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Plugin') }}</th>
                                    <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Tipo') }}</th>
                                    <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Descripcion') }}</th>
                                    <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Formatos') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-theme-light">
                                <tr>
                                    <td class="py-3 px-3 font-medium text-theme">{{ __('Reporte de Ancestros') }}</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">{{ __('Reporte') }}</span></td>
                                    <td class="py-3 px-3 text-theme-secondary">{{ __('Lista de ancestros con numeracion Ahnentafel (1=persona, 2=padre, 3=madre, 4-7=abuelos...)') }}</td>
                                    <td class="py-3 px-3 text-theme-secondary">HTML, PDF</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-medium text-theme">{{ __('Reporte de Descendientes') }}</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">{{ __('Reporte') }}</span></td>
                                    <td class="py-3 px-3 text-theme-secondary">{{ __('Arbol indentado de descendientes con conyuges e hijos') }}</td>
                                    <td class="py-3 px-3 text-theme-secondary">HTML, PDF</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-medium text-theme">{{ __('Grafico de Abanico') }}</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">{{ __('Reporte') }}</span></td>
                                    <td class="py-3 px-3 text-theme-secondary">{{ __('Diagrama semicircular de ancestros con arcos por generacion') }}</td>
                                    <td class="py-3 px-3 text-theme-secondary">HTML, SVG, PDF</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-medium text-theme">{{ __('Cuadro de Pedigri') }}</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">{{ __('Reporte') }}</span></td>
                                    <td class="py-3 px-3 text-theme-secondary">{{ __('Diagrama horizontal clasico de pedigri con cajas y lineas conectoras') }}</td>
                                    <td class="py-3 px-3 text-theme-secondary">HTML, SVG, PDF</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 font-medium text-theme">{{ __('Presencia y Comunicacion') }}</td>
                                    <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full">{{ __('Comunicacion') }}</span></td>
                                    <td class="py-3 px-3 text-theme-secondary">{{ __('Muestra usuarios en linea y permite chat de texto entre familiares') }}</td>
                                    <td class="py-3 px-3 text-theme-secondary">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            <strong>{{ __('Reportes:') }}</strong> {{ __('Una vez habilitados, los reportes aparecen como botones en el perfil de cada persona y en la barra de herramientas del arbol genealogico.') }}
                        </p>
                    </div>
                </section>

                {{-- 2. Ciclo de vida --}}
                <section id="ciclo-vida" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">2</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Ciclo de vida de un plugin') }}</h2>
                    </div>

                    <div class="space-y-4 text-theme-secondary">
                        <p>{{ __('Cada plugin pasa por estos estados:') }}</p>

                        <div class="flex flex-wrap items-center gap-2 text-sm font-medium">
                            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">{{ __('Descubierto') }}</span>
                            <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-lg">{{ __('Instalado') }}</span>
                            <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg">{{ __('Habilitado') }}</span>
                        </div>

                        <ul class="list-disc pl-6 space-y-2 mt-4">
                            <li><strong>{{ __('Descubierto:') }}</strong> {{ __('El sistema detecta automaticamente cualquier carpeta en') }} <code class="text-xs bg-theme-secondary px-1 rounded">plugins/</code> {{ __('que contenga un archivo') }} <code class="text-xs bg-theme-secondary px-1 rounded">plugin.json</code>.</li>
                            <li><strong>{{ __('Instalar:') }}</strong> {{ __('Ejecuta las migraciones del plugin (crea tablas en la base de datos si las necesita). El plugin queda instalado pero inactivo.') }}</li>
                            <li><strong>{{ __('Habilitar:') }}</strong> {{ __('Activa el plugin. Sus rutas, vistas y funcionalidad quedan disponibles para todos los usuarios.') }}</li>
                            <li><strong>{{ __('Deshabilitar:') }}</strong> {{ __('Desactiva el plugin sin perder datos. Se puede volver a habilitar en cualquier momento.') }}</li>
                            <li><strong>{{ __('Desinstalar:') }}</strong> {{ __('Revierte las migraciones (elimina las tablas del plugin). Los archivos permanecen en disco.') }}</li>
                            <li><strong>{{ __('Eliminar:') }}</strong> {{ __('Borra los archivos del plugin del disco. Solo disponible si el plugin no esta instalado.') }}</li>
                        </ul>
                    </div>
                </section>

                {{-- 3. Configurar Pusher --}}
                <section id="configurar-pusher" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">3</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Configurar Pusher (chat en tiempo real)') }}</h2>
                    </div>

                    <div class="space-y-4 text-theme-secondary">
                        <p>{{ __('El plugin de Presencia y Comunicacion funciona con polling (cada 5 segundos consulta nuevos mensajes). Para mensajeria instantanea, puedes configurar Pusher:') }}</p>

                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                <strong>{{ __('Nota:') }}</strong> {{ __('Pusher es opcional. El chat funciona sin el, solo que los mensajes tardan hasta 5 segundos en aparecer en vez de ser instantaneos.') }}
                            </p>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Paso 1: Crear cuenta en Pusher') }}</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>{{ __('Ve a') }} <code class="text-xs bg-theme-secondary px-1 rounded">pusher.com</code> {{ __('y crea una cuenta gratuita') }}</li>
                            <li>{{ __('Crea una nueva app (Channels)') }}</li>
                            <li>{{ __('Copia las credenciales: App ID, Key, Secret y Cluster') }}</li>
                        </ol>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Paso 2: Configurar .env') }}</h3>
                        <p>{{ __('Agrega estas lineas a tu archivo') }} <code class="text-xs bg-theme-secondary px-1 rounded">.env</code>:</p>

                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>BROADCAST_DRIVER=pusher

PUSHER_APP_ID=tu-app-id
PUSHER_APP_KEY=tu-app-key
PUSHER_APP_SECRET=tu-app-secret
PUSHER_APP_CLUSTER=mt1</pre>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Paso 3: Instalar dependencias frontend') }}</h3>
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>npm install laravel-echo pusher-js</pre>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Free tier de Pusher') }}</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>200 {{ __('conexiones simultaneas') }}</li>
                            <li>100,000 {{ __('mensajes por dia') }}</li>
                            <li>{{ __('Sin limite de canales') }}</li>
                            <li>{{ __('Suficiente para la mayoria de familias') }}</li>
                        </ul>
                    </div>
                </section>

                {{-- 4. Compatibilidad --}}
                <section id="compatibilidad" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">4</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Compatibilidad') }}</h2>
                    </div>

                    <div class="space-y-4 text-theme-secondary">
                        <p>{{ __('Todos los plugins incluidos son completamente independientes entre si.') }}</p>

                        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <p class="text-sm text-green-700 dark:text-green-300">
                                <strong>{{ __('100% compatible:') }}</strong> {{ __('Puedes habilitar cualquier combinacion de plugins sin conflictos. Cada uno funciona de forma autonoma.') }}
                            </p>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-4">{{ __('Requisitos por plugin') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Reportes (4 plugins):') }}</strong> {{ __('Solo requieren PHP 8.1+ y la libreria DomPDF (ya incluida). Funcionan en cualquier hosting.') }}</li>
                            <li><strong>{{ __('Presencia y Comunicacion:') }}</strong> {{ __('Funciona sin configuracion adicional via polling. Para chat instantaneo, configurar Pusher (ver seccion 3).') }}</li>
                        </ul>

                        <h3 class="text-lg font-semibold text-theme mt-4">{{ __('Donde aparecen') }}</h3>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>{{ __('Reportes:') }}</strong> {{ __('Botones en el perfil de cada persona + barra de herramientas del arbol') }}</li>
                            <li><strong>{{ __('Presencia:') }}</strong> {{ __('Widget "Usuarios en linea" en el dashboard + indicador en la barra de navegacion + pagina de Chat') }}</li>
                        </ul>
                    </div>
                </section>

                {{-- 5. Subir y eliminar --}}
                <section id="subir-plugin" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">5</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Subir y eliminar plugins') }}</h2>
                    </div>

                    <div class="space-y-4 text-theme-secondary">
                        <h3 class="text-lg font-semibold text-theme">{{ __('Subir un plugin') }}</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>{{ __('Prepara un archivo ZIP con la carpeta del plugin') }}</li>
                            <li>{{ __('El ZIP debe contener un archivo') }} <code class="text-xs bg-theme-secondary px-1 rounded">plugin.json</code> {{ __('con un campo "slug" valido') }}</li>
                            <li>{{ __('Usa el formulario "Subir nuevo plugin" en la pagina de Plugins') }}</li>
                            <li>{{ __('El plugin aparecera como "No instalado" listo para activar') }}</li>
                        </ol>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Eliminar un plugin') }}</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>{{ __('Si el plugin esta habilitado, primero deshabilitalo') }}</li>
                            <li>{{ __('Si el plugin esta instalado, primero desinstalalo (esto elimina sus tablas)') }}</li>
                            <li>{{ __('Una vez "No instalado", aparecera el boton "Eliminar" que borra los archivos del disco') }}</li>
                        </ol>

                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mt-4">
                            <p class="text-sm text-red-700 dark:text-red-300">
                                <strong>{{ __('Atencion:') }}</strong> {{ __('Eliminar un plugin borra permanentemente sus archivos. Desinstalar un plugin borra sus tablas y datos de la base de datos. Estas acciones no se pueden deshacer.') }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- 6. Crear plugin personalizado --}}
                <section id="crear-plugin" class="bg-theme-card rounded-xl shadow-sm p-6 md:p-8 scroll-mt-20">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 bg-mf-primary text-white rounded-full flex items-center justify-center font-bold">6</span>
                        <h2 class="text-2xl font-bold text-theme">{{ __('Crear un plugin personalizado') }}</h2>
                    </div>

                    <div class="space-y-4 text-theme-secondary">
                        <h3 class="text-lg font-semibold text-theme">{{ __('Estructura de archivos') }}</h3>
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>plugins/mi-plugin/
    plugin.json              {{ __('# Manifest obligatorio') }}
    src/
        MiPluginPlugin.php   {{ __('# ServiceProvider principal') }}
        Controllers/         {{ __('# Controladores opcionales') }}
        Models/              {{ __('# Modelos opcionales') }}
    resources/views/         {{ __('# Vistas Blade opcionales') }}
    routes/
        web.php              {{ __('# Rutas opcionales') }}
    database/migrations/     {{ __('# Migraciones opcionales') }}</pre>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Ejemplo de plugin.json') }}</h3>
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>{
    "slug": "mi-plugin",
    "name": "My Plugin",
    "name_es": "Mi Plugin",
    "version": "1.0.0",
    "description": "Description in English",
    "description_es": "Descripcion en espanol",
    "author": "Tu Nombre",
    "type": "general",
    "requires": {
        "mi-familia": ">=2.1.0",
        "php": ">=8.1"
    },
    "provider": "MiPluginPlugin",
    "hooks": []
}</pre>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('ServiceProvider minimo') }}</h3>
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>&lt;?php

namespace Plugin\MiPlugin;

use App\Plugins\PluginServiceProvider;

class MiPluginPlugin extends PluginServiceProvider
{
    // {{ __('Los metodos boot(), install() y uninstall()') }}
    // {{ __('se heredan automaticamente del padre.') }}
    // {{ __('Sobreescribe hooks() para registrar') }}
    // {{ __('contenido en puntos de extension.') }}

    public function hooks(): array
    {
        return [
            // 'dashboard.widgets' => 'mi-plugin::mi-vista',
        ];
    }
}</pre>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Hooks disponibles') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-theme">
                                        <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Hook') }}</th>
                                        <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Ubicacion') }}</th>
                                        <th class="text-left py-2 px-3 text-theme font-semibold">{{ __('Datos disponibles') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-theme-light text-theme-secondary">
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">person.show.sidebar</td>
                                        <td class="py-2 px-3">{{ __('Perfil de persona (sidebar)') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">$person</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">person.show.content</td>
                                        <td class="py-2 px-3">{{ __('Perfil de persona (contenido)') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">$person</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">dashboard.widgets</td>
                                        <td class="py-2 px-3">{{ __('Dashboard principal') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">$user</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">header.menu.items</td>
                                        <td class="py-2 px-3">{{ __('Barra de navegacion') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">-</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">tree.toolbar</td>
                                        <td class="py-2 px-3">{{ __('Barra del arbol genealogico') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">$person</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-xs">admin.sidebar</td>
                                        <td class="py-2 px-3">{{ __('Panel de administracion') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-lg font-semibold text-theme mt-6">{{ __('Autoloading') }}</h3>
                        <p>{{ __('Para que el sistema encuentre las clases de tu plugin, agrega una entrada PSR-4 en') }} <code class="text-xs bg-theme-secondary px-1 rounded">composer.json</code>:</p>
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto">
                            <pre>"autoload": {
    "psr-4": {
        "Plugin\\MiPlugin\\": "plugins/mi-plugin/src/"
    }
}</pre>
                        </div>
                        <p>{{ __('Despues ejecuta:') }} <code class="text-xs bg-theme-secondary px-1 rounded">composer dump-autoload</code></p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
