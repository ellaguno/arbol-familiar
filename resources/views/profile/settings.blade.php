<x-app-layout>
    <x-slot name="title">{{ __('Configuracion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-theme">{{ __('Configuracion') }}</h1>
            <p class="text-theme-secondary mt-1">{{ __('Administra tu cuenta y preferencias') }}</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Columna izquierda: Menu -->
            <div class="lg:col-span-1">
                <div class="card">
                    <div class="card-body p-0">
                        <nav class="space-y-1">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-theme-secondary hover:bg-theme-secondary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Datos personales') }}
                            </a>
                            <a href="{{ route('profile.settings') }}" class="flex items-center gap-3 px-4 py-3 text-mf-primary bg-mf-light border-l-4 border-mf-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('Configuracion') }}
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Contenido -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informacion de cuenta -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Informacion de cuenta') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">{{ __('Correo electronico') }}</label>
                                <p class="text-theme">{{ $user->email }}</p>
                                <p class="form-help">
                                    @if($user->email_verified_at)
                                        <span class="text-green-600 dark:text-green-400">{{ __('Verificado') }}</span>
                                    @else
                                        <span class="text-yellow-600 dark:text-yellow-400">{{ __('Pendiente de verificacion') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="form-label">{{ __('Miembro desde') }}</label>
                                <p class="text-theme">{{ $user->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <label class="form-label">{{ __('Ultimo acceso') }}</label>
                                <p class="text-theme">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cambiar contrasena -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Cambiar contrasena') }}</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="current_password" class="form-label">{{ __('Contrasena actual') }}</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-input @error('current_password') border-red-500 @enderror" required>
                                @error('current_password')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="form-label">{{ __('Nueva contrasena') }}</label>
                                <input type="password" name="password" id="password"
                                       class="form-input @error('password') border-red-500 @enderror" required>
                                <p class="form-help">{{ __('Minimo 8 caracteres, mayusculas, minusculas, numeros y simbolos.') }}</p>
                                @error('password')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="form-label">{{ __('Confirmar nueva contrasena') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-input" required>
                            </div>

                            <button type="submit" class="btn-primary">
                                {{ __('Actualizar contrasena') }}
                            </button>
                        </form>
                    </div>
                </div>


                <!-- Tema -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Tema') }}</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-theme-secondary mb-4">{{ __('Elige el modo de color que prefieras. Si seleccionas "Default del sitio", se usara la configuracion del administrador.') }}</p>
                        <form action="{{ route('profile.theme.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-wrap gap-3">
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-3 rounded-lg border-2 transition-colors {{ ($user->theme_preference ?? 'default') === 'default' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-theme' }}">
                                    <input type="radio" name="theme_preference" value="default" class="form-radio" {{ ($user->theme_preference ?? 'default') === 'default' ? 'checked' : '' }}>
                                    <div>
                                        <span class="font-medium">{{ __('Default del sitio') }}</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-3 rounded-lg border-2 transition-colors {{ ($user->theme_preference ?? 'default') === 'light' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-theme' }}">
                                    <input type="radio" name="theme_preference" value="light" class="form-radio" {{ ($user->theme_preference ?? 'default') === 'light' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <span class="font-medium">{{ __('Claro') }}</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer px-4 py-3 rounded-lg border-2 transition-colors {{ ($user->theme_preference ?? 'default') === 'dark' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-theme' }}">
                                    <input type="radio" name="theme_preference" value="dark" class="form-radio" {{ ($user->theme_preference ?? 'default') === 'dark' ? 'checked' : '' }}>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                        <span class="font-medium">{{ __('Oscuro') }}</span>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" class="btn-primary mt-4">
                                {{ __('Guardar preferencia') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Estado en linea -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Estado en linea') }}</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-theme-secondary mb-4">
                            {{ __('Si desactivas esta opcion, no apareceras en las listas de usuarios en linea y nadie podra ver que estas conectado.') }}
                        </p>
                        <form action="{{ route('profile.online-status.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="show_online_status" value="0">
                                <input type="checkbox" name="show_online_status" value="1"
                                       class="form-checkbox"
                                       {{ ($user->show_online_status ?? true) ? 'checked' : '' }}>
                                <span class="font-medium text-theme">{{ __('Mostrar mi estado en linea') }}</span>
                            </label>

                            <button type="submit" class="btn-primary mt-4">
                                {{ __('Guardar preferencia') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Zona de peligro -->
                <div class="card border-red-200 dark:border-red-800">
                    <div class="card-header bg-red-50 dark:bg-red-900/20">
                        <h2 class="text-lg font-semibold text-red-800 dark:text-red-400">{{ __('Eliminar perfil ¡Precaución!') }}</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-theme-secondary mb-4">
                            {{ __('Eliminar tu cuenta borrara permanentemente todos tus datos. Esta accion no se puede deshacer.') }}
                        </p>

                        <button type="button" class="btn-accent" x-data @click="$dispatch('open-delete-modal')">
                            {{ __('Eliminar mi cuenta') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de eliminacion -->
    <div x-data="{ open: false }" @open-delete-modal.window="open = true">
        <div x-show="open" x-transition x-cloak class="modal-overlay" @click="open = false"></div>
        <div x-show="open" x-transition x-cloak class="modal">
            <div class="modal-content" @click.away="open = false">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-4">{{ __('Eliminar cuenta') }}</h3>
                    <p class="text-theme-secondary mb-6">
                        {{ __('Esta accion eliminara permanentemente tu cuenta y todos tus datos. Para confirmar, ingresa tu contrasena y escribe ELIMINAR.') }}
                    </p>

                    <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('DELETE')

                        <div>
                            <label for="delete_password" class="form-label">{{ __('Contrasena') }}</label>
                            <input type="password" name="password" id="delete_password" class="form-input" required>
                        </div>

                        <div>
                            <label for="confirmation" class="form-label">{{ __('Escribe ELIMINAR para confirmar') }}</label>
                            <input type="text" name="confirmation" id="confirmation" class="form-input" required>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="open = false" class="btn-outline flex-1">
                                {{ __('Cancelar') }}
                            </button>
                            <button type="submit" class="btn-accent flex-1">
                                {{ __('Eliminar cuenta') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
