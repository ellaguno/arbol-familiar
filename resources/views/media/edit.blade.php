<x-app-layout>
    <x-slot name="title">{{ __('Editar') }} {{ $media->title }} - {{ config('app.name') }}</x-slot>

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
                    <a href="{{ route('media.show', $media) }}" class="text-gray-500 hover:text-gray-700 truncate max-w-xs ml-1 md:ml-2">{{ $media->title }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Editar') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Editar archivo') }}</h1>
        </div>

        <form action="{{ route('media.update', $media) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Preview -->
            <div class="card">
                <div class="card-body">
                    @if($media->isImage())
                        <img src="{{ $media->url }}" alt="{{ $media->title }}"
                             class="max-h-48 mx-auto rounded-lg">
                    @elseif($media->isDocument())
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-gray-600">{{ $media->file_name }}</p>
                        </div>
                    @else
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <p class="mt-2 text-gray-600 break-all">{{ $media->external_url }}</p>
                        </div>
                    @endif
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
                               value="{{ old('title', $media->title) }}"
                               class="form-input">
                    </div>

                    <div>
                        <label for="description" class="form-label">{{ __('Descripcion') }}</label>
                        <textarea name="description" id="description" rows="3" class="form-input">{{ old('description', $media->description) }}</textarea>
                    </div>

                    <div>
                        <label for="person_id" class="form-label">{{ __('Asociar a persona') }}</label>
                        <select name="person_id" id="person_id" class="form-input">
                            <option value="">{{ __('Ninguna') }}</option>
                            @foreach($persons as $person)
                                <option value="{{ $person->id }}" {{ old('person_id', $media->mediable_id) == $person->id ? 'selected' : '' }}>
                                    {{ $person->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" class="form-checkbox"
                                   {{ old('is_primary', $media->is_primary) ? 'checked' : '' }}>
                            <span>{{ __('Marcar como foto principal') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('media.show', $media) }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">{{ __('Guardar cambios') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
