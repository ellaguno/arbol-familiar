<x-app-layout>
    <x-slot name="title">{{ __('Configuracion') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Configuracion del sistema') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Ajustes generales de la plataforma') }}</p>
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
            <!-- Colores del sitio -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Colores del sitio') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-600 mb-4">{{ __('Personaliza la paleta de colores de todo el sitio. Los cambios se aplican inmediatamente.') }}</p>

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
                                               class="h-10 w-14 rounded border border-gray-300 cursor-pointer"
                                               onchange="document.getElementById('hex_{{ $key }}').value = this.value; updatePreview();">
                                        <input type="text"
                                               id="hex_{{ $key }}"
                                               value="{{ $colors[$key] ?? '#3b82f6' }}"
                                               class="form-input w-28 font-mono text-sm"
                                               onchange="document.getElementById('color_{{ $key }}').value = this.value; document.getElementById('color_{{ $key }}').name = '{{ $key }}'; updatePreview();"
                                               pattern="^#[0-9a-fA-F]{6}$">
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $colorDescriptions[$key] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <!-- Preview -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('Vista previa') }}</h4>
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

            <!-- Informacion del sistema -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Informacion del sistema') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="grid md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Version de Laravel') }}</dt>
                            <dd class="font-medium">{{ app()->version() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Version de PHP') }}</dt>
                            <dd class="font-medium">{{ PHP_VERSION }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Entorno') }}</dt>
                            <dd class="font-medium">{{ config('app.env') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Modo debug') }}</dt>
                            <dd>
                                @if(config('app.debug'))
                                    <span class="text-yellow-600 font-medium">{{ __('Activado') }}</span>
                                @else
                                    <span class="text-green-600 font-medium">{{ __('Desactivado') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Zona horaria') }}</dt>
                            <dd class="font-medium">{{ config('app.timezone') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Idioma') }}</dt>
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
                            <dt class="text-sm text-gray-500">{{ __('Driver de archivos') }}</dt>
                            <dd class="font-medium">{{ config('filesystems.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Driver de cache') }}</dt>
                            <dd class="font-medium">{{ config('cache.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Driver de sesion') }}</dt>
                            <dd class="font-medium">{{ config('session.driver') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Driver de cola') }}</dt>
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
                            <dt class="text-sm text-gray-500">{{ __('Driver') }}</dt>
                            <dd class="font-medium">{{ config('database.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Nombre') }}</dt>
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
                            <dt class="text-sm text-gray-500">{{ __('Driver') }}</dt>
                            <dd class="font-medium">{{ config('mail.default') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Servidor SMTP') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.host') ?: __('No configurado') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Puerto') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.port') ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Encriptacion') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.encryption') ?: __('Ninguna') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Usuario') }}</dt>
                            <dd class="font-medium">{{ config('mail.mailers.smtp.username') ?: __('No configurado') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Remitente') }}</dt>
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

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">{{ __('Configuracion SMTP') }}</h4>
                        <p class="text-sm text-gray-600 mb-3">
                            {{ __('Para enviar correos reales, edita el archivo') }} <code class="bg-gray-200 px-1 rounded">.env</code> {{ __('con los siguientes parametros:') }}
                        </p>
                        <pre class="text-xs bg-gray-800 text-green-400 p-3 rounded overflow-x-auto">MAIL_MAILER=smtp
MAIL_HOST=smtp.tuservidor.com
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"</pre>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ __('Ejemplos de servidores SMTP: Gmail (smtp.gmail.com:587), SendGrid, Mailgun, Amazon SES') }}
                        </p>
                    </div>

                    @if(config('mail.default') !== 'log')
                        <div class="mt-4">
                            <form action="{{ route('admin.settings.test-mail') }}" method="POST" class="flex items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label for="test_email" class="block text-sm text-gray-600 mb-1">{{ __('Enviar correo de prueba') }}</label>
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
                                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Diagnostico de Correo') }}</h3>
                                        <button onclick="closeDiagnostic()" class="text-gray-400 hover:text-gray-600">
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
                                            <span class="ml-3 text-gray-600">{{ __('Ejecutando diagnostico...') }}</span>
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
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ __('Limpiar cache') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Elimina la cache de la aplicacion, vistas, rutas y configuracion') }}</p>
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

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ __('Optimizar vistas') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Compila y cachea las vistas Blade para mejor rendimiento') }}</p>
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
                                <span class="text-gray-600">{{ __('Archivos multimedia') }}</span>
                                <span class="text-gray-900 font-medium">
                                    {{ number_format($mediaSize / 1024 / 1024, 2) }} MB
                                    ({{ number_format($mediaCount) }} {{ __('archivos') }})
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
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
