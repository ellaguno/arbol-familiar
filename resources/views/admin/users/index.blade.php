<x-app-layout>
    <x-slot name="title">{{ __('Usuarios') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Usuarios') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Gestionar usuarios del sistema') }}</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="{{ __('Buscar por email o nombre...') }}"
                               class="form-input">
                    </div>
                    <div>
                        <select name="status" class="form-input">
                            <option value="">{{ __('Todos los estados') }}</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>{{ __('Verificados') }}</option>
                            <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>{{ __('No verificados') }}</option>
                            <option value="admin" {{ request('status') === 'admin' ? 'selected' : '' }}>{{ __('Administradores') }}</option>
                            <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>{{ __('Bloqueados') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filtrar') }}</button>
                    <a href="{{ route('admin.users') }}" class="btn-outline">{{ __('Limpiar') }}</a>
                </form>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme">
                    <thead class="bg-theme-secondary">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Usuario') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Roles') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Ultimo acceso') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-theme-muted uppercase">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-theme-card divide-y divide-theme">
                        @forelse($users as $user)
                            <tr class="hover:bg-theme-secondary">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                            <span class="text-theme-secondary font-medium">{{ strtoupper(substr($user->email, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-theme">{{ $user->full_name }}</p>
                                            <p class="text-sm text-theme-muted">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->isLocked())
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ __('Bloqueado') }}</span>
                                    @elseif($user->hasVerifiedEmail())
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">{{ __('Verificado') }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">{{ __('Pendiente') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1">
                                        @if($user->is_admin)
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Admin</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-theme-muted">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('Nunca') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-theme-secondary hover:text-theme">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-theme-muted">
                                    {{ __('No se encontraron usuarios') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
