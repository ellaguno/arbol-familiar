<x-app-layout>
    <x-slot name="title">{{ __('Importacion completada') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Importacion completada') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('Los datos han sido importados exitosamente') }}</p>
        </div>

        <!-- Estadisticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $result['stats']['persons_created'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Personas creadas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $result['stats']['persons_updated'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Personas actualizadas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $result['stats']['families_created'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Familias creadas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $result['stats']['events_created'] }}</div>
                    <div class="text-gray-600 text-sm">{{ __('Eventos creados') }}</div>
                </div>
            </div>
        </div>

        @if(($result['stats']['media_imported'] ?? 0) > 0 || ($result['stats']['media_linked'] ?? 0) > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
                <div class="card bg-green-50 border-green-200">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $result['stats']['media_imported'] ?? 0 }}</div>
                        <div class="text-green-800 text-sm">{{ __('Medios importados') }}</div>
                    </div>
                </div>
                <div class="card bg-blue-50 border-blue-200">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $result['stats']['media_linked'] ?? 0 }}</div>
                        <div class="text-blue-800 text-sm">{{ __('Medios vinculados') }}</div>
                    </div>
                </div>
                @if(($result['stats']['media_skipped'] ?? 0) > 0)
                    <div class="card bg-yellow-50 border-yellow-200">
                        <div class="card-body text-center">
                            <div class="text-3xl font-bold text-yellow-600">{{ $result['stats']['media_skipped'] }}</div>
                            <div class="text-yellow-800 text-sm">{{ __('Medios omitidos') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Advertencias -->
        @if(!empty($result['warnings']))
            <div class="card mb-8 bg-yellow-50 border-yellow-200">
                <div class="card-header bg-yellow-100">
                    <h2 class="text-lg font-semibold text-yellow-800">{{ __('Advertencias durante la importacion') }}</h2>
                </div>
                <div class="card-body">
                    <ul class="list-disc list-inside text-yellow-700 space-y-1 max-h-48 overflow-y-auto">
                        @foreach($result['warnings'] as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Proximos pasos -->
        <div class="card mb-8">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ __('Proximos pasos') }}</h2>
            </div>
            <div class="card-body">
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold">1</div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Revisa las personas importadas') }}</h4>
                            <p class="text-gray-600 text-sm">{{ __('Verifica que los datos sean correctos y completa la informacion faltante.') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold">2</div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Revisa fotos y documentos') }}</h4>
                            @if(($result['stats']['media_imported'] ?? 0) > 0)
                                <p class="text-gray-600 text-sm">{{ __('Se importaron :count medios. Verifica que esten correctamente vinculados.', ['count' => $result['stats']['media_imported']]) }}</p>
                            @else
                                <p class="text-gray-600 text-sm">{{ __('Puedes agregar fotos y documentos manualmente, o importar un archivo GEDZIP.') }}</p>
                            @endif
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold">3</div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Ajusta la privacidad') }}</h4>
                            <p class="text-gray-600 text-sm">{{ __('Revisa los niveles de privacidad de cada persona segun sea necesario.') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold">4</div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ __('Vincula tu cuenta') }}</h4>
                            <p class="text-gray-600 text-sm">{{ __('Asegurate de que tu persona en el arbol este vinculada a tu cuenta.') }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('persons.index') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ __('Ver personas') }}
            </a>
            <a href="{{ route('tree.index') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                {{ __('Ver arbol') }}
            </a>
            <a href="{{ route('gedcom.import') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                {{ __('Importar mas') }}
            </a>
        </div>
    </div>
</x-app-layout>
