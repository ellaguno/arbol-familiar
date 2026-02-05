<x-app-layout>
    <x-slot name="title">{{ __('Administracion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-theme">{{ __('Panel de Administracion') }}</h1>
            <p class="text-theme-secondary mt-1">{{ __('Gestion del sistema y estadisticas') }}</p>
        </div>

        <!-- Estadisticas rapidas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 dark:text-blue-400 text-sm font-medium">{{ __('Usuarios') }}</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['users']['total'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                        {{ $stats['users']['verified'] }} {{ __('verificados') }} |
                        {{ $stats['users']['verified'] }} {{ __('activos') }}
                    </p>
                </div>
            </div>

            <div class="card bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 dark:text-blue-400 text-sm font-medium">{{ __('Personas') }}</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['content']['persons'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:border-purple-800">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 dark:text-purple-400 text-sm font-medium">{{ __('Familias') }}</p>
                            <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $stats['content']['families'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 dark:text-yellow-400 text-sm font-medium">{{ __('Actividad hoy') }}</p>
                            <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $stats['activity']['today'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">{{ $stats['activity']['week'] }} {{ __('esta semana') }}</p>
                </div>
            </div>
        </div>

        <!-- Accesos rapidos -->
        <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <a href="{{ route('admin.users') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Usuarios') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Gestionar usuarios') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.content') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Contenido') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Textos e imagenes') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Reportes') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Estadisticas') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.activity') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Actividad') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Registro de acciones') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.plugins') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Plugins') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Extensiones') }}</p>
                    </div>
                </div>
            </a>

            @if(Route::has('admin.research.settings'))
            <a href="{{ route('admin.research.settings') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Investigacion IA') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Proveedores y API') }}</p>
                    </div>
                </div>
            </a>
            @endif

            <a href="{{ route('admin.settings') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body flex items-center gap-4">
                    <div class="w-10 h-10 bg-theme-secondary rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-theme-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-theme">{{ __('Configuracion') }}</h3>
                        <p class="text-sm text-theme-muted">{{ __('Sistema y colores') }}</p>
                    </div>
                </div>
            </a>
        </div>

        {!! $hooks->render('admin.sidebar', []) !!}

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Usuarios recientes -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-lg font-semibold">{{ __('Usuarios recientes') }}</h2>
                    <a href="{{ route('admin.users') }}" class="text-sm text-mf-primary hover:underline">{{ __('Ver todos') }}</a>
                </div>
                <div class="divide-y divide-theme-light">
                    @forelse($recentUsers as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="block p-4 hover:bg-theme-secondary transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                    <span class="text-theme-secondary font-medium">{{ substr($user->email, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-theme truncate">{{ $user->full_name }}</p>
                                    <p class="text-sm text-theme-muted truncate">{{ $user->email }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-theme-muted">{{ $user->created_at->diffForHumans() }}</p>
                                    @if($user->is_admin)
                                        <span class="text-xs bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-2 py-0.5 rounded-full">Admin</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-theme-muted">{{ __('No hay usuarios recientes') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-lg font-semibold">{{ __('Actividad reciente') }}</h2>
                    <a href="{{ route('admin.activity') }}" class="text-sm text-mf-primary hover:underline">{{ __('Ver todo') }}</a>
                </div>
                <div class="divide-y divide-theme-light max-h-96 overflow-y-auto">
                    @forelse($recentActivity as $log)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-theme-secondary flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-theme">
                                        <span class="font-medium">{{ $log->user?->email ?? 'Sistema' }}</span>
                                        <span class="text-theme-muted">{{ $log->action }}</span>
                                    </p>
                                    <p class="text-xs text-theme-muted">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-theme-muted">{{ __('No hay actividad reciente') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
