<x-app-layout>
    <x-slot name="title">{{ __('Editar') }} {{ $family->label }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('families.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Familias') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('families.show', $family) }}" class="text-gray-500 hover:text-gray-700 ml-1 md:ml-2">{{ $family->label }}</a>
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
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Editar familia') }}</h1>
            <p class="text-gray-600 mt-1">{{ $family->label }}</p>
        </div>

        <form action="{{ route('families.update', $family) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('families._form', ['family' => $family, 'childIds' => $childIds])

            <div class="flex justify-between">
                <button type="button" class="btn-accent" x-data @click="$dispatch('open-delete-modal')">
                    {{ __('Eliminar familia') }}
                </button>
                <div class="flex gap-4">
                    <a href="{{ route('families.show', $family) }}" class="btn-outline">{{ __('Cancelar') }}</a>
                    <button type="submit" class="btn-primary">{{ __('Guardar cambios') }}</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de eliminacion -->
    <div x-data="{ open: false }" @open-delete-modal.window="open = true">
        <div x-show="open" x-transition x-cloak class="modal-overlay" @click="open = false"></div>
        <div x-show="open" x-transition x-cloak class="modal">
            <div class="modal-content" @click.away="open = false">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-red-600 mb-4">{{ __('Eliminar familia') }}</h3>
                    <p class="text-gray-600 mb-6">
                        {{ __('Esta accion eliminara la familia') }} <strong>{{ $family->label }}</strong>.
                        {{ __('Los registros de personas no seran afectados.') }}
                    </p>

                    <form action="{{ route('families.destroy', $family) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="open = false" class="btn-outline flex-1">
                                {{ __('Cancelar') }}
                            </button>
                            <button type="submit" class="btn-accent flex-1">
                                {{ __('Eliminar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
