<x-app-layout>
    <x-slot name="title">{{ __('Preview de importacion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('gedcom.import') }}" class="text-gray-500 hover:text-gray-700">{{ __('Importar GEDCOM') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Preview') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Preview de importacion') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Revisa los datos antes de importar') }}</p>
        </div>

        <!-- Resumen -->
        <div class="grid md:grid-cols-3 gap-4 mb-8">
            <div class="card bg-blue-50 border-blue-200">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $preview['total_individuals'] }}</div>
                    <div class="text-blue-800">{{ __('Personas') }}</div>
                </div>
            </div>
            <div class="card bg-purple-50 border-purple-200">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $preview['total_families'] }}</div>
                    <div class="text-purple-800">{{ __('Familias') }}</div>
                </div>
            </div>
            <div class="card bg-gray-50">
                <div class="card-body text-center">
                    <div class="text-lg font-medium text-gray-900 truncate">{{ $fileName }}</div>
                    <div class="text-gray-500">{{ __('Archivo') }}</div>
                </div>
            </div>
        </div>

        <!-- Errores -->
        @if(!empty($preview['errors']))
            <div class="card mb-8 bg-red-50 border-red-200">
                <div class="card-header bg-red-100">
                    <h2 class="text-lg font-semibold text-red-800">{{ __('Errores encontrados') }}</h2>
                </div>
                <div class="card-body">
                    <ul class="list-disc list-inside text-red-700 space-y-1">
                        @foreach($preview['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-red-600 font-medium">{{ __('No se puede importar hasta corregir estos errores.') }}</p>
                </div>
            </div>
        @endif

        <!-- Advertencias -->
        @if(!empty($preview['warnings']))
            <div class="card mb-8 bg-yellow-50 border-yellow-200">
                <div class="card-header bg-yellow-100">
                    <h2 class="text-lg font-semibold text-yellow-800">{{ __('Advertencias') }}</h2>
                </div>
                <div class="card-body">
                    <ul class="list-disc list-inside text-yellow-700 space-y-1">
                        @foreach($preview['warnings'] as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Preview de personas -->
        <div class="card mb-8">
            <div class="card-header flex justify-between items-center">
                <h2 class="text-lg font-semibold">{{ __('Personas') }} ({{ __('mostrando :count de :total', ['count' => count($preview['individuals']), 'total' => $preview['total_individuals']]) }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('ID') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Genero') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Nacimiento') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Lugar') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($preview['individuals'] as $indi)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $indi['gedcom_id'] }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $indi['name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($indi['gender'] === 'M')
                                        <span class="text-blue-600">{{ __('Masculino') }}</span>
                                    @elseif($indi['gender'] === 'F')
                                        <span class="text-pink-600">{{ __('Femenino') }}</span>
                                    @else
                                        <span class="text-gray-400">{{ __('Desconocido') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $indi['birth_date'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $indi['birth_place'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Preview de familias -->
        @if(!empty($preview['families']))
            <div class="card mb-8">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Familias') }} ({{ __('mostrando :count de :total', ['count' => count($preview['families']), 'total' => $preview['total_families']]) }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('ID') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Esposo') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Esposa') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Hijos') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Matrimonio') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preview['families'] as $fam)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $fam['gedcom_id'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $fam['husband'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $fam['wife'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $fam['children_count'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $fam['marriage_date'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Formulario de confirmacion -->
        @if(empty($preview['errors']))
            <form action="{{ route('gedcom.confirm') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="temp_path" value="{{ $tempPath }}">

                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Opciones de importacion') }}</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label for="privacy_level" class="form-label">{{ __('Nivel de privacidad para personas importadas') }}</label>
                            <select name="privacy_level" id="privacy_level" class="form-input">
                                <option value="private">{{ __('Solo yo (privado)') }}</option>
                                <option value="family" selected>{{ __('Mi familia') }}</option>
                                <option value="community">{{ __('Toda la comunidad') }}</option>
                                <option value="public">{{ __('Público') }}</option>
                            </select>
                            <p class="form-help">{{ __('Puedes cambiar la privacidad individualmente despues.') }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="check_duplicates" value="1" class="form-checkbox" checked>
                                <span>{{ __('Buscar duplicados antes de importar') }}</span>
                            </label>
                            <p class="form-help ml-6">{{ __('Evita crear personas duplicadas comparando nombre y fecha de nacimiento.') }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="update_existing" value="1" class="form-checkbox">
                                <span>{{ __('Actualizar registros existentes') }}</span>
                            </label>
                            <p class="form-help ml-6">{{ __('Si se encuentra un duplicado, actualiza los datos con la informacion del GEDCOM.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('gedcom.import') }}" class="btn-outline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ __('Cancelar') }}
                    </a>

                    <button type="submit" class="btn-primary"
                            onclick="return confirm('{{ __('Estas seguro de importar :persons personas y :families familias?', ['persons' => $preview['total_individuals'], 'families' => $preview['total_families']]) }}')">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('Confirmar importacion') }}
                    </button>
                </div>
            </form>
        @else
            <div class="flex justify-start">
                <a href="{{ route('gedcom.import') }}" class="btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
