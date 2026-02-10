<x-app-layout>
    <x-slot name="title">{{ __('Cintillo de Fotos') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center"><a href="{{ route('admin.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Admin') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Cintillo de Fotos') }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-2xl font-bold text-theme mb-2">{{ __('Configuracion del Cintillo de Fotos') }}</h1>
        <p class="text-theme-secondary mb-6">{{ __('Configura el banner animado de fotos que aparece en el dashboard.') }}</p>

        @if(session('success'))
            <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
        @endif

        @if($errors->any())
            <x-alert type="error" class="mb-6">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form action="{{ route('admin.photo-banner.settings.update') }}" method="POST" class="space-y-8">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Apariencia') }}</h2>
                </div>
                <div class="card-body space-y-6">
                    {{-- Altura del banner --}}
                    <div>
                        <label for="banner_height" class="form-label">{{ __('Altura del banner') }} (px)</label>
                        <div class="flex items-center gap-4">
                            <input type="range" id="banner_height" name="banner_height"
                                   min="60" max="200" step="10"
                                   value="{{ $settings['banner_height'] ?? 120 }}"
                                   class="flex-1"
                                   oninput="document.getElementById('height_value').textContent = this.value + 'px'">
                            <span id="height_value" class="text-sm font-medium text-theme w-16 text-right">{{ $settings['banner_height'] ?? 120 }}px</span>
                        </div>
                        <p class="text-xs text-theme-muted mt-1">{{ __('Altura de las imagenes en el cintillo. Rango: 60-200px.') }}</p>
                    </div>

                    {{-- Espacio entre imagenes --}}
                    <div>
                        <label for="image_gap" class="form-label">{{ __('Espacio entre imagenes') }} (px)</label>
                        <div class="flex items-center gap-4">
                            <input type="range" id="image_gap" name="image_gap"
                                   min="0" max="16" step="2"
                                   value="{{ $settings['image_gap'] ?? 4 }}"
                                   class="flex-1"
                                   oninput="document.getElementById('gap_value').textContent = this.value + 'px'">
                            <span id="gap_value" class="text-sm font-medium text-theme w-16 text-right">{{ $settings['image_gap'] ?? 4 }}px</span>
                        </div>
                        <p class="text-xs text-theme-muted mt-1">{{ __('Separacion entre cada imagen. Rango: 0-16px.') }}</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Comportamiento') }}</h2>
                </div>
                <div class="card-body space-y-6">
                    {{-- Velocidad de scroll --}}
                    <div>
                        <label for="scroll_speed" class="form-label">{{ __('Velocidad del scroll') }} ({{ __('segundos') }})</label>
                        <div class="flex items-center gap-4">
                            <input type="range" id="scroll_speed" name="scroll_speed"
                                   min="10" max="120" step="5"
                                   value="{{ $settings['scroll_speed'] ?? 30 }}"
                                   class="flex-1"
                                   oninput="document.getElementById('speed_value').textContent = this.value + 's'">
                            <span id="speed_value" class="text-sm font-medium text-theme w-16 text-right">{{ $settings['scroll_speed'] ?? 30 }}s</span>
                        </div>
                        <p class="text-xs text-theme-muted mt-1">{{ __('Tiempo en segundos para un ciclo completo. Menor = mas rapido. Rango: 10-120s.') }}</p>
                    </div>

                    {{-- Maximo de imagenes --}}
                    <div>
                        <label for="max_images" class="form-label">{{ __('Maximo de imagenes') }}</label>
                        <input type="number" id="max_images" name="max_images"
                               min="10" max="200" step="10"
                               value="{{ $settings['max_images'] ?? 50 }}"
                               class="form-input w-32">
                        <p class="text-xs text-theme-muted mt-1">{{ __('Numero maximo de fotos a mostrar. Se seleccionan aleatoriamente. Rango: 10-200.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Preview --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Informacion') }}</h2>
                </div>
                <div class="card-body">
                    <div class="text-sm text-theme-secondary space-y-2">
                        <p>{{ __('El cintillo muestra fotos de los medios asociados a personas en el sistema.') }}</p>
                        <p>{{ __('Las imagenes se seleccionan aleatoriamente y se actualizan cada 5 minutos (cache).') }}</p>
                        <p>{{ __('Al pasar el mouse sobre una imagen, la animacion se pausa y muestra el nombre de la persona.') }}</p>
                        <p>{{ __('Al hacer clic en una imagen, se navega al perfil de la persona.') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.index') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">
                    {{ __('Guardar cambios') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
