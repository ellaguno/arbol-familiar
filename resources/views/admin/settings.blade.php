<x-app-layout>
    <x-slot name="title">{{ __('Configuracion') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Configuracion del sistema') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Ajustes generales de la plataforma') }}</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Tema y fondo -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ __('Tema y fondo') }}</h2>
                    @php
                        $currentTheme = \App\Models\SiteSetting::get('colors', 'theme_mode', 'dark');
                        $currentBgColor = \App\Models\SiteSetting::get('colors', 'bg_color', '');
                        $currentBgImage = \App\Models\SiteSetting::get('colors', 'bg_image', '');
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $currentTheme === 'dark' ? 'bg-gray-800 text-gray-200' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $currentTheme === 'dark' ? __('Oscuro') : __('Claro') }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Configura el modo de color y fondo del sitio. Los usuarios pueden sobreescribir esta preferencia desde su perfil.') }}</p>

                    <form action="{{ route('admin.settings.theme') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Modo de tema -->
                        <div class="mb-6">
                            <label class="form-label">{{ __('Modo de tema por defecto') }}</label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-3 rounded-lg border-2 transition-colors {{ $currentTheme === 'light' ? 'border-blue-500 bg-blue-50' : 'border-theme' }}">
                                    <input type="radio" name="theme_mode" value="light" class="form-radio" {{ $currentTheme === 'light' ? 'checked' : '' }}>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            <span class="font-medium">{{ __('Claro') }}</span>
                                        </div>
                                        <p class="text-xs text-theme-muted mt-1">{{ __('Fondos blancos, texto oscuro') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-3 rounded-lg border-2 transition-colors {{ $currentTheme === 'dark' ? 'border-blue-500 bg-blue-50' : 'border-theme' }}">
                                    <input type="radio" name="theme_mode" value="dark" class="form-radio" {{ $currentTheme === 'dark' ? 'checked' : '' }}>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                            <span class="font-medium">{{ __('Oscuro') }}</span>
                                        </div>
                                        <p class="text-xs text-theme-muted mt-1">{{ __('Fondos oscuros, texto claro') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Color de fondo -->
                        <div class="mb-6">
                            <label for="bg_color" class="form-label">{{ __('Color de fondo del contenido') }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color"
                                       name="bg_color"
                                       id="bg_color"
                                       value="{{ $currentBgColor ?: '#f3f4f6' }}"
                                       class="h-10 w-14 rounded border border-theme cursor-pointer">
                                <input type="text"
                                       id="bg_color_hex"
                                       value="{{ $currentBgColor }}"
                                       class="form-input w-28 font-mono text-sm"
                                       placeholder="#f3f4f6"
                                       onchange="document.getElementById('bg_color').value = this.value || '#f3f4f6';">
                                <button type="button" onclick="document.getElementById('bg_color').value='#f3f4f6'; document.getElementById('bg_color_hex').value='';" class="text-sm text-theme-muted hover:text-theme-secondary">
                                    {{ __('Limpiar') }}
                                </button>
                            </div>
                            <p class="text-xs text-theme-muted mt-1">{{ __('Deja vacio para usar el color por defecto del tema. Solo aplica al area de contenido.') }}</p>
                        </div>

                        <!-- Imagen de fondo -->
                        <div class="mb-6">
                            <label for="bg_image" class="form-label">{{ __('Imagen de fondo del contenido') }}</label>
                            @if($currentBgImage)
                                <div class="mb-3 p-3 bg-theme-secondary rounded-lg">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset($currentBgImage) }}" alt="Background" class="h-20 w-32 object-cover rounded">
                                        <div>
                                            <p class="text-sm text-theme-secondary">{{ __('Imagen actual') }}</p>
                                            <label class="flex items-center gap-2 mt-2 text-sm text-red-600 cursor-pointer">
                                                <input type="checkbox" name="remove_bg_image" value="1" class="form-checkbox">
                                                {{ __('Eliminar imagen') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <input type="file"
                                   name="bg_image"
                                   id="bg_image"
                                   accept="image/*"
                                   class="form-input">
                            <p class="text-xs text-theme-muted mt-1">{{ __('JPG, PNG o WebP. Max 5MB. Solo aplica al area de contenido, no al header ni footer.') }}</p>
                        </div>

                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Guardar tema') }}
                        </button>
                    </form>

                    <script>
                    document.getElementById('bg_color').addEventListener('change', function() {
                        document.getElementById('bg_color_hex').value = this.value === '#f3f4f6' ? '' : this.value;
                    });
                    </script>
                </div>
            </div>

            <!-- Menu de navegacion -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Menu de navegacion') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Controla que opciones aparecen en el menu principal. Por defecto estas opciones estan ocultas.') }}</p>

                    <form action="{{ route('admin.settings.navigation') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @php
                            $showResearch = \App\Models\SiteSetting::get('navigation', 'show_research', '0');
                            $showHelp = \App\Models\SiteSetting::get('navigation', 'show_help', '0');
                        @endphp

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_research" value="1"
                                       class="form-checkbox"
                                       {{ $showResearch ? 'checked' : '' }}>
                                <div>
                                    <span class="font-medium">{{ __('Mostrar "Investigacion"') }}</span>
                                    <p class="text-xs text-theme-muted">{{ __('Muestra la opcion de Investigacion en el menu (actualmente marcada como "Proximamente").') }}</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="show_help" value="1"
                                       class="form-checkbox"
                                       {{ $showHelp ? 'checked' : '' }}>
                                <div>
                                    <span class="font-medium">{{ __('Mostrar "Como usar Mi Familia?"') }}</span>
                                    <p class="text-xs text-theme-muted">{{ __('Muestra el enlace a la pagina de ayuda en el menu principal.') }}</p>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary mt-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Guardar opciones del menu') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tipografia del sitio -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Tipografia del sitio') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Selecciona la tipografia que se usara en todo el sitio.') }}</p>

                    <form action="{{ route('admin.settings.colors') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @php
                            $currentFont = \App\Models\SiteSetting::get('colors', 'font', 'Ubuntu');
                            $availableFonts = \App\Services\SiteSettingsService::AVAILABLE_FONTS;
                        @endphp

                        <div class="max-w-md">
                            <label for="font" class="form-label">{{ __('Tipografia') }}</label>
                            <select name="font" id="font" class="form-input" onchange="previewFont(this.value)">
                                @foreach($availableFonts as $fontName => $fontData)
                                    <option value="{{ $fontName }}" {{ $currentFont === $fontName ? 'selected' : '' }}>{{ $fontName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Preview -->
                        <div class="mt-4 p-4 bg-theme-secondary rounded-lg">
                            <h4 class="text-sm font-medium text-theme-secondary mb-3">{{ __('Vista previa') }}</h4>
                            <div id="font-preview" style="font-family: '{{ $currentFont }}', sans-serif;">
                                <p class="text-2xl font-bold mb-1">{{ config('app.name') }}</p>
                                <p class="text-base mb-1">{{ __('El veloz murcielago hindu comia feliz cardillo y kiwi.') }}</p>
                                <p class="text-sm text-theme-muted">ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz 0123456789</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Guardar tipografia') }}
                            </button>
                        </div>
                    </form>

                    <script>
                    const fontSlugs = @json(collect($availableFonts)->map(fn($f) => $f['slug'].':'.$f['weights'])->toArray());

                    function previewFont(fontName) {
                        const data = fontSlugs[fontName];
                        if (!data) return;

                        // Load font from Bunny Fonts
                        const linkId = 'preview-font-link';
                        let link = document.getElementById(linkId);
                        if (!link) {
                            link = document.createElement('link');
                            link.id = linkId;
                            link.rel = 'stylesheet';
                            document.head.appendChild(link);
                        }
                        link.href = 'https://fonts.bunny.net/css?family=' + data + '&display=swap';

                        // Update preview
                        document.getElementById('font-preview').style.fontFamily = "'" + fontName + "', sans-serif";
                    }
                    </script>
                </div>
            </div>

            <!-- Colores del sitio -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Colores del sitio') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Personaliza la paleta de colores de todo el sitio. Los cambios se aplican inmediatamente.') }}</p>

                    <form action="{{ route('admin.settings.colors') }}" method="POST" id="colors-form">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $colors = \App\Models\SiteSetting::colors();
                                $colorLabels = [
                                    'primary' => __('Primario'),
                                    'secondary' => __('Secundario'),
                                    'accent' => __('Acento'),
                                    'light' => __('Claro'),
                                    'dark' => __('Oscuro'),
                                ];
                                $colorDescriptions = [
                                    'primary' => __('Botones principales, enlaces, encabezados'),
                                    'secondary' => __('Bordes activos, enlaces secundarios'),
                                    'accent' => __('Botones de accion, llamadas a la accion'),
                                    'light' => __('Fondos suaves, hover de elementos'),
                                    'dark' => __('Hover de botones, texto oscuro'),
                                ];
                            @endphp

                            @foreach($colorLabels as $key => $label)
                                <div class="space-y-2">
                                    <label for="color_{{ $key }}" class="form-label">{{ $label }}</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color"
                                               name="{{ $key }}"
                                               id="color_{{ $key }}"
                                               value="{{ $colors[$key] ?? '#3b82f6' }}"
                                               class="h-10 w-14 rounded border border-theme cursor-pointer"
                                               onchange="document.getElementById('hex_{{ $key }}').value = this.value; updatePreview();">
                                        <input type="text"
                                               id="hex_{{ $key }}"
                                               value="{{ $colors[$key] ?? '#3b82f6' }}"
                                               class="form-input w-28 font-mono text-sm"
                                               onchange="document.getElementById('color_{{ $key }}').value = this.value; document.getElementById('color_{{ $key }}').name = '{{ $key }}'; updatePreview();"
                                               pattern="^#[0-9a-fA-F]{6}$">
                                    </div>
                                    <p class="text-xs text-theme-muted">{{ $colorDescriptions[$key] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <!-- Preview -->
                        <div class="mt-6 p-4 bg-theme-secondary rounded-lg">
                            <h4 class="text-sm font-medium text-theme-secondary mb-3">{{ __('Vista previa') }}</h4>
                            <div class="flex flex-wrap gap-3" id="color-preview">
                                <button type="button" id="preview-primary" class="px-4 py-2 text-white rounded-lg text-sm" style="background-color: {{ $colors['primary'] }}">{{ __('Boton primario') }}</button>
                                <button type="button" id="preview-secondary" class="px-4 py-2 text-white rounded-lg text-sm" style="background-color: {{ $colors['secondary'] }}">{{ __('Boton secundario') }}</button>
                                <button type="button" id="preview-accent" class="px-4 py-2 text-white rounded-lg text-sm" style="background-color: {{ $colors['accent'] }}">{{ __('Boton acento') }}</button>
                                <button type="button" id="preview-outline" class="px-4 py-2 rounded-lg text-sm border-2" style="color: {{ $colors['secondary'] }}; border-color: {{ $colors['secondary'] }}">{{ __('Boton outline') }}</button>
                                <span id="preview-light" class="px-4 py-2 rounded-lg text-sm" style="background-color: {{ $colors['light'] }}; color: {{ $colors['primary'] }}">{{ __('Fondo claro') }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-4">
                            <button type="submit" class="btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Guardar colores') }}
                            </button>
                            <button type="button" onclick="resetColors()" class="btn-outline text-sm">
                                {{ __('Restablecer valores por defecto') }}
                            </button>
                        </div>
                    </form>

                    <script>
                    function updatePreview() {
                        const p = document.getElementById('color_primary').value;
                        const s = document.getElementById('color_secondary').value;
                        const a = document.getElementById('color_accent').value;
                        const l = document.getElementById('color_light').value;
                        const d = document.getElementById('color_dark').value;

                        document.getElementById('preview-primary').style.backgroundColor = p;
                        document.getElementById('preview-secondary').style.backgroundColor = s;
                        document.getElementById('preview-accent').style.backgroundColor = a;
                        document.getElementById('preview-outline').style.color = s;
                        document.getElementById('preview-outline').style.borderColor = s;
                        document.getElementById('preview-light').style.backgroundColor = l;
                        document.getElementById('preview-light').style.color = p;

                        // Update CSS variables live
                        document.documentElement.style.setProperty('--mf-primary', p);
                        document.documentElement.style.setProperty('--mf-secondary', s);
                        document.documentElement.style.setProperty('--mf-accent', a);
                        document.documentElement.style.setProperty('--mf-light', l);
                        document.documentElement.style.setProperty('--mf-dark', d);
                    }

                    function resetColors() {
                        const defaults = {
                            primary: '#3b82f6',
                            secondary: '#2563eb',
                            accent: '#f59e0b',
                            light: '#dbeafe',
                            dark: '#1d4ed8'
                        };
                        for (const [key, val] of Object.entries(defaults)) {
                            document.getElementById('color_' + key).value = val;
                            document.getElementById('hex_' + key).value = val;
                        }
                        updatePreview();
                    }
                    </script>
                </div>
            </div>

            <!-- Herencia cultural -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ __('Herencia cultural') }}</h2>
                    @php
                        $hEnabled = \App\Models\SiteSetting::get('heritage', 'heritage_enabled', '0');
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $hEnabled ? 'bg-green-100 text-green-700' : 'bg-theme-secondary text-theme-muted' }}">
                        {{ $hEnabled ? __('Habilitado') : __('Deshabilitado') }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Permite a los usuarios registrar su herencia cultural, region de origen y datos de migracion familiar.') }}</p>

                    <form action="{{ route('admin.settings.heritage') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @php
                            $hLabel = \App\Models\SiteSetting::get('heritage', 'heritage_label', 'Herencia cultural');
                            $hRegionsJson = \App\Models\SiteSetting::get('heritage', 'heritage_regions', '{}');
                            $hDecadesJson = \App\Models\SiteSetting::get('heritage', 'heritage_decades', '{}');
                            $hRegions = json_decode($hRegionsJson, true) ?: [];
                            $hDecades = json_decode($hDecadesJson, true) ?: [];
                            $hRegionsText = collect($hRegions)->map(fn($v, $k) => "$k|$v")->implode("\n");
                            $hDecadesText = collect($hDecades)->map(fn($v, $k) => "$k|$v")->implode("\n");
                        @endphp

                        <!-- Toggle -->
                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="heritage_enabled" value="1"
                                       class="form-checkbox"
                                       {{ $hEnabled ? 'checked' : '' }}>
                                <span class="font-medium">{{ __('Habilitar herencia cultural') }}</span>
                            </label>
                            <p class="text-xs text-theme-muted mt-1">{{ __('Cuando esta deshabilitado, la seccion de herencia no aparece en ningun formulario ni perfil.') }}</p>
                        </div>

                        <!-- Label -->
                        <div class="mb-6">
                            <label for="heritage_label" class="form-label">{{ __('Nombre de la seccion') }}</label>
                            <input type="text" name="heritage_label" id="heritage_label"
                                   value="{{ $hLabel }}"
                                   class="form-input max-w-md"
                                   placeholder="Herencia cultural">
                            <p class="text-xs text-theme-muted mt-1">{{ __('Este texto se muestra como titulo de la seccion en formularios y perfiles.') }}</p>
                        </div>

                        <!-- Regions -->
                        <div class="mb-6">
                            <label for="heritage_regions" class="form-label">{{ __('Regiones de origen') }}</label>
                            <textarea name="heritage_regions" id="heritage_regions"
                                      rows="6"
                                      class="form-input font-mono text-sm resize-y max-w-lg">{{ $hRegionsText }}</textarea>
                            <p class="text-xs text-theme-muted mt-1">{{ __('Una region por linea, formato: clave|Nombre. Ejemplo: dalmacia|Dalmacia') }}</p>
                        </div>

                        <!-- Decades -->
                        <div class="mb-6">
                            <label for="heritage_decades" class="form-label">{{ __('Decadas de migracion') }}</label>
                            <textarea name="heritage_decades" id="heritage_decades"
                                      rows="6"
                                      class="form-input font-mono text-sm resize-y max-w-lg">{{ $hDecadesText }}</textarea>
                            <p class="text-xs text-theme-muted mt-1">{{ __('Una decada por linea, formato: clave|Nombre. Ejemplo: 1900-1910|1900 - 1910') }}</p>
                        </div>

                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Guardar configuracion de herencia') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Informacion del sistema -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Informacion del sistema') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="grid md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Version de Laravel') }}</dt>
                            <dd class="font-medium">{{ app()->version() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Version de PHP') }}</dt>
                            <dd class="font-medium">{{ PHP_VERSION }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Entorno') }}</dt>
                            <dd class="font-medium">{{ config('app.env') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Modo debug') }}</dt>
                            <dd>
                                @if(config('app.debug'))
                                    <span class="text-yellow-600 font-medium">{{ __('Activado') }}</span>
                                @else
                                    <span class="text-green-600 font-medium">{{ __('Desactivado') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Zona horaria') }}</dt>
                            <dd class="font-medium">{{ config('app.timezone') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Idioma') }}</dt>
                            <dd class="font-medium">{{ config('app.locale') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Almacenamiento -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Almacenamiento') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="grid md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver de archivos') }}</dt>
                            <dd class="font-medium">{{ config('filesystems.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver de cache') }}</dt>
                            <dd class="font-medium">{{ config('cache.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver de sesion') }}</dt>
                            <dd class="font-medium">{{ config('session.driver') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver de cola') }}</dt>
                            <dd class="font-medium">{{ config('queue.default') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Base de datos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Base de datos') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="grid md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver') }}</dt>
                            <dd class="font-medium">{{ config('database.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Nombre') }}</dt>
                            <dd class="font-medium">{{ config('database.connections.' . config('database.default') . '.database') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Configuracion de correo -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-lg font-semibold">{{ __('Correo electronico') }}</h2>
                    @if(config('mail.default') === 'log')
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">{{ __('Modo desarrollo') }}</span>
                    @elseif(config('mail.mailers.smtp.host') && config('mail.mailers.smtp.host') !== 'smtp.example.com')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">{{ __('Configurado') }}</span>
                    @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">{{ __('No configurado') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Driver') }}</dt>
                            <dd class="font-medium">{{ config('mail.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Servidor SMTP') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.host') ?: __('No configurado') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Puerto') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.port') ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Encriptacion') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.encryption') ?: __('Ninguna') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Usuario') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.username') ?: __('No configurado') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-theme-muted">{{ __('Remitente') }}</dt>
                            <dd class="font-medium">{{ config('mail.from.address') }}</dd>
                        </div>
                    </dl>

                    @if(config('mail.default') === 'log')
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-yellow-800">{{ __('Modo desarrollo activo') }}</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        {{ __('Los correos no se envian, se guardan en') }} <code class="bg-yellow-100 px-1 rounded">storage/logs/laravel.log</code>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 p-4 bg-theme-secondary rounded-lg">
                        <h4 class="font-medium text-theme mb-2">{{ __('Configuracion SMTP') }}</h4>
                        <p class="text-sm text-theme-secondary mb-3">
                            {{ __('Para enviar correos reales, edita el archivo') }} <code class="bg-theme-secondary px-1 rounded">.env</code> {{ __('con los siguientes parametros:') }}
                        </p>
                        <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto">MAIL_MAILER=smtp
MAIL_HOST=smtp.tuservidor.com
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"</pre>
                        <p class="text-xs text-theme-muted mt-2">
                            {{ __('Ejemplos de servidores SMTP: Gmail (smtp.gmail.com:587), SendGrid, Mailgun, Amazon SES') }}
                        </p>
                    </div>

                    @if(config('mail.default') !== 'log')
                        <div class="mt-4">
                            <form action="{{ route('admin.settings.test-mail') }}" method="POST" class="flex items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label for="test_email" class="block text-sm text-theme-secondary mb-1">{{ __('Enviar correo de prueba') }}</label>
                                    <input type="email" name="test_email" id="test_email"
                                           value="{{ auth()->user()->email }}"
                                           class="form-input"
                                           placeholder="correo@ejemplo.com">
                                </div>
                                <button type="submit" class="btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Probar') }}
                                </button>
                                <button type="button" onclick="runMailDiagnostic()" class="btn-outline">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    {{ __('Diagnostico') }}
                                </button>
                            </form>
                        </div>

                        <!-- Modal de diagnostico -->
                        <div id="diagnosticModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold text-theme">{{ __('Diagnostico de Correo') }}</h3>
                                        <button onclick="closeDiagnostic()" class="text-theme-muted hover:text-theme-secondary">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="diagnosticContent">
                                        <div class="flex items-center justify-center py-8">
                                            <svg class="animate-spin h-8 w-8 text-mf-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="ml-3 text-theme-secondary">{{ __('Ejecutando diagnostico...') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        function runMailDiagnostic() {
                            const modal = document.getElementById('diagnosticModal');
                            const content = document.getElementById('diagnosticContent');

                            modal.classList.remove('hidden');
                            modal.classList.add('flex');

                            content.innerHTML = `
                                <div class="flex items-center justify-center py-8">
                                    <svg class="animate-spin h-8 w-8 text-mf-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="ml-3 text-gray-600">{{ __('Ejecutando diagnostico...') }}</span>
                                </div>
                            `;

                            fetch('{{ route('admin.settings.mail-diagnostic') }}')
                                .then(response => response.json())
                                .then(data => {
                                    content.innerHTML = renderDiagnostic(data);
                                })
                                .catch(error => {
                                    content.innerHTML = `
                                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                            <p class="text-red-700">Error al ejecutar diagnostico: ${error.message}</p>
                                        </div>
                                    `;
                                });
                        }

                        function closeDiagnostic() {
                            const modal = document.getElementById('diagnosticModal');
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }

                        function renderDiagnostic(data) {
                            let html = '';

                            // Resumen
                            const summaryColor = data.summary.percentage >= 80 ? 'green' : (data.summary.percentage >= 50 ? 'yellow' : 'red');
                            html += `
                                <div class="mb-6 p-4 bg-${summaryColor}-50 border border-${summaryColor}-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-${summaryColor}-800">{{ __('Puntuacion') }}: ${data.summary.percentage}%</span>
                                        <span class="text-sm text-${summaryColor}-600">${data.summary.passed}/${data.summary.total} {{ __('verificaciones pasadas') }}</span>
                                    </div>
                                    <div class="mt-2 w-full bg-${summaryColor}-200 rounded-full h-2">
                                        <div class="bg-${summaryColor}-500 h-2 rounded-full" style="width: ${data.summary.percentage}%"></div>
                                    </div>
                                </div>
                            `;

                            // Configuracion actual
                            html += `
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('Configuracion Actual') }}</h4>
                                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                        <dl class="grid grid-cols-2 gap-2">
                                            <dt class="text-gray-500">Mailer:</dt><dd class="font-mono">${data.config.mailer || '-'}</dd>
                                            <dt class="text-gray-500">Host:</dt><dd class="font-mono">${data.config.host || '-'}</dd>
                                            <dt class="text-gray-500">Puerto:</dt><dd class="font-mono">${data.config.port || '-'}</dd>
                                            <dt class="text-gray-500">Encriptacion:</dt><dd class="font-mono">${data.config.encryption || '-'}</dd>
                                            <dt class="text-gray-500">Usuario:</dt><dd class="font-mono">${data.config.username || '-'}</dd>
                                            <dt class="text-gray-500">EHLO Domain:</dt><dd class="font-mono ${!data.config.ehlo_domain ? 'text-red-600 font-bold' : ''}">${data.config.ehlo_domain || '{{ __('NO CONFIGURADO') }}'}</dd>
                                            <dt class="text-gray-500">Remitente:</dt><dd class="font-mono">${data.config.from_address || '-'}</dd>
                                        </dl>
                                    </div>
                                </div>
                            `;

                            // Verificaciones
                            html += `<div class="mb-4"><h4 class="font-medium text-gray-900 mb-2">{{ __('Verificaciones') }}</h4><div class="space-y-2">`;
                            for (const [key, check] of Object.entries(data.checks)) {
                                const icon = check.status
                                    ? '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                                    : '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                                html += `
                                    <div class="flex items-start gap-2 p-2 rounded ${check.status ? 'bg-green-50' : 'bg-red-50'}">
                                        ${icon}
                                        <div class="flex-1">
                                            <p class="font-medium text-sm">${check.name}</p>
                                            <p class="text-xs text-gray-600">${check.message}</p>
                                        </div>
                                    </div>
                                `;
                            }
                            html += `</div></div>`;

                            // Test SMTP
                            if (data.smtp_test) {
                                const smtpIcon = data.smtp_test.status
                                    ? '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                                    : '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                                html += `
                                    <div class="mb-4">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ __('Conexion SMTP') }}</h4>
                                        <div class="flex items-start gap-2 p-2 rounded ${data.smtp_test.status ? 'bg-green-50' : 'bg-red-50'}">
                                            ${smtpIcon}
                                            <p class="text-sm">${data.smtp_test.message}</p>
                                        </div>
                                    </div>
                                `;
                            }

                            // DNS
                            if (Object.keys(data.dns).length > 0) {
                                html += `<div class="mb-4"><h4 class="font-medium text-gray-900 mb-2">{{ __('Registros DNS') }}</h4><div class="space-y-2">`;
                                for (const [key, record] of Object.entries(data.dns)) {
                                    const icon = record.status
                                        ? '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                                        : '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                                    html += `
                                        <div class="flex items-start gap-2 p-2 rounded ${record.status ? 'bg-green-50' : 'bg-yellow-50'}">
                                            ${icon}
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">${record.name}</p>
                                                <p class="text-xs text-gray-600 break-all">${record.message}</p>
                                            </div>
                                        </div>
                                    `;
                                }
                                html += `</div></div>`;
                            }

                            // Recomendaciones si EHLO no esta configurado
                            if (!data.config.ehlo_domain) {
                                html += `
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <h4 class="font-medium text-red-800 mb-2">{{ __('Accion Requerida') }}</h4>
                                        <p class="text-sm text-red-700 mb-2">{{ __('Agrega la siguiente linea a tu archivo .env:') }}</p>
                                        <code class="block bg-red-100 p-2 rounded text-sm font-mono">MAIL_EHLO_DOMAIN={{ config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'tudominio.com' }}</code>
                                        <p class="text-xs text-red-600 mt-2">{{ __('Luego ejecuta: php artisan config:clear') }}</p>
                                    </div>
                                `;
                            }

                            return html;
                        }

                        // Cerrar modal al hacer clic fuera
                        document.getElementById('diagnosticModal').addEventListener('click', function(e) {
                            if (e.target === this) {
                                closeDiagnostic();
                            }
                        });
                        </script>
                    @endif
                </div>
            </div>

            <!-- Mantenimiento -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Mantenimiento') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-center justify-between p-4 bg-theme-secondary rounded-lg">
                        <div>
                            <h3 class="font-medium text-theme">{{ __('Limpiar cache') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Elimina la cache de la aplicacion, vistas, rutas y configuracion') }}</p>
                        </div>
                        <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                {{ __('Limpiar') }}
                            </button>
                        </form>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-theme-secondary rounded-lg">
                        <div>
                            <h3 class="font-medium text-theme">{{ __('Optimizar vistas') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Compila y cachea las vistas Blade para mejor rendimiento') }}</p>
                        </div>
                        <form action="{{ route('admin.settings.optimize') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                {{ __('Optimizar') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Estadisticas de almacenamiento -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Uso de almacenamiento') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @php
                            $mediaSize = \App\Models\Media::sum('file_size') ?? 0;
                            $mediaCount = \App\Models\Media::count();
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-theme-secondary">{{ __('Archivos multimedia') }}</span>
                                <span class="text-theme font-medium">
                                    {{ number_format($mediaSize / 1024 / 1024, 2) }} MB
                                    ({{ number_format($mediaCount) }} {{ __('archivos') }})
                                </span>
                            </div>
                            <div class="w-full bg-theme-secondary rounded-full h-2">
                                @php
                                    $maxSize = 1024 * 1024 * 1024; // 1GB
                                    $percentage = min(($mediaSize / $maxSize) * 100, 100);
                                @endphp
                                <div class="bg-mf-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
