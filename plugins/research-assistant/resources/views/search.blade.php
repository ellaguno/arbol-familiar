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

        @if(!$aiConfigured)
            <div class="card mb-6">
                <div class="card-body">
                    <div class="flex items-center gap-4 text-yellow-600 dark:text-yellow-400">
                        <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">{{ __('IA no configurada') }}</h3>
                            <p class="text-sm">{{ __('El administrador debe configurar un proveedor de IA para usar esta funcionalidad.') }}</p>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.research.settings') }}" class="text-sm underline hover:no-underline">{{ __('Ir a configuracion') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
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
                    <div class="card-body">
                        <div class="p-3 bg-theme-secondary rounded-lg border border-theme">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-theme">{{ $providerName }}</p>
                                    <p class="text-sm text-theme-muted">{{ $defaultModel }}</p>
                                </div>
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
        @endif
    </div>
</x-app-layout>
