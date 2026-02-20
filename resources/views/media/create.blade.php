<x-app-layout>
    <x-slot name="title">{{ __('Subir archivo') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('media.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Galeria') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Subir archivo') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Subir archivo') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Sube fotos, documentos o agrega enlaces') }}</p>
        </div>

        <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
              x-data="{
                  type: '{{ old('type', 'image') }}',
                  dragover: false,
                  fileName: ''
              }">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Tipo de archivo') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-3 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="image" class="sr-only peer" x-model="type">
                            <div class="p-4 border-2 rounded-lg text-center peer-checked:border-mf-primary peer-checked:bg-mf-light transition-colors">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('Imagen') }}</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="document" class="sr-only peer" x-model="type">
                            <div class="p-4 border-2 rounded-lg text-center peer-checked:border-mf-primary peer-checked:bg-mf-light transition-colors">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('Documento') }}</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="link" class="sr-only peer" x-model="type">
                            <div class="p-4 border-2 rounded-lg text-center peer-checked:border-mf-primary peer-checked:bg-mf-light transition-colors">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('Enlace') }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Archivo -->
            <div class="card" x-show="type !== 'link'" x-cloak>
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Archivo') }}</h2>
                </div>
                <div class="card-body">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-mf-primary transition-colors"
                         :class="{ 'border-mf-primary bg-mf-light': dragover }"
                         @dragover.prevent.stop="dragover = true"
                         @dragenter.prevent.stop="dragover = true"
                         @dragleave.prevent.stop="dragover = false"
                         @drop.prevent.stop="
                            dragover = false;
                            if ($event.dataTransfer.files.length > 0) {
                                const dt = new DataTransfer();
                                for (const file of $event.dataTransfer.files) {
                                    dt.items.add(file);
                                }
                                $refs.fileInput.files = dt.files;
                                fileName = dt.files[0]?.name || '';
                            }
                         ">

                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!fileName">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>

                        <!-- Icono de check cuando hay archivo -->
                        <svg class="w-12 h-12 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="fileName" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>

                        <p class="text-gray-600 mb-2" x-show="!fileName">{{ __('Arrastra un archivo aqui o') }}</p>
                        <p class="text-green-600 font-medium mb-2" x-show="fileName" x-cloak>
                            {{ __('Archivo seleccionado:') }} <span x-text="fileName"></span>
                        </p>
                        <label class="btn-outline cursor-pointer inline-block">
                            <input type="file" name="file" x-ref="fileInput" class="hidden"
                                   :accept="type === 'image' ? 'image/*' : '.pdf,.doc,.docx,.txt'"
                                   @change="fileName = $refs.fileInput.files[0]?.name || ''">
                            <span x-show="!fileName">{{ __('Seleccionar archivo') }}</span>
                            <span x-show="fileName" x-cloak>{{ __('Cambiar archivo') }}</span>
                        </label>

                        <p class="text-sm text-gray-500 mt-4">
                            <span x-show="type === 'image'">{{ __('JPG, PNG, WebP (max 4MB)') }}</span>
                            <span x-show="type === 'document'">{{ __('PDF, Word, TXT (max 4MB)') }}</span>
                        </p>
                    </div>
                    @error('file')
                        <p class="form-error mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- URL -->
            <div class="card" x-show="type === 'link'" x-cloak>
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Enlace externo') }}</h2>
                </div>
                <div class="card-body">
                    <label for="external_url" class="form-label">{{ __('URL') }}</label>
                    <input type="text" name="external_url" id="external_url"
                           value="{{ old('external_url') }}"
                           placeholder="www.ejemplo.com"
                           class="form-input @error('external_url') border-red-500 @enderror">
                    <p class="form-help">{{ __('Puedes escribir sin https://, se agregara automaticamente.') }}</p>
                    @error('external_url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Detalles -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Detalles') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label for="title" class="form-label">{{ __('Titulo') }}</label>
                        <input type="text" name="title" id="title"
                               value="{{ old('title') }}"
                               class="form-input">
                        <p class="form-help">{{ __('Si no se especifica, se usara el nombre del archivo.') }}</p>
                    </div>

                    <div>
                        <label for="description" class="form-label">{{ __('Descripcion') }}</label>
                        <textarea name="description" id="description" rows="3" class="form-input">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="person_id" class="form-label">{{ __('Asociar a persona') }}</label>
                        <select name="person_id" id="person_id" class="form-input">
                            <option value="">{{ __('Ninguna') }}</option>
                            @foreach($persons as $person)
                                <option value="{{ $person->id }}" {{ old('person_id', $personId) == $person->id ? 'selected' : '' }}>
                                    {{ $person->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($events->isNotEmpty())
                        <div>
                            <label for="event_id" class="form-label">{{ __('Vincular a evento') }}</label>
                            <select name="event_id" id="event_id" class="form-input">
                                <option value="">{{ __('Ninguno') }}</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->type_label }}{{ $event->date ? ' - ' . $event->date->format('d/m/Y') : '' }}{{ $event->place ? ' - ' . $event->place : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="form-help">{{ __('Vincula este archivo a un evento especifico de la persona (ej. acta de bautismo).') }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" class="form-checkbox"
                                   {{ old('is_primary') ? 'checked' : '' }}>
                            <span>{{ __('Marcar como foto principal') }}</span>
                        </label>
                        <p class="form-help ml-6">{{ __('Solo aplica si se asocia a una persona.') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('media.index') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">{{ __('Subir') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
