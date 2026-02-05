<x-app-layout>
    <x-slot name="title">{{ __('Relaciones') }} - {{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('persons.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Personas') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('persons.show', $person) }}" class="text-theme-muted hover:text-theme-secondary ml-1 md:ml-2">{{ $person->full_name }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Relaciones') }}</span>
                </li>
            </ol>
        </nav>

        <div class="flex items-center gap-4 mb-8">
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
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Relaciones familiares') }}</h1>
                <p class="text-theme-secondary">{{ $person->full_name }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Relaciones existentes -->
            <div class="space-y-6">
                <!-- Padres -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Padres') }}</h2>
                    </div>
                    <div class="card-body">
                        @if($person->father || $person->mother)
                            <ul class="space-y-3">
                                @if($person->father)
                                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-theme-hover">
                                        <a href="{{ route('persons.show', $person->father) }}" class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <span class="text-blue-600 dark:text-blue-400 font-medium">{{ substr($person->father->first_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $person->father->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ __('Padre') }}</p>
                                            </div>
                                        </a>
                                        <form action="{{ route('persons.relationships.destroy', [$person, 'parent', $person->father]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2" onclick="return confirm('{{ __('Eliminar esta relacion?') }}')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if($person->mother)
                                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-theme-hover">
                                        <a href="{{ route('persons.show', $person->mother) }}" class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                                                <span class="text-pink-600 dark:text-pink-400 font-medium">{{ substr($person->mother->first_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $person->mother->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ __('Madre') }}</p>
                                            </div>
                                        </a>
                                        <form action="{{ route('persons.relationships.destroy', [$person, 'parent', $person->mother]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2" onclick="return confirm('{{ __('Eliminar esta relacion?') }}')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        @else
                            <p class="text-theme-muted text-center py-4">{{ __('Sin padres registrados') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Conyuge -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Conyuge') }}</h2>
                    </div>
                    <div class="card-body">
                        @php
                            $spouses = collect();
                            foreach($person->familiesAsHusband as $family) {
                                if($family->wife) $spouses->push(['person' => $family->wife, 'status' => $family->status]);
                            }
                            foreach($person->familiesAsWife as $family) {
                                if($family->husband) $spouses->push(['person' => $family->husband, 'status' => $family->status]);
                            }
                        @endphp
                        @if($spouses->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach($spouses as $spouse)
                                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-theme-hover">
                                        <a href="{{ route('persons.show', $spouse['person']) }}" class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-{{ $spouse['person']->gender === 'M' ? 'blue' : 'pink' }}-100 {{ $spouse['person']->gender === 'M' ? 'dark:bg-blue-900/30' : 'dark:bg-pink-900/30' }} flex items-center justify-center">
                                                <span class="text-{{ $spouse['person']->gender === 'M' ? 'blue' : 'pink' }}-600 {{ $spouse['person']->gender === 'M' ? 'dark:text-blue-400' : 'dark:text-pink-400' }} font-medium">{{ substr($spouse['person']->first_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $spouse['person']->full_name }}</p>
                                                <p class="text-sm text-theme-muted">
                                                    @switch($spouse['status'])
                                                        @case('married') {{ __('Casados') }} @break
                                                        @case('divorced') {{ __('Divorciados') }} @break
                                                        @case('widowed') {{ __('Viudo/a') }} @break
                                                        @case('separated') {{ __('Separados') }} @break
                                                        @case('partners') {{ __('Pareja') }} @break
                                                        @default {{ __('Desconocido') }}
                                                    @endswitch
                                                </p>
                                            </div>
                                        </a>
                                        <form action="{{ route('persons.relationships.destroy', [$person, 'spouse', $spouse['person']]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2" onclick="return confirm('{{ __('Eliminar esta relacion?') }}')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-theme-muted text-center py-4">{{ __('Sin conyuge registrado') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Hijos -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Hijos') }}</h2>
                    </div>
                    <div class="card-body">
                        @if($person->children->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach($person->children as $child)
                                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-theme-hover">
                                        <a href="{{ route('persons.show', $child) }}" class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                                <span class="text-theme-secondary font-medium">{{ substr($child->first_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $child->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ $child->gender === 'M' ? __('Hijo') : __('Hija') }}</p>
                                            </div>
                                        </a>
                                        <form action="{{ route('persons.relationships.destroy', [$person, 'child', $child]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2" onclick="return confirm('{{ __('Eliminar esta relacion?') }}')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-theme-muted text-center py-4">{{ __('Sin hijos registrados') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Hermanos -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Hermanos') }}</h2>
                    </div>
                    <div class="card-body">
                        @if($person->siblings->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach($person->siblings as $sibling)
                                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-theme-hover">
                                        <a href="{{ route('persons.show', $sibling) }}" class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                                <span class="text-theme-secondary font-medium">{{ substr($sibling->first_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $sibling->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ $sibling->gender === 'M' ? __('Hermano') : __('Hermana') }}</p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-theme-muted text-center py-4">{{ __('Sin hermanos registrados') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Agregar relacion -->
            <div>
                <div class="card sticky top-8">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Agregar relacion') }}</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('persons.relationships.store-with-auth', $person) }}" method="POST" class="space-y-4"
                              x-data="relationshipForm()"
                              @submit.prevent="submitForm">
                            @csrf

                            <div>
                                <label for="relationship_type" class="form-label">{{ __('Tipo de relacion') }}</label>
                                <select name="relationship_type" id="relationship_type" class="form-input" required x-model="type">
                                    <option value="">{{ __('Seleccionar') }}</option>
                                    <option value="father">{{ __('Padre de') }} {{ $person->first_name }}</option>
                                    <option value="mother">{{ __('Madre de') }} {{ $person->first_name }}</option>
                                    <option value="spouse">{{ __('Conyuge de') }} {{ $person->first_name }}</option>
                                    <option value="child">{{ __('Hijo/a de') }} {{ $person->first_name }}</option>
                                    <option value="sibling">{{ __('Hermano/a de') }} {{ $person->first_name }}</option>
                                </select>
                            </div>

                            <!-- Búsqueda de persona con autocompletado -->
                            <div class="relative">
                                <label for="person_search" class="form-label">{{ __('Buscar persona') }}</label>
                                <div class="relative">
                                    <input type="text"
                                           id="person_search"
                                           x-model="searchQuery"
                                           @focus="showResults = searchResults.length > 0"
                                           @click.away="showResults = false"
                                           class="form-input pr-10"
                                           placeholder="{{ __('Escribe nombre o apellido...') }}"
                                           autocomplete="off">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg x-show="!loading" class="w-5 h-5 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <svg x-show="loading" class="w-5 h-5 text-theme-muted animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="related_person_id" x-model="selectedPersonId" required>

                                <!-- Resultados de búsqueda -->
                                <div x-show="showResults && searchResults.length > 0"
                                     x-transition
                                     class="absolute z-50 w-full mt-1 bg-theme-card border border-theme rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                    <template x-for="person in searchResults" :key="person.id">
                                        <button type="button"
                                                @click="selectPerson(person)"
                                                class="w-full flex items-center gap-3 p-3 hover:bg-theme-hover text-left border-b last:border-b-0">
                                            <template x-if="person.photo">
                                                <img :src="person.photo" class="w-10 h-10 rounded-full object-cover">
                                            </template>
                                            <template x-if="!person.photo">
                                                <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                                    <span class="text-theme-secondary font-medium" x-text="person.name.charAt(0)"></span>
                                                </div>
                                            </template>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-theme truncate" x-text="person.name"></p>
                                                <p class="text-sm text-theme-muted">
                                                    <span x-show="person.birth_year" x-text="'*' + person.birth_year"></span>
                                                    <span x-show="!person.is_living" class="text-theme-muted">{{ __('(fallecido)') }}</span>
                                                </p>
                                            </div>
                                            <template x-if="person.requires_authorization">
                                                <span class="flex-shrink-0 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    {{ __('Requiere permiso') }}
                                                </span>
                                            </template>
                                            <template x-if="person.is_own || person.can_edit">
                                                <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full" title="{{ __('Puedes vincular') }}"></span>
                                            </template>
                                        </button>
                                    </template>
                                </div>

                                <!-- Sin resultados -->
                                <div x-show="showResults && searchQuery.length >= 2 && searchResults.length === 0 && !loading"
                                     class="absolute z-50 w-full mt-1 bg-theme-card border border-theme rounded-lg shadow-lg p-4 text-center text-theme-muted">
                                    {{ __('No se encontraron personas') }}
                                </div>
                            </div>

                            <!-- Persona seleccionada -->
                            <div x-show="selectedPerson" class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <template x-if="selectedPerson?.photo">
                                            <img :src="selectedPerson.photo" class="w-10 h-10 rounded-full object-cover">
                                        </template>
                                        <template x-if="!selectedPerson?.photo">
                                            <div class="w-10 h-10 rounded-full bg-blue-200 dark:bg-blue-900/40 flex items-center justify-center">
                                                <span class="text-blue-600 dark:text-blue-400 font-medium" x-text="selectedPerson?.name?.charAt(0)"></span>
                                            </div>
                                        </template>
                                        <div>
                                            <p class="font-medium text-theme" x-text="selectedPerson?.name"></p>
                                            <p class="text-sm text-blue-600 dark:text-blue-400" x-show="selectedPerson?.requires_authorization">
                                                {{ __('Se solicitará autorización al creador') }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearSelection" class="text-theme-muted hover:text-theme-secondary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Aviso de autorización -->
                            <div x-show="selectedPerson?.requires_authorization" class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm text-amber-800 dark:text-amber-300">
                                            {{ __('Esta persona fue creada por otro usuario. Se enviará una solicitud de autorización para establecer la relación.') }}
                                        </p>
                                        <input type="hidden" name="request_authorization" value="1">
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones para conyuge -->
                            <div x-show="type === 'spouse'" x-cloak class="space-y-4">
                                <div>
                                    <label for="family_status" class="form-label">{{ __('Estado de la relacion') }}</label>
                                    <select name="family_status" id="family_status" class="form-input">
                                        <option value="married">{{ __('Casados') }}</option>
                                        <option value="partners">{{ __('Pareja') }}</option>
                                        <option value="divorced">{{ __('Divorciados') }}</option>
                                        <option value="separated">{{ __('Separados') }}</option>
                                        <option value="widowed">{{ __('Viudo/a') }}</option>
                                        <option value="unknown">{{ __('Desconocido') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="marriage_date" class="form-label">{{ __('Fecha de matrimonio') }}</label>
                                    <input type="date" name="marriage_date" id="marriage_date" min="1000-01-01" max="9999-12-31" class="form-input">
                                </div>
                            </div>

                            <button type="submit"
                                    class="btn-primary w-full"
                                    :disabled="!selectedPersonId || !type"
                                    :class="{'opacity-50 cursor-not-allowed': !selectedPersonId || !type}">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span x-text="selectedPerson?.requires_authorization ? '{{ __('Solicitar vinculación') }}' : '{{ __('Agregar relación') }}'"></span>
                            </button>
                        </form>

                        <div class="mt-6 pt-6 border-t border-theme">
                            <p class="text-sm text-theme-muted mb-4">{{ __('No encuentras la persona?') }}</p>
                            <a href="{{ route('persons.create') }}" class="btn-outline w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                {{ __('Crear nueva persona') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function relationshipForm() {
            return {
                type: '',
                searchQuery: '',
                searchResults: [],
                selectedPerson: null,
                selectedPersonId: null,
                showResults: false,
                loading: false,
                searchTimeout: null,

                init() {
                    this.$watch('searchQuery', (value) => {
                        if (this.searchTimeout) {
                            clearTimeout(this.searchTimeout);
                        }
                        if (value.length >= 2) {
                            this.searchTimeout = setTimeout(() => {
                                this.searchPersons();
                            }, 300);
                        } else {
                            this.searchResults = [];
                        }
                    });
                },

                async searchPersons() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const url = '{{ route("persons.search-for-relationship") }}' + '?q=' + encodeURIComponent(this.searchQuery) + '&exclude={{ $person->id }}';
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.searchResults = data.results || [];
                            this.showResults = true;
                        } else {
                            console.error('Error response:', response.status);
                            this.searchResults = [];
                        }
                    } catch (error) {
                        console.error('Error searching persons:', error);
                        this.searchResults = [];
                    } finally {
                        this.loading = false;
                    }
                },

                selectPerson(person) {
                    this.selectedPerson = person;
                    this.selectedPersonId = person.id;
                    this.searchQuery = person.name;
                    this.showResults = false;
                },

                clearSelection() {
                    this.selectedPerson = null;
                    this.selectedPersonId = null;
                    this.searchQuery = '';
                },

                submitForm() {
                    if (!this.selectedPersonId || !this.type) {
                        return;
                    }
                    this.$el.submit();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
