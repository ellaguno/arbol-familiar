<x-app-layout>
    <x-slot name="title">{{ __('Editar') }} {{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('persons.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Personas') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('persons.show', $person) }}" class="text-theme-muted hover:text-theme-secondary ml-1 md:ml-2">{{ $person->full_name }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Editar') }}</span>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Editar persona') }}</h1>
                <p class="text-theme-secondary mt-1">{{ $person->full_name }}</p>
            </div>

            <!-- Foto de perfil -->
            <div class="flex items-center gap-4">
                @if($person->photo_path)
                    <img src="{{ Storage::url($person->photo_path) }}"
                         alt="{{ $person->full_name }}"
                         class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-theme-secondary flex items-center justify-center">
                        <svg class="w-8 h-8 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                @endif
                <div class="flex flex-col gap-2">
                    <form action="{{ route('persons.photo.update', $person) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="photo" accept="image/*" class="text-sm file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-theme-secondary file:text-theme-secondary hover:file:bg-theme-hover">
                        <button type="submit" class="btn-outline text-sm py-1 px-3">{{ __('Subir') }}</button>
                    </form>
                    @if($person->photo_path)
                        <form action="{{ route('persons.photo.delete', $person) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm">
                                {{ __('Eliminar foto') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('persons.update', $person) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('persons._form', ['person' => $person])

            <div class="flex justify-between">
                <button type="button" class="btn-accent" x-data @click="$dispatch('open-delete-modal')">
                    {{ __('Eliminar persona') }}
                </button>
                <div class="flex gap-4">
                    <a href="{{ route('persons.show', $person) }}" class="btn-outline">{{ __('Cancelar') }}</a>
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
                    <h3 class="text-xl font-bold text-red-600 mb-4">{{ __('Eliminar persona') }}</h3>
                    <p class="text-theme-secondary mb-6">
                        {{ __('Esta accion eliminara permanentemente a') }} <strong>{{ $person->full_name }}</strong>.
                        {{ __('Esta accion no se puede deshacer.') }}
                    </p>

                    <form action="{{ route('persons.destroy', $person) }}" method="POST">
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
