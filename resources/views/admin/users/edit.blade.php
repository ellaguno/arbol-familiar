<x-app-layout>
    <x-slot name="title">{{ __('Editar') }} {{ $user->email }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center"><a href="{{ route('admin.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Admin') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('admin.users') }}" class="text-gray-500 hover:text-gray-700 ml-1 md:ml-2">{{ __('Usuarios') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Editar') }}</span>
                </li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ __('Editar usuario') }}: {{ $user->email }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="form-label">{{ __('Nombre') }}</label>
                            <input type="text" name="first_name" id="first_name"
                                   value="{{ old('first_name', $user->first_name) }}"
                                   class="form-input @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="form-label">{{ __('Apellido') }}</label>
                            <input type="text" name="last_name" id="last_name"
                                   value="{{ old('last_name', $user->last_name) }}"
                                   class="form-input @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-input @error('email') border-red-500 @enderror" required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="privacy_level" class="form-label">{{ __('Nivel de privacidad') }}</label>
                            <select name="privacy_level" id="privacy_level" class="form-input">
                                <option value="public" {{ old('privacy_level', $user->privacy_level) === 'public' ? 'selected' : '' }}>
                                    {{ __('Publico') }}
                                </option>
                                <option value="registered" {{ old('privacy_level', $user->privacy_level) === 'registered' ? 'selected' : '' }}>
                                    {{ __('Solo registrados') }}
                                </option>
                                <option value="private" {{ old('privacy_level', $user->privacy_level) === 'private' ? 'selected' : '' }}>
                                    {{ __('Privado') }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="privacy_level_note" class="form-label">{{ __('Nota') }}</label>
                            <p class="text-sm text-gray-500">{{ __('El nivel de privacidad controla quien puede ver la informacion del usuario.') }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-6 space-y-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Permisos y estado') }}</h3>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_admin" value="1"
                                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                                       {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                       class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                <span class="text-sm text-gray-700">{{ __('Administrador') }}</span>
                            </label>
                        </div>

                        @if($user->id === auth()->id())
                            <p class="text-sm text-yellow-600">
                                {{ __('No puedes modificar tu propio estado de administrador.') }}
                            </p>
                        @endif
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn-outline">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="btn-primary">
                            {{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Zona peligrosa -->
        @if($user->id !== auth()->id())
            <div class="card mt-8 border-red-200">
                <div class="card-header bg-red-50">
                    <h2 class="text-lg font-semibold text-red-700">{{ __('Zona peligrosa') }}</h2>
                </div>
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ __('Eliminar usuario') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Esta accion no se puede deshacer. Se eliminaran todos los datos del usuario.') }}</p>
                        </div>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              onsubmit="return confirm('{{ __('Esta seguro de eliminar este usuario? Esta accion no se puede deshacer.') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accent">
                                {{ __('Eliminar usuario') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
