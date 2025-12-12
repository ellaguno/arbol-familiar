<x-app-layout>
    <x-slot name="title">{{ __('Importar GEDCOM') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Importar GEDCOM') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Importa tu arbol genealogico desde un archivo GEDCOM') }}</p>
        </div>

        <!-- Informacion sobre GEDCOM -->
        <div class="card mb-8 bg-blue-50 border-blue-200">
            <div class="card-body">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-2">{{ __('Que es GEDCOM?') }}</h3>
                        <p class="text-blue-800 text-sm mb-2">
                            {{ __('GEDCOM (GEnealogical Data COMmunication) es el formato estandar para intercambiar datos genealogicos entre diferentes programas.') }}
                        </p>
                        <p class="text-blue-700 text-sm">
                            {{ __('Puedes exportar archivos GEDCOM desde programas como Ancestry, MyHeritage, FamilySearch, Gramps, y muchos mas.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('gedcom.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Seleccionar archivo') }}</h2>
                </div>
                <div class="card-body">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-mf-primary transition-colors"
                         x-data="{ dragover: false, fileName: '' }"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="dragover = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name || ''"
                         :class="{ 'border-mf-primary bg-mf-light': dragover }">

                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>

                        <p class="text-gray-600 mb-2">{{ __('Arrastra tu archivo GEDCOM aqui o') }}</p>
                        <label class="btn-outline cursor-pointer inline-block">
                            <input type="file" name="file" x-ref="fileInput" class="hidden"
                                   accept=".ged,.GED,.txt"
                                   @change="fileName = $event.target.files[0]?.name || ''">
                            {{ __('Seleccionar archivo') }}
                        </label>

                        <p class="text-sm text-gray-500 mt-4">{{ __('Archivos .ged o .txt (max 10MB)') }}</p>

                        <template x-if="fileName">
                            <div class="mt-4 p-3 bg-green-50 rounded-lg inline-flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-green-700" x-text="fileName"></span>
                            </div>
                        </template>
                    </div>

                    @error('file')
                        <p class="form-error mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('gedcom.template') }}" class="text-sm text-mf-primary hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    {{ __('Descargar plantilla de ejemplo') }}
                </a>

                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ __('Analizar archivo') }}
                </button>
            </div>
        </form>

        <!-- Consejos -->
        <div class="mt-8 card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">{{ __('Consejos para importar') }}</h3>
            </div>
            <div class="card-body">
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Haz una copia de respaldo de tu archivo antes de importar.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Revisa el preview antes de confirmar la importacion.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Los archivos muy grandes pueden tardar varios minutos en procesarse.') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        {{ __('Las fotos y documentos no se importan automaticamente, solo los datos.') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
