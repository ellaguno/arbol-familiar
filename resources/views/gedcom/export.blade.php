<x-app-layout>
    <x-slot name="title">{{ __('Exportar GEDCOM') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Exportar GEDCOM') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Descarga tu arbol genealogico en formato GEDCOM') }}</p>
        </div>

        <!-- Estadisticas -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_persons'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Total personas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['total_living'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Vivas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-bold text-gray-500">{{ $stats['total_deceased'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Fallecidas') }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('gedcom.download') }}" method="POST" class="space-y-6"
              x-data="{ exportType: 'all' }">
            @csrf

            <!-- Tipo de exportacion -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Que deseas exportar?') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                           :class="{ 'border-mf-primary bg-mf-light': exportType === 'all' }">
                        <input type="radio" name="export_type" value="all" class="mt-1" x-model="exportType">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Todo el arbol') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('Exporta todas las personas y familias de tu base de datos.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                           :class="{ 'border-mf-primary bg-mf-light': exportType === 'tree' }">
                        <input type="radio" name="export_type" value="tree" class="mt-1" x-model="exportType">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Arbol desde persona raiz') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('Exporta ancestros y descendientes de una persona especifica.') }}</p>
                        </div>
                    </label>

                    <!-- Opciones de arbol -->
                    <div x-show="exportType === 'tree'" x-cloak class="ml-8 space-y-4 border-l-2 border-gray-200 pl-4">
                        <div>
                            <label for="start_person_id" class="form-label">{{ __('Persona raiz') }}</label>
                            <select name="start_person_id" id="start_person_id" class="form-input">
                                @if($userPerson)
                                    <option value="{{ $userPerson->id }}" selected>{{ $userPerson->full_name }} ({{ __('Tu') }})</option>
                                @endif
                                @foreach($persons as $person)
                                    @if(!$userPerson || $person->id !== $userPerson->id)
                                        <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="generations" class="form-label">{{ __('Generaciones') }}</label>
                            <select name="generations" id="generations" class="form-input">
                                <option value="3">3 {{ __('generaciones') }}</option>
                                <option value="5">5 {{ __('generaciones') }}</option>
                                <option value="10" selected>10 {{ __('generaciones') }}</option>
                                <option value="15">15 {{ __('generaciones') }}</option>
                                <option value="20">{{ __('Todas (max 20)') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opciones de contenido -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Opciones de contenido') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="include_living" value="1" class="form-checkbox" checked>
                        <div>
                            <span class="font-medium text-gray-900">{{ __('Incluir personas vivas') }}</span>
                            <p class="text-sm text-gray-500">{{ __('Si no se marca, solo se exportaran personas fallecidas.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="include_notes" value="1" class="form-checkbox" checked>
                        <div>
                            <span class="font-medium text-gray-900">{{ __('Incluir notas y biografias') }}</span>
                            <p class="text-sm text-gray-500">{{ __('Agrega las notas personales al archivo GEDCOM.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="include_events" value="1" class="form-checkbox" checked>
                        <div>
                            <span class="font-medium text-gray-900">{{ __('Incluir eventos') }}</span>
                            <p class="text-sm text-gray-500">{{ __('Bautizos, confirmaciones, educacion, ocupacion, etc.') }}</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Informacion -->
            <div class="card bg-blue-50 border-blue-200">
                <div class="card-body">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-blue-900 mb-1">{{ __('Acerca del archivo GEDCOM') }}</h3>
                            <ul class="text-blue-800 text-sm space-y-1">
                                <li>{{ __('El archivo se genera en formato GEDCOM 5.5.1 con codificacion UTF-8.') }}</li>
                                <li>{{ __('Las fotos y documentos no se incluyen, solo los datos.') }}</li>
                                <li>{{ __('Compatible con la mayoria de programas genealogicos.') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('gedcom.import') }}" class="text-mf-primary hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('Importar archivo') }}
                </a>

                <div class="flex gap-4">
                    <a href="{{ route('gedcom.quick') }}" class="btn-outline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ __('Exportacion rapida') }}
                    </a>

                    <button type="submit" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('Descargar GEDCOM') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
