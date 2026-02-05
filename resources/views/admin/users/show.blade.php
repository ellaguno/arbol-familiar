<x-app-layout>
    <x-slot name="title">{{ $user->email }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center"><a href="{{ route('admin.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Admin') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <a href="{{ route('admin.users') }}" class="text-theme-muted hover:text-theme-secondary ml-1 md:ml-2">{{ __('Usuarios') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ $user->email }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Info principal -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="text-lg font-semibold">{{ __('Informacion del usuario') }}</h2>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-outline btn-sm">{{ __('Editar') }}</a>
                    </div>
                    <div class="card-body">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Email') }}</dt>
                                <dd class="font-medium">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Nombre') }}</dt>
                                <dd class="font-medium">{{ $user->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Estado email') }}</dt>
                                <dd>
                                    @if($user->hasVerifiedEmail())
                                        <span class="text-green-600">{{ __('Verificado') }}</span>
                                    @else
                                        <span class="text-yellow-600">{{ __('Pendiente') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Estado cuenta') }}</dt>
                                <dd>
                                    @if($user->isLocked())
                                        <span class="text-red-600">{{ __('Bloqueado hasta :date', ['date' => $user->locked_until->format('d/m/Y H:i')]) }}</span>
                                    @else
                                        <span class="text-green-600">{{ __('Activo') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Privacidad') }}</dt>
                                <dd>{{ $user->privacy_level }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Registrado') }}</dt>
                                <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-theme-muted">{{ __('Ultimo acceso') }}</dt>
                                <dd>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : __('Nunca') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Estadisticas del usuario -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Actividad del usuario') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-theme-secondary rounded-lg">
                                <div class="text-2xl font-bold text-theme">{{ $stats['persons_created'] }}</div>
                                <div class="text-sm text-theme-muted">{{ __('Personas creadas') }}</div>
                            </div>
                            <div class="text-center p-4 bg-theme-secondary rounded-lg">
                                <div class="text-2xl font-bold text-theme">{{ $stats['families_created'] }}</div>
                                <div class="text-sm text-theme-muted">{{ __('Familias creadas') }}</div>
                            </div>
                            <div class="text-center p-4 bg-theme-secondary rounded-lg">
                                <div class="text-2xl font-bold text-theme">{{ $stats['media_created'] }}</div>
                                <div class="text-sm text-theme-muted">{{ __('Archivos subidos') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de actividad -->
                @if($user->activityLog->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Historial reciente') }}</h2>
                        </div>
                        <div class="divide-y divide-theme-light max-h-64 overflow-y-auto">
                            @foreach($user->activityLog as $log)
                                <div class="p-4 text-sm">
                                    <span class="text-theme-secondary">{{ $log->action }}</span>
                                    <span class="text-theme-muted text-xs ml-2">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Acciones -->
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Acciones') }}</h2>
                    </div>
                    <div class="card-body space-y-3">
                        @if(!$user->hasVerifiedEmail())
                            <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-outline w-full">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Verificar email') }}
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.users.toggle-lock', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline w-full {{ $user->isLocked() ? 'text-green-600' : 'text-red-600' }}">
                                @if($user->isLocked())
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Desbloquear') }}
                                @else
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    {{ __('Bloquear') }}
                                @endif
                            </button>
                        </form>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('{{ __('Esta seguro de eliminar este usuario?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accent w-full">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    {{ __('Eliminar usuario') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Resetear contraseña -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Resetear contrasena') }}</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="password" class="form-label">{{ __('Nueva contrasena') }}</label>
                                <input type="password" name="password" id="password" class="form-input" required minlength="8">
                            </div>
                            <div>
                                <label for="password_confirmation" class="form-label">{{ __('Confirmar') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
                            </div>
                            <button type="submit" class="btn-primary w-full">{{ __('Cambiar contrasena') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
