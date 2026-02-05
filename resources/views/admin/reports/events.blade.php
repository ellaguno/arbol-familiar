<x-app-layout>
    <x-slot name="title">{{ __('Reporte de eventos') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Eventos vitales') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Nacimientos, matrimonios y defunciones') }}</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <!-- Resumen -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stats['births']) }}</div>
                    <div class="text-sm text-blue-600 dark:text-blue-400">{{ __('Nacimientos') }}</div>
                </div>
            </div>
            <div class="card bg-pink-50 border-pink-200 dark:bg-pink-900/20 dark:border-pink-800">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-pink-900 dark:text-pink-100">{{ number_format($stats['marriages']) }}</div>
                    <div class="text-sm text-pink-600 dark:text-pink-400">{{ __('Matrimonios') }}</div>
                </div>
            </div>
            <div class="card bg-theme-secondary border-theme">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['deaths']) }}</div>
                    <div class="text-sm text-theme-secondary">{{ __('Defunciones') }}</div>
                </div>
            </div>
            <div class="card bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['average_lifespan'] ?? '-' }}</div>
                    <div class="text-sm text-blue-600 dark:text-blue-400">{{ __('Esperanza de vida') }}</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Nacimientos por decada -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Nacimientos por decada') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($birthsByDecade as $decade => $count)
                            @php
                                $maxCount = max(array_values($birthsByDecade));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-sm text-theme-muted">{{ $decade }}</span>
                                <div class="flex-1 bg-theme-secondary rounded-full h-4">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-10 text-sm text-theme-secondary text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Defunciones por decada -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Defunciones por decada') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($deathsByDecade as $decade => $count)
                            @php
                                $maxCount = max(array_values($deathsByDecade));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-sm text-theme-muted">{{ $decade }}</span>
                                <div class="flex-1 bg-theme-secondary rounded-full h-4">
                                    <div class="bg-gray-500 h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-10 text-sm text-theme-secondary text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Distribucion de edades al fallecer -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Edad al fallecer') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        @foreach($deathAgeDistribution as $range => $count)
                            @php
                                $maxCount = max(array_values($deathAgeDistribution));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-theme">{{ $range }}</span>
                                    <span class="text-theme-muted">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-theme-secondary rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Meses mas comunes -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Nacimientos por mes') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-4 gap-2">
                        @php
                            $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $maxMonth = max($birthsByMonth);
                        @endphp
                        @foreach($birthsByMonth as $month => $count)
                            @php
                                $percentage = $maxMonth > 0 ? ($count / $maxMonth) * 100 : 0;
                            @endphp
                            <div class="text-center">
                                <div class="text-xs text-theme-muted mb-1">{{ $months[$month - 1] ?? $month }}</div>
                                <div class="h-20 bg-theme-secondary rounded relative">
                                    <div class="absolute bottom-0 left-0 right-0 bg-blue-400 rounded-b" style="height: {{ $percentage }}%"></div>
                                </div>
                                <div class="text-xs text-theme-secondary mt-1">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Estadisticas adicionales -->
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Datos interesantes') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <div class="text-lg font-bold text-yellow-800 dark:text-yellow-300">
                                {{ $interestingFacts['oldest_person'] ?? __('No disponible') }}
                            </div>
                            <div class="text-sm text-yellow-600 dark:text-yellow-400">{{ __('Persona mas longeva') }}</div>
                        </div>
                        <div class="text-center p-4 bg-pink-50 dark:bg-pink-900/20 rounded-lg">
                            <div class="text-lg font-bold text-pink-800 dark:text-pink-300">
                                {{ $interestingFacts['longest_marriage'] ?? __('No disponible') }}
                            </div>
                            <div class="text-sm text-pink-600 dark:text-pink-400">{{ __('Matrimonio mas largo') }}</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-lg font-bold text-blue-800 dark:text-blue-300">
                                {{ $interestingFacts['most_children'] ?? __('No disponible') }}
                            </div>
                            <div class="text-sm text-blue-600 dark:text-blue-400">{{ __('Mayor numero de hijos') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
