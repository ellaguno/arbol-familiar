<x-app-layout>
    <x-slot name="title">{{ __('Busqueda avanzada') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('search.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Buscar') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Avanzada') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Busqueda avanzada') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Filtra por multiples criterios para encontrar personas especificas') }}</p>
        </div>

        <form action="{{ route('search.advanced') }}" method="GET" class="space-y-6">
            <input type="hidden" name="search" value="1">

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Nombre y apellido') }}</h2>
                </div>
                <div class="card-body grid md:grid-cols-3 gap-4">
                    <div>
                        <label for="first_name" class="form-label">{{ __('Nombre') }}</label>
                        <input type="text" name="first_name" id="first_name"
                               value="{{ request('first_name') }}"
                               class="form-input"
                               placeholder="{{ __('Ej: Ivan') }}">
                    </div>

                    <div>
                        <label for="patronymic" class="form-label">{{ __('Apellido paterno') }}</label>
                        <input type="text" name="patronymic" id="patronymic"
                               value="{{ request('patronymic') }}"
                               class="form-input"
                               placeholder="{{ __('Ej: Horvat') }}">
                    </div>

                    <div>
                        <label for="matronymic" class="form-label">{{ __('Apellido materno') }}</label>
                        <input type="text" name="matronymic" id="matronymic"
                               value="{{ request('matronymic') }}"
                               class="form-input"
                               placeholder="{{ __('Ej: Kovac') }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Datos personales') }}</h2>
                </div>
                <div class="card-body grid md:grid-cols-3 gap-4">
                    <div>
                        <label for="gender" class="form-label">{{ __('Genero') }}</label>
                        <select name="gender" id="gender" class="form-input">
                            <option value="">{{ __('Cualquiera') }}</option>
                            <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                            <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>{{ __('Femenino') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="living_status" class="form-label">{{ __('Estado') }}</label>
                        <select name="living_status" id="living_status" class="form-input">
                            <option value="">{{ __('Cualquiera') }}</option>
                            <option value="living" {{ request('living_status') === 'living' ? 'selected' : '' }}>{{ __('Vivo') }}</option>
                            <option value="deceased" {{ request('living_status') === 'deceased' ? 'selected' : '' }}>{{ __('Fallecido') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="birth_place" class="form-label">{{ __('Lugar de nacimiento') }}</label>
                        <input type="text" name="birth_place" id="birth_place"
                               value="{{ request('birth_place') }}"
                               class="form-input"
                               placeholder="{{ __('Ej: Zagreb') }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Fecha de nacimiento') }}</h2>
                </div>
                <div class="card-body grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="birth_year_from" class="form-label">{{ __('Año desde') }}</label>
                        <input type="number" name="birth_year_from" id="birth_year_from"
                               value="{{ request('birth_year_from') }}"
                               class="form-input"
                               min="1500" max="{{ date('Y') }}"
                               placeholder="{{ __('Ej: 1900') }}">
                    </div>

                    <div>
                        <label for="birth_year_to" class="form-label">{{ __('Año hasta') }}</label>
                        <input type="number" name="birth_year_to" id="birth_year_to"
                               value="{{ request('birth_year_to') }}"
                               class="form-input"
                               min="1500" max="{{ date('Y') }}"
                               placeholder="{{ __('Ej: 1950') }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Herencia cultural') }}</h2>
                </div>
                <div class="card-body grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="heritage_region" class="form-label">{{ __('Region') }}</label>
                        <select name="heritage_region" id="heritage_region" class="form-input">
                            <option value="">{{ __('Cualquiera') }}</option>
                            @foreach($regions as $region)
                                <option value="{{ $region }}" {{ request('heritage_region') === $region ? 'selected' : '' }}>
                                    {{ $region }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="origin_town" class="form-label">{{ __('Pueblo de origen') }}</label>
                        <input type="text" name="origin_town" id="origin_town"
                               value="{{ request('origin_town') }}"
                               class="form-input"
                               placeholder="{{ __('Ej: Kastav') }}">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('search.advanced') }}" class="btn-outline">{{ __('Limpiar') }}</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    {{ __('Buscar') }}
                </button>
            </div>
        </form>

        @if($results)
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">
                    {{ __('Resultados') }}
                    <span class="text-gray-500 font-normal">({{ $results->total() }})</span>
                </h2>

                @if($results->isEmpty())
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Sin resultados') }}</h3>
                            <p class="text-gray-500">{{ __('No se encontraron personas con esos criterios.') }}</p>
                        </div>
                    </div>
                @else
                    <div class="card divide-y divide-gray-100">
                        @foreach($results as $person)
                            <a href="{{ route('persons.show', $person) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    @if($person->photo_path)
                                        <img src="{{ Storage::url($person->photo_path) }}" class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                            <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold">
                                                {{ substr($person->first_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900">{{ $person->full_name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            @if($person->birth_date)
                                                {{ $person->birth_date->format('d/m/Y') }}
                                            @endif
                                            @if($person->birth_place)
                                                - {{ $person->birth_place }}
                                            @endif
                                            @if(!$person->is_living)
                                                <span class="text-gray-400">({{ __('Fallecido') }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @if($person->heritage_region)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                {{ $person->heritage_region }}
                                            </span>
                                        @endif
                                        @if($person->origin_town)
                                            <p class="text-xs text-gray-500 mt-1">{{ $person->origin_town }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $results->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
