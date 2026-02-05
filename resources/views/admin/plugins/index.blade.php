<x-app-layout>
    <x-slot name="title">{{ __('Plugins') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Plugins') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Gestiona los plugins instalados en el sistema') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.plugins.manual') }}" class="btn-outline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    {{ __('Manual') }}
                </a>
                <a href="{{ route('admin.index') }}" class="btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Subir plugin --}}
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-4">
                    @csrf
                    <div class="flex-1">
                        <label for="plugin_zip" class="block text-sm font-medium text-theme mb-1">{{ __('Subir nuevo plugin') }}</label>
                        <input type="file" name="plugin_zip" id="plugin_zip" accept=".zip"
                               class="input-field text-sm"
                               required>
                        <p class="text-xs text-theme-muted mt-1">{{ __('Archivo ZIP con plugin.json. Maximo 10MB.') }}</p>
                    </div>
                    <button type="submit" class="btn-primary self-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('Subir') }}
                    </button>
                </form>
                @error('plugin_zip')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if(empty($pluginList))
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-theme-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-theme mb-2">{{ __('No hay plugins disponibles') }}</h3>
                    <p class="text-theme-muted">{{ __('Coloca plugins en el directorio') }} <code class="bg-theme-secondary px-2 py-1 rounded text-sm">plugins/</code> {{ __('o sube un archivo ZIP.') }}</p>
                </div>
            </div>
        @else
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($pluginList as $item)
                    @php
                        $record = $item['record'];
                        $manifest = $item['manifest'];
                        $available = $item['available'];
                        $type = $item['type'];
                    @endphp
                    <div class="card">
                        <div class="card-body">
                            {{-- Header: nombre, version, tipo --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-lg font-semibold text-theme">{{ $item['name_localized'] }}</h3>
                                        <span class="px-2 py-0.5 text-xs font-medium bg-theme-secondary text-theme-secondary rounded-full">
                                            v{{ $record->version }}
                                        </span>
                                    </div>
                                    @if($manifest && isset($manifest['author']))
                                        <p class="text-sm text-theme-muted mt-0.5">{{ __('por') }} {{ $manifest['author'] }}</p>
                                    @elseif($record->author)
                                        <p class="text-sm text-theme-muted mt-0.5">{{ __('por') }} {{ $record->author }}</p>
                                    @endif
                                </div>

                                {{-- Type badge --}}
                                @if($type === 'report')
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full flex-shrink-0">
                                        {{ __('Reporte') }}
                                    </span>
                                @elseif($type === 'communication')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full flex-shrink-0">
                                        {{ __('Comunicacion') }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-full flex-shrink-0">
                                        {{ __('General') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Description --}}
                            <p class="text-sm text-theme-secondary mb-4">{{ $item['description_localized'] }}</p>

                            {{-- Status + Actions --}}
                            <div class="flex items-center justify-between pt-3 border-t border-theme">
                                {{-- Status badge --}}
                                <div>
                                    @if(!$available)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            {{ __('Archivos no encontrados') }}
                                        </span>
                                    @elseif($record->hasError())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            {{ __('Error') }}
                                        </span>
                                    @elseif($record->isEnabled())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ __('Activo') }}
                                        </span>
                                    @elseif($record->isInstalled())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                            {{ __('Instalado') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                            {{ __('No instalado') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex items-center gap-2">
                                    @if(!$available)
                                        {{-- Solo eliminar registro huerfano --}}
                                        <form action="{{ route('admin.plugins.delete', $record->slug) }}" method="POST"
                                              onsubmit="return confirm('{{ __('Eliminar este registro?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-outline text-sm px-3 py-1.5 text-red-600 border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                {{ __('Eliminar') }}
                                            </button>
                                        </form>
                                    @elseif(!$record->isInstalled())
                                        <form action="{{ route('admin.plugins.install', $record->slug) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-primary text-sm px-3 py-1.5">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                {{ __('Instalar') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.plugins.delete', $record->slug) }}" method="POST"
                                              onsubmit="return confirm('{{ __('Esto eliminara todos los archivos del plugin. Continuar?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-outline text-sm px-3 py-1.5 text-red-600 border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                {{ __('Eliminar') }}
                                            </button>
                                        </form>
                                    @elseif($record->isEnabled())
                                        <form action="{{ route('admin.plugins.toggle', $record->slug) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-outline text-sm px-3 py-1.5">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                {{ __('Deshabilitar') }}
                                            </button>
                                        </form>
                                    @else
                                        {{-- Installed but disabled --}}
                                        <form action="{{ route('admin.plugins.toggle', $record->slug) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-primary text-sm px-3 py-1.5">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ __('Habilitar') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.plugins.uninstall', $record->slug) }}" method="POST"
                                              onsubmit="return confirm('{{ __('Estas seguro de desinstalar este plugin? Se eliminaran sus datos.') }}')">
                                            @csrf
                                            <button type="submit" class="btn-outline text-sm px-3 py-1.5 text-red-600 border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                {{ __('Desinstalar') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
