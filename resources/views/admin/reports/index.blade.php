<x-app-layout>
    <x-slot name="title">{{ __('Reportes') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Reportes y estadisticas') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Analisis de datos del sistema') }}</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.reports.export') }}" method="POST" class="inline flex items-center gap-2">
                    @csrf
                    <select name="type" class="form-input text-sm py-2">
                        <option value="persons">{{ __('Personas') }}</option>
                        <option value="surnames">{{ __('Apellidos') }}</option>
                        <option value="places">{{ __('Lugares') }}</option>
                        <option value="families">{{ __('Familias') }}</option>
                    </select>
                    <button type="submit" class="btn-outline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Exportar CSV') }}
                    </button>
                </form>
                <a href="{{ route('admin.index') }}" class="btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>

        <!-- Estadisticas generales -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['users']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Usuarios') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['persons']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Personas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['families']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Familias') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['media']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Archivos') }}</div>
                </div>
            </div>
        </div>

        <!-- Reportes disponibles -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('admin.reports.demographics') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Demografico') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Edades, genero, generaciones') }}</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.geographic') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Geografico') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Lugares de nacimiento y residencia') }}</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.surnames') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Apellidos') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Distribucion de apellidos') }}</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.families') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Familias') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Estructura familiar') }}</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.events') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Eventos') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Nacimientos, matrimonios, defunciones') }}</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.data-quality') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-theme-secondary rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-theme-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme">{{ __('Calidad de datos') }}</h3>
                            <p class="text-sm text-theme-muted">{{ __('Completitud y consistencia') }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Graficos rapidos -->
        <div class="grid lg:grid-cols-2 gap-8 mt-8">
            <!-- Registros por mes -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Personas registradas por mes') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($chartData['registrations'] as $month => $count)
                            @php
                                $maxCount = max(array_values($chartData['registrations']));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-20 text-sm text-theme-muted">{{ $month }}</span>
                                <div class="flex-1 bg-theme-secondary rounded-full h-4">
                                    <div class="bg-mf-primary h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-10 text-sm text-theme-secondary text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Distribucion por genero -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Distribucion por genero') }}</h2>
                </div>
                <div class="card-body">
                    <div class="flex items-center justify-center gap-8">
                        @php
                            $total = ($chartData['gender']['M'] ?? 0) + ($chartData['gender']['F'] ?? 0) + ($chartData['gender']['unknown'] ?? 0);
                        @endphp
                        <div class="text-center">
                            <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-2">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $chartData['gender']['M'] ?? 0 }}</span>
                            </div>
                            <span class="text-sm text-theme-secondary">{{ __('Masculino') }}</span>
                            @if($total > 0)
                                <p class="text-xs text-theme-muted">{{ number_format((($chartData['gender']['M'] ?? 0) / $total) * 100, 1) }}%</p>
                            @endif
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 bg-pink-100 dark:bg-pink-900/30 rounded-full flex items-center justify-center mb-2">
                                <span class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ $chartData['gender']['F'] ?? 0 }}</span>
                            </div>
                            <span class="text-sm text-theme-secondary">{{ __('Femenino') }}</span>
                            @if($total > 0)
                                <p class="text-xs text-theme-muted">{{ number_format((($chartData['gender']['F'] ?? 0) / $total) * 100, 1) }}%</p>
                            @endif
                        </div>
                        @if(($chartData['gender']['unknown'] ?? 0) > 0)
                            <div class="text-center">
                                <div class="w-20 h-20 bg-theme-secondary rounded-full flex items-center justify-center mb-2">
                                    <span class="text-2xl font-bold text-theme-secondary">{{ $chartData['gender']['unknown'] }}</span>
                                </div>
                                <span class="text-sm text-theme-secondary">{{ __('Sin especificar') }}</span>
                                @if($total > 0)
                                    <p class="text-xs text-theme-muted">{{ number_format(($chartData['gender']['unknown'] / $total) * 100, 1) }}%</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
