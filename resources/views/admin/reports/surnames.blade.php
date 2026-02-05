<x-app-layout>
    <x-slot name="title">{{ __('Reporte de apellidos') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Distribucion de apellidos') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Analisis de apellidos en la base de datos') }}</p>
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
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['unique_surnames']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Apellidos unicos') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ $stats['most_common'] }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Mas comun') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['heritage_surnames'] ?? 0) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Apellidos de herencia') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['persons_with_surname'], 1) }}%</div>
                    <div class="text-sm text-theme-muted">{{ __('Con apellido') }}</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Top 20 apellidos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Top 20 apellidos') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($topSurnames as $index => $surname)
                            @php
                                $maxCount = $topSurnames->first()->total ?? 1;
                                $percentage = ($surname->total / $maxCount) * 100;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-6 text-sm font-medium text-theme-muted">{{ $index + 1 }}</span>
                                <div class="flex-1">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-theme">{{ $surname->last_name }}</span>
                                        <span class="text-theme-muted">{{ $surname->total }}</span>
                                    </div>
                                    <div class="w-full bg-theme-secondary rounded-full h-2">
                                        <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Apellidos por letra inicial -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Apellidos por letra inicial') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-6 gap-2">
                        @foreach($surnamesByLetter as $letter => $count)
                            <div class="text-center p-2 bg-theme-secondary rounded">
                                <div class="text-lg font-bold text-theme">{{ $letter }}</div>
                                <div class="text-xs text-theme-muted">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Apellidos de herencia identificados -->
            @if(isset($heritageSurnames) && count($heritageSurnames) > 0)
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Apellidos de herencia mas frecuentes') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($heritageSurnames as $surname)
                            <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-theme">{{ $surname->last_name }}</div>
                                    <div class="text-sm text-theme-muted">{{ $surname->total }} {{ __('personas') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
