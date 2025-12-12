<x-app-layout>
    <x-slot name="title">{{ __('Galeria de') }} {{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('persons.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Personas') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('persons.show', $person) }}" class="text-gray-500 hover:text-gray-700 ml-1 md:ml-2">{{ $person->full_name }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Galeria') }}</span>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div class="flex items-center gap-4">
                @if($person->photo_path)
                    <img src="{{ Storage::url($person->photo_path) }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                        <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold text-xl">{{ substr($person->first_name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('Galeria') }}</h1>
                    <p class="text-gray-600">{{ $person->full_name }}</p>
                </div>
            </div>
            <a href="{{ route('media.create', ['person_id' => $person->id]) }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Agregar') }}
            </a>
        </div>

        @if($media->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Sin fotos ni documentos') }}</h3>
                    <p class="text-gray-500 mb-4">{{ __('Agrega fotos y documentos para esta persona.') }}</p>
                    <a href="{{ route('media.create', ['person_id' => $person->id]) }}" class="btn-primary">
                        {{ __('Agregar primer archivo') }}
                    </a>
                </div>
            </div>
        @else
            <!-- Foto principal -->
            @php $primary = $media->where('is_primary', true)->first() @endphp
            @if($primary && $primary->isImage())
                <div class="mb-8">
                    <h2 class="text-lg font-semibold mb-4">{{ __('Foto principal') }}</h2>
                    <div class="card inline-block">
                        <a href="{{ route('media.show', $primary) }}">
                            <img src="{{ $primary->url }}" alt="{{ $primary->title }}"
                                 class="max-h-64 rounded-lg">
                        </a>
                    </div>
                </div>
            @endif

            <!-- Grid de media -->
            <h2 class="text-lg font-semibold mb-4">{{ __('Todos los archivos') }} ({{ $media->count() }})</h2>
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
                                    <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
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
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
