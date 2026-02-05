<x-app-layout>
    <x-slot name="title">{{ __('Personas') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Personas') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Administra las personas en tu arbol genealogico') }}</p>
            </div>
            <a href="{{ route('persons.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Nueva persona') }}
            </a>
        </div>

        <!-- Filtros y busqueda -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('persons.index') }}" method="GET" class="space-y-4">
                    <div class="grid md:grid-cols-4 gap-4">
                        <!-- Busqueda -->
                        <div class="md:col-span-2">
                            <label for="search" class="form-label">{{ __('Buscar') }}</label>
                            <div class="relative">
                                <input type="text" name="search" id="search"
                                       value="{{ request('search') }}"
                                       placeholder="{{ __('Nombre, apellido, apodo...') }}"
                                       class="form-input pl-10">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Genero -->
                        <div>
                            <label for="gender" class="form-label">{{ __('Genero') }}</label>
                            <select name="gender" id="gender" class="form-input">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                                <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>{{ __('Femenino') }}</option>
                                <option value="O" {{ request('gender') === 'O' ? 'selected' : '' }}>{{ __('Otro') }}</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="is_living" class="form-label">{{ __('Estado') }}</label>
                            <select name="is_living" id="is_living" class="form-input">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="yes" {{ request('is_living') === 'yes' ? 'selected' : '' }}>{{ __('Vivos') }}</option>
                                <option value="no" {{ request('is_living') === 'no' ? 'selected' : '' }}>{{ __('Fallecidos') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-4 gap-4">
                        @if($heritageEnabled ?? false)
                        <!-- Herencia cultural -->
                        <div>
                            <label for="ethnic_heritage" class="form-label">{{ $heritageLabel ?? __('Herencia cultural') }}</label>
                            <select name="ethnic_heritage" id="ethnic_heritage" class="form-input">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="yes" {{ request('ethnic_heritage') === 'yes' ? 'selected' : '' }}>{{ __('Si') }}</option>
                                <option value="no" {{ request('ethnic_heritage') === 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
                            </select>
                        </div>

                        <!-- Region de origen -->
                        <div>
                            <label for="heritage_region" class="form-label">{{ __('Region de origen') }}</label>
                            <select name="heritage_region" id="heritage_region" class="form-input">
                                <option value="">{{ __('Todas') }}</option>
                                @foreach($heritageRegions ?? [] as $code => $name)
                                    <option value="{{ $code }}" {{ request('heritage_region') === $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Ordenar por -->
                        <div>
                            <label for="sort" class="form-label">{{ __('Ordenar por') }}</label>
                            <select name="sort" id="sort" class="form-input">
                                <option value="first_name" {{ request('sort', 'first_name') === 'first_name' ? 'selected' : '' }}>{{ __('Nombre') }}</option>
                                <option value="patronymic" {{ request('sort') === 'patronymic' ? 'selected' : '' }}>{{ __('Apellido') }}</option>
                                <option value="birth_date" {{ request('sort') === 'birth_date' ? 'selected' : '' }}>{{ __('Fecha de nacimiento') }}</option>
                                <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>{{ __('Fecha de registro') }}</option>
                            </select>
                        </div>

                        <!-- Direccion -->
                        <div>
                            <label for="dir" class="form-label">{{ __('Orden') }}</label>
                            <select name="dir" id="dir" class="form-input">
                                <option value="asc" {{ request('dir', 'asc') === 'asc' ? 'selected' : '' }}>{{ __('Ascendente') }}</option>
                                <option value="desc" {{ request('dir') === 'desc' ? 'selected' : '' }}>{{ __('Descendente') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            {{ __('Filtrar') }}
                        </button>
                        <a href="{{ route('persons.index') }}" class="btn-outline">
                            {{ __('Limpiar') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        @if($persons->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-theme-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-theme mb-2">{{ __('No hay personas') }}</h3>
                    <p class="text-theme-muted mb-4">{{ __('Comienza agregando personas a tu arbol genealogico.') }}</p>
                    <a href="{{ route('persons.create') }}" class="btn-primary">
                        {{ __('Agregar primera persona') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($persons as $person)
                    <div class="card hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            <div class="flex items-start gap-4">
                                <!-- Foto -->
                                <div class="flex-shrink-0">
                                    @if($person->photo_path)
                                        <img src="{{ Storage::url($person->photo_path) }}"
                                             alt="{{ $person->full_name }}"
                                             class="w-16 h-16 rounded-full object-cover">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-theme-secondary flex items-center justify-center">
                                            <svg class="w-8 h-8 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('persons.show', $person) }}" class="block">
                                        <h3 class="text-lg font-semibold text-theme truncate hover:text-mf-primary">
                                            {{ $person->full_name }}
                                        </h3>
                                    </a>
                                    @if($person->nickname)
                                        <p class="text-sm text-theme-muted">"{{ $person->nickname }}"</p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if($person->gender)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $person->gender === 'M' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : ($person->gender === 'F' ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300' : 'bg-theme-secondary text-theme') }}">
                                                {{ $person->gender === 'M' ? __('Masculino') : ($person->gender === 'F' ? __('Femenino') : __('Otro')) }}
                                            </span>
                                        @endif
                                        @if(($heritageEnabled ?? false) && $person->has_ethnic_heritage)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                *
                                            </span>
                                        @endif
                                        @if(!$person->is_living)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-theme-secondary text-theme-secondary">
                                                {{ __('Fallecido') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($person->birth_date || $person->residence_place)
                                        <p class="text-sm text-theme-muted mt-2">
                                            @if($person->birth_date)
                                                {{ $person->birth_date->format('d/m/Y') }}
                                                @if($person->age)
                                                    ({{ $person->age }} {{ __('años') }})
                                                @endif
                                            @endif
                                            @if($person->residence_place)
                                                <br>{{ $person->residence_place }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="mt-4 pt-4 border-t border-theme flex justify-between">
                                <a href="{{ route('persons.show', $person) }}" class="text-mf-primary hover:underline text-sm">
                                    {{ __('Ver detalle') }}
                                </a>
                                <div class="flex gap-2">
                                    <a href="{{ route('persons.edit', $person) }}" class="text-theme-muted hover:text-theme-secondary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('persons.relationships', $person) }}" class="text-theme-muted hover:text-theme-secondary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginacion -->
            <div class="mt-6">
                {{ $persons->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
