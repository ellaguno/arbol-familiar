<x-app-layout>
    <x-slot name="title">{{ __('Actividad') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Registro de actividad') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Historial de acciones del sistema') }}</p>
            </div>
            <div class="flex gap-2">
                <!-- Boton limpiar registros -->
                <div x-data="{ showModal: false }">
                    <button @click="showModal = true" class="btn-outline text-red-600 border-red-300 hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/30">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('Limpiar registros') }}
                    </button>

                    <!-- Modal de confirmacion -->
                    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-theme-card rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-theme" id="modal-title">{{ __('Limpiar registros de actividad') }}</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-theme-muted">{{ __('Esta accion eliminara los registros de actividad. Selecciona una opcion:') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 space-y-3">
                                    <form action="{{ route('admin.activity.clear') }}" method="POST" class="flex items-center justify-between p-3 bg-theme-secondary rounded-lg">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="days" value="30">
                                        <span class="text-sm text-theme-secondary">{{ __('Eliminar registros con mas de 30 dias') }}</span>
                                        <button type="submit" class="btn-outline text-sm py-1 px-3">{{ __('Eliminar') }}</button>
                                    </form>
                                    <form action="{{ route('admin.activity.clear') }}" method="POST" class="flex items-center justify-between p-3 bg-theme-secondary rounded-lg">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="days" value="90">
                                        <span class="text-sm text-theme-secondary">{{ __('Eliminar registros con mas de 90 dias') }}</span>
                                        <button type="submit" class="btn-outline text-sm py-1 px-3">{{ __('Eliminar') }}</button>
                                    </form>
                                    <form action="{{ route('admin.activity.clear') }}" method="POST" class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="days" value="0">
                                        <span class="text-sm text-red-700 dark:text-red-300 font-medium">{{ __('Eliminar TODOS los registros') }}</span>
                                        <button type="submit" class="bg-red-600 text-white text-sm py-1 px-3 rounded hover:bg-red-700">{{ __('Eliminar todo') }}</button>
                                    </form>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-theme shadow-sm px-4 py-2 bg-theme-card text-base font-medium text-theme-secondary hover:bg-theme-secondary sm:mt-0 sm:w-auto sm:text-sm">
                                        {{ __('Cancelar') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.index') }}" class="btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('admin.activity') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="{{ __('Buscar en acciones...') }}"
                               class="form-input">
                    </div>
                    <div>
                        <select name="user" class="form-input">
                            <option value="">{{ __('Todos los usuarios') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="type" class="form-input">
                            <option value="">{{ __('Todos los tipos') }}</option>
                            <option value="create" {{ request('type') === 'create' ? 'selected' : '' }}>{{ __('Creacion') }}</option>
                            <option value="update" {{ request('type') === 'update' ? 'selected' : '' }}>{{ __('Actualizacion') }}</option>
                            <option value="delete" {{ request('type') === 'delete' ? 'selected' : '' }}>{{ __('Eliminacion') }}</option>
                            <option value="login" {{ request('type') === 'login' ? 'selected' : '' }}>{{ __('Inicio de sesion') }}</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="from" value="{{ request('from') }}"
                               min="1000-01-01" max="9999-12-31"
                               class="form-input" placeholder="{{ __('Desde') }}">
                    </div>
                    <div>
                        <input type="date" name="to" value="{{ request('to') }}"
                               min="1000-01-01" max="9999-12-31"
                               class="form-input" placeholder="{{ __('Hasta') }}">
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filtrar') }}</button>
                    <a href="{{ route('admin.activity') }}" class="btn-outline">{{ __('Quitar filtros') }}</a>
                </form>
            </div>
        </div>

        <!-- Lista de actividad -->
        <div class="card">
            <div class="divide-y divide-theme-light">
                @forelse($activities as $activity)
                    <div class="p-4 hover:bg-theme-secondary">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                @switch($activity->type ?? 'default')
                                    @case('create')
                                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                        @break
                                    @case('update')
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </div>
                                        @break
                                    @case('delete')
                                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </div>
                                        @break
                                    @case('login')
                                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </div>
                                        @break
                                    @default
                                        <div class="w-10 h-10 bg-theme-secondary rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-theme-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                @endswitch
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-theme">
                                        {{ $activity->user?->email ?? __('Sistema') }}
                                    </span>
                                    @if($activity->user?->is_admin)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Admin</span>
                                    @endif
                                </div>
                                <p class="text-theme-secondary mt-1">{{ $activity->action }}</p>
                                @if($activity->ip_address)
                                    <p class="text-xs text-theme-muted mt-1">
                                        IP: {{ $activity->ip_address }}
                                        @if($activity->user_agent)
                                            | {{ Str::limit($activity->user_agent, 50) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm text-theme-muted">{{ $activity->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-theme-muted">{{ $activity->created_at->format('H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-theme-muted">
                        <svg class="w-12 h-12 mx-auto text-theme-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        {{ __('No hay actividad registrada') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    </div>
</x-app-layout>
