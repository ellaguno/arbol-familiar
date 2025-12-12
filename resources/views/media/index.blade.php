<x-app-layout>
    <x-slot name="title">{{ __('Galeria') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Galeria') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Fotos, documentos y enlaces') }}</p>
            </div>
            <a href="{{ route('media.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Subir archivo') }}
            </a>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('media.index') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="{{ __('Buscar por titulo...') }}"
                               class="form-input">
                    </div>
                    <div>
                        <select name="type" class="form-input">
                            <option value="">{{ __('Todos los tipos') }}</option>
                            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>{{ __('Imagenes') }}</option>
                            <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>{{ __('Documentos') }}</option>
                            <option value="link" {{ request('type') === 'link' ? 'selected' : '' }}>{{ __('Enlaces') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filtrar') }}</button>
                    <a href="{{ route('media.index') }}" class="btn-outline">{{ __('Limpiar') }}</a>
                </form>
            </div>
        </div>

        @if($media->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No hay archivos') }}</h3>
                    <p class="text-gray-500 mb-4">{{ __('Sube fotos y documentos para tu arbol genealogico.') }}</p>
                    <a href="{{ route('media.create') }}" class="btn-primary">{{ __('Subir primer archivo') }}</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($media as $item)
                    <div class="group relative">
                        <a href="{{ route('media.show', $item) }}" class="block">
                            @if($item->isImage())
                                <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    <img src="{{ $item->url }}" alt="{{ $item->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                </div>
                            @elseif($item->isDocument())
                                <div class="aspect-square rounded-lg bg-gray-100 flex items-center justify-center">
                                    @if($item->isPdf())
                                        <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 13h1c.55 0 1 .45 1 1v2c0 .55-.45 1-1 1h-1v1.5H7V13h1.5zm1 2.5v-1h-1v1h1zm2.5-2.5h1.5c.55 0 1 .45 1 1v3c0 .55-.45 1-1 1H12V13zm1 4v-3h-.5v3h.5zm2-4h2v1h-1.5v.5h1v1h-1v2H15V13z"/>
                                        </svg>
                                    @else
                                        <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @endif
                                </div>
                            @else
                                <div class="aspect-square rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                            @endif
                        </a>

                        @if($item->is_primary)
                            <div class="absolute top-2 left-2">
                                <span class="bg-yellow-400 text-yellow-900 text-xs px-2 py-0.5 rounded-full">
                                    <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </span>
                            </div>
                        @endif

                        <p class="mt-2 text-sm font-medium text-gray-900 truncate">{{ $item->title }}</p>
                        <p class="text-xs text-gray-500">{{ $item->formatted_size }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $media->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
