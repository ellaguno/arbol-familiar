<x-app-layout>
    <x-slot name="title">{{ __('Contenido del sitio') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Contenido del sitio') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Edita los textos e imagenes de las pantallas principales') }}</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach($groups as $key => $group)
                <a href="{{ route('admin.content.edit', $key) }}" class="card hover:shadow-lg transition-shadow group">
                    <div class="card-body flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: var(--mf-light, #dbeafe);">
                            <svg class="w-6 h-6" style="color: var(--mf-primary, #3b82f6);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $group['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ $group['name'] }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $group['description'] }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
