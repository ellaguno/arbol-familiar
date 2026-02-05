<x-app-layout>
    <x-slot name="title">{{ __('Configuracion de Investigacion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('admin.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Administracion') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Investigacion') }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold text-theme mb-8">{{ __('Configuracion del Asistente de Investigacion') }}</h1>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.research.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Default AI Provider -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Proveedor de IA por defecto') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-theme mb-2">{{ __('Proveedor') }}</label>
                            <select name="ai_provider" id="ai_provider" class="form-input w-full"
                                    x-data="{ provider: '{{ old('ai_provider', $settings['ai_provider'] ?? 'openrouter') }}' }"
                                    x-model="provider"
                                    @change="$dispatch('provider-changed', provider)">
                                @foreach($providers as $key => $provider)
                                    <option value="{{ $key }}" {{ ($settings['ai_provider'] ?? 'openrouter') === $key ? 'selected' : '' }}>
                                        {{ $provider['name'] }}
                                        @if($provider['configured'])
                                            ({{ __('Configurado') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-data="modelSelector(@js($providers), '{{ old('ai_provider', $settings['ai_provider'] ?? 'openrouter') }}', '{{ old('ai_model', $settings['ai_model'] ?? '') }}')"
                             @provider-changed.window="updateModels($event.detail)">
                            <label class="block text-sm font-medium text-theme mb-2">{{ __('Modelo por defecto') }}</label>
                            <select name="ai_model" class="form-input w-full" x-model="selectedModel">
                                <template x-for="(name, id) in models" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Keys -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Claves de API') }}</h2>
                    <p class="text-sm text-theme-muted mt-1">{{ __('Las claves se almacenan encriptadas. Deja en blanco para mantener la clave existente.') }}</p>
                </div>
                <div class="card-body space-y-4">
                    @foreach(['openrouter' => 'OpenRouter', 'deepseek' => 'Deepseek', 'openai' => 'OpenAI', 'anthropic' => 'Anthropic'] as $key => $name)
                        <div>
                            <label class="block text-sm font-medium text-theme mb-2">
                                {{ $name }} API Key
                                @if(!empty($settings["{$key}_api_key"]))
                                    <span class="text-green-600 dark:text-green-400 text-xs ml-2">{{ __('Configurado') }}</span>
                                @endif
                            </label>
                            <input type="password" name="{{ $key }}_api_key"
                                   class="form-input w-full"
                                   placeholder="{{ !empty($settings["{$key}_api_key"]) ? '••••••••••••••••' : __('No configurado') }}"
                                   autocomplete="off">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sources -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Fuentes de busqueda') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="familysearch_enabled" value="1" class="form-checkbox"
                               {{ ($settings['familysearch_enabled'] ?? true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-theme font-medium">FamilySearch</span>
                            <p class="text-sm text-theme-muted">{{ __('Genera URLs de busqueda para registros historicos') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="wikipedia_enabled" value="1" class="form-checkbox"
                               {{ ($settings['wikipedia_enabled'] ?? true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-theme font-medium">Wikipedia</span>
                            <p class="text-sm text-theme-muted">{{ __('Busca articulos relacionados en Wikipedia') }}</p>
                        </div>
                    </label>

                    <div class="pt-4 border-t border-theme">
                        <label class="block text-sm font-medium text-theme mb-2">{{ __('Resultados maximos por fuente') }}</label>
                        <input type="number" name="max_results_per_source"
                               value="{{ $settings['max_results_per_source'] ?? 10 }}"
                               min="1" max="50"
                               class="form-input w-32">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Guardar configuracion') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function modelSelector(providers, initialProvider, initialModel) {
            return {
                providers: providers,
                currentProvider: initialProvider,
                selectedModel: initialModel,
                models: {},

                init() {
                    this.updateModels(this.currentProvider);
                    this.$nextTick(() => {
                        if (initialModel && this.models[initialModel]) {
                            this.selectedModel = initialModel;
                        }
                    });
                },

                updateModels(provider) {
                    this.currentProvider = provider;
                    if (this.providers[provider]) {
                        this.models = this.providers[provider].models;
                        const modelIds = Object.keys(this.models);
                        if (modelIds.length > 0 && !this.models[this.selectedModel]) {
                            this.selectedModel = modelIds[0];
                        }
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
