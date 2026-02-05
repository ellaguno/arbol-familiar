<x-app-layout>
    <x-slot name="title">{{ __('Nueva persona') }} - {{ config('app.name') }}</x-slot>

    <!-- Modal de aviso importante -->
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative bg-theme-card rounded-lg px-6 py-6 shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="text-left">
                    <h3 class="text-xl font-bold text-red-600 mb-4">{{ __('Importante') }}</h3>
                    <div class="text-sm text-theme-secondary space-y-4">
                        <p>
                            {{ __('Si el familiar que esta siendo registrado vive y es mayor de edad, recibira un mensaje en el correo electronico proporcionado solicitando su autorizacion para el uso de sus datos personales en tu arbol, si ya es un usuario de {{ config('app.name') }} recibira ademas una notificacion en el tablero de mensajes.') }}
                        </p>
                        <p>
                            {{ __('Una vez que su informacion sea autorizada apareceran sus datos completos. En tanto emite su autorizacion se mostraran unicamente sus apellidos y en caso de no autorizar su publicacion, se eliminaran los datos restantes de su perfil, conservandose unicamente sus apellidos en el arbol.') }}
                        </p>
                        <p>
                            {{ __('En caso de no ingresar un correo electronico, los datos seran publicados bajo responsabilidad unica del usuario de este sitio, quien manifiesta hacer todos los esfuerzos razonables por contactar a su familiar para recabar su consentimiento.') }}
                        </p>
                        <p>
                            {{ __('Los datos ingresados se consideran otorgados de buena fe y por interes comun entre el titular de los datos y el familiar que los ingresa, dado el parentesco que mantienen entre ellos, con el unico fin de establecer su linea familiar para su consulta privada.') }}
                        </p>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="button" @click="open = false" class="w-full btn-primary">
                        {{ __('Enterado') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $prefill = $prefill ?? [];
        $relatedPerson = $relatedPerson ?? null;
        $relation = $relation ?? null;

        $relationLabels = [
            'child' => __('Hijo/a de'),
            'father' => __('Padre de'),
            'mother' => __('Madre de'),
            'parent' => __('Padre/Madre de'),
            'sibling' => __('Hermano/a de'),
            'spouse' => __('Conyuge de'),
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('persons.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Personas') }}</a>
                </li>
                @if($relatedPerson)
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('persons.show', $relatedPerson) }}" class="text-theme-muted hover:text-theme-secondary ml-1 md:ml-2">{{ $relatedPerson->full_name }}</a>
                    </li>
                @endif
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Nueva persona') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-theme">{{ __('Nueva persona') }}</h1>
            @if($relatedPerson && $relation)
                <div class="mt-3 flex items-center gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-3">
                    @if($relatedPerson->photo_path)
                        <img src="{{ Storage::url($relatedPerson->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-{{ $relatedPerson->gender === 'M' ? 'blue' : 'pink' }}-100 {{ $relatedPerson->gender === 'M' ? 'dark:bg-blue-900/30' : 'dark:bg-pink-900/30' }} flex items-center justify-center">
                            <span class="text-{{ $relatedPerson->gender === 'M' ? 'blue' : 'pink' }}-600 {{ $relatedPerson->gender === 'M' ? 'dark:text-blue-400' : 'dark:text-pink-400' }} font-bold">{{ substr($relatedPerson->first_name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-blue-700 dark:text-blue-400">
                            <span class="font-medium">{{ $relationLabels[$relation] ?? __('Familiar de') }}</span>
                            <a href="{{ route('persons.show', $relatedPerson) }}" class="font-bold hover:underline">{{ $relatedPerson->full_name }}</a>
                        </p>
                        @if(!empty($prefill['patronymic']) || !empty($prefill['matronymic']))
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                                {{ __('Apellidos prellenados automaticamente') }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-theme-secondary mt-1">{{ __('Agrega una nueva persona a tu arbol genealogico') }}</p>
            @endif
        </div>

        <form action="{{ route('persons.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($relatedPerson && $relation)
                <input type="hidden" name="relation" value="{{ $relation }}">
                <input type="hidden" name="related_to" value="{{ $relatedPerson->id }}">

                @if($relation === 'child' && $families->count() > 1)
                    <!-- Selector de familia cuando hay multiples conyuges -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Seleccionar padres') }}</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-sm text-theme-secondary mb-4">
                                {{ __(':name tiene multiples relaciones. Selecciona los padres de este hijo:', ['name' => $relatedPerson->first_name]) }}
                            </p>
                            <div class="space-y-3">
                                @foreach($families as $family)
                                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-theme-hover {{ ($selectedFamilyId == $family->id || (!$selectedFamilyId && $loop->first)) ? 'border-blue-500 bg-blue-50' : 'border-theme' }}">
                                        <input type="radio" name="family_id" value="{{ $family->id }}"
                                               class="form-radio text-blue-600"
                                               {{ ($selectedFamilyId == $family->id || (!$selectedFamilyId && $loop->first)) ? 'checked' : '' }}
                                               onchange="window.location.href='{{ route('persons.create', ['relation' => $relation, 'related_to' => $relatedPerson->id, 'family_id' => $family->id]) }}'">
                                        <div class="flex items-center gap-2">
                                            @if($family->husband)
                                                <div class="flex items-center gap-1">
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                        <span class="text-blue-600 dark:text-blue-400 text-sm font-medium">{{ substr($family->husband->first_name, 0, 1) }}</span>
                                                    </div>
                                                    <span class="text-sm font-medium">{{ $family->husband->full_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-sm text-theme-muted">{{ __('Padre desconocido') }}</span>
                                            @endif
                                            <span class="text-theme-muted">&</span>
                                            @if($family->wife)
                                                <div class="flex items-center gap-1">
                                                    <div class="w-8 h-8 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                                                        <span class="text-pink-600 dark:text-pink-400 text-sm font-medium">{{ substr($family->wife->first_name, 0, 1) }}</span>
                                                    </div>
                                                    <span class="text-sm font-medium">{{ $family->wife->full_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-sm text-theme-muted">{{ __('Madre desconocida') }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($relation === 'child' && $families->count() == 1)
                    <input type="hidden" name="family_id" value="{{ $families->first()->id }}">
                @endif
            @endif

            @include('persons._form', ['person' => null, 'prefill' => $prefill])

            <div class="flex justify-end gap-4">
                @if($relatedPerson)
                    <a href="{{ route('tree.view', $relatedPerson) }}" class="btn-outline">{{ __('Cancelar') }}</a>
                @else
                    <a href="{{ route('persons.index') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                @endif
                <button type="submit" class="btn-primary">{{ __('Crear persona') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
