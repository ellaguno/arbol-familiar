<x-app-layout>
    <x-slot name="title">{{ __('Investigar') }} {{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('research.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Investigacion') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Nueva busqueda') }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold text-theme mb-6">{{ __('Investigar persona') }}</h1>

        <!-- Person card -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    @if($person->photo_path)
                        <img src="{{ Storage::url($person->photo_path) }}"
                             alt="{{ $person->full_name }}"
                             class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-20 h-20 rounded-full bg-mf-primary text-white flex items-center justify-center text-2xl font-bold">
                            {{ substr($person->given_names, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-semibold text-theme">{{ $person->full_name }}</h2>
                        <p class="text-theme-muted">
                            @if($person->birth_date)
                                {{ $person->birth_date->format('Y') }}
                                @if($person->birth_place) - {{ $person->birth_place }} @endif
                            @endif
                            @if($person->death_date)
                                &mdash; {{ $person->death_date->format('Y') }}
                                @if($person->death_place) - {{ $person->death_place }} @endif
                            @endif
                        </p>
                        @if($person->father || $person->mother)
                            <p class="text-sm text-theme-muted mt-1">
                                @if($person->father)
                                    {{ __('Padre:') }} {{ $person->father->full_name }}
                                @endif
                                @if($person->father && $person->mother) | @endif
                                @if($person->mother)
                                    {{ __('Madre:') }} {{ $person->mother->full_name }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Search form -->
        <form action="{{ route('research.search') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="person_id" value="{{ $person->id }}">

            <div class="card">
                <div class="card-body">
                    <label class="block text-sm font-medium text-theme mb-2">{{ __('Que deseas investigar sobre esta persona?') }}</label>
                    <textarea name="query" rows="3" class="form-input w-full" required
                              placeholder="{{ __('Ej: Buscar registros de nacimiento, matrimonio o defuncion. Encontrar familiares. Investigar historia del apellido...') }}">{{ old('query', __('Investigar a :name', ['name' => $person->full_name])) }}</textarea>
                    @error('query')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-theme">{{ __('Fuentes de busqueda') }}</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($sources as $source)
                            <label class="flex items-center gap-3 p-3 border border-theme rounded-lg cursor-pointer hover:bg-theme-secondary transition-colors">
                                <input type="checkbox" name="sources[]" value="{{ $source->getId() }}"
                                       class="form-checkbox" {{ $source->isEnabled() ? 'checked' : '' }}>
                                <span class="text-theme">{{ $source->getName() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('sources')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-theme">{{ __('Asistente de IA') }}</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-theme mb-2">{{ __('Proveedor') }}</label>
                            <select name="ai_provider" id="ai_provider" class="form-input w-full"
                                    x-data="{ provider: '{{ old('ai_provider', $defaultProvider) }}' }"
                                    x-model="provider"
                                    @change="$dispatch('provider-changed', provider)">
                                @foreach($providers as $key => $provider)
                                    <option value="{{ $key }}"
                                            {{ !$provider['configured'] ? 'disabled' : '' }}
                                            {{ old('ai_provider', $defaultProvider) === $key ? 'selected' : '' }}>
                                        {{ $provider['name'] }}
                                        @if(!$provider['configured'])
                                            ({{ __('No configurado') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('ai_provider')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="modelSelector(@js($providers), '{{ old('ai_provider', $defaultProvider) }}', '{{ old('ai_model', $defaultModel) }}')"
                             @provider-changed.window="updateModels($event.detail)">
                            <label class="block text-sm font-medium text-theme mb-2">{{ __('Modelo') }}</label>
                            <select name="ai_model" class="form-input w-full" x-model="selectedModel">
                                <template x-for="(name, id) in models" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                            @error('ai_model')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('persons.show', $person) }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    {{ __('Iniciar investigacion') }}
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
