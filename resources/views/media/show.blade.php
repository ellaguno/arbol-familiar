<x-app-layout>
    <x-slot name="title">{{ $media->title }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('media.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Galeria') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium truncate max-w-xs ml-1 md:ml-2">{{ $media->title }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Preview -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-body p-0">
                        @if($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->title }}"
                                 class="w-full h-auto rounded-lg">
                        @elseif($media->isDocument())
                            @if($media->isPdf())
                                <div class="aspect-[4/3]">
                                    <iframe src="{{ $media->url }}" class="w-full h-full rounded-lg"></iframe>
                                </div>
                            @else
                                <div class="p-12 text-center">
                                    <svg class="w-24 h-24 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-600 mb-4">{{ $media->file_name }}</p>
                                    <a href="{{ route('media.download', $media) }}" class="btn-primary">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        {{ __('Descargar') }}
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="p-12 text-center">
                                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <p class="text-gray-600 mb-4">{{ __('Enlace externo') }}</p>
                                <a href="{{ $media->external_url }}" target="_blank" class="btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ __('Abrir enlace') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Informacion') }}</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $media->title }}</h3>
                            @if($media->is_primary)
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ __('Principal') }}
                                </span>
                            @endif
                        </div>

                        @if($media->description)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Descripcion') }}</dt>
                                <dd class="text-gray-700">{{ $media->description }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Tipo') }}</dt>
                            <dd class="text-gray-700">
                                @if($media->isImage()) {{ __('Imagen') }}
                                @elseif($media->isDocument()) {{ __('Documento') }}
                                @else {{ __('Enlace') }}
                                @endif
                            </dd>
                        </div>

                        @if($media->file_size)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Tamano') }}</dt>
                                <dd class="text-gray-700">{{ $media->formatted_size }}</dd>
                            </div>
                        @endif

                        @if($media->mediable)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Persona asociada') }}</dt>
                                <dd>
                                    <a href="{{ route('persons.show', $media->mediable_id) }}" class="text-mf-primary hover:underline">
                                        {{ $media->mediable->full_name }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        @if($media->created_at)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Subido') }}</dt>
                                <dd class="text-gray-700">{{ $media->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body space-y-2">
                        @if($media->file_path)
                            <a href="{{ route('media.download', $media) }}" class="btn-outline w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                {{ __('Descargar') }}
                            </a>
                        @endif

                        @if($media->mediable_id)
                            <form action="{{ route('media.primary', $media) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-outline w-full">
                                    @if($media->is_primary)
                                        {{ __('Quitar como principal') }}
                                    @else
                                        {{ __('Marcar como principal') }}
                                    @endif
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('media.edit', $media) }}" class="btn-outline w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('Editar') }}
                        </a>

                        <form action="{{ route('media.destroy', $media) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accent w-full" onclick="return confirm('{{ __('Eliminar este archivo?') }}')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Eliminar') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
