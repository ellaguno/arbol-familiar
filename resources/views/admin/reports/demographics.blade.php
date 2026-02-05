<x-app-layout>
    <x-slot name="title">{{ __('Reporte demografico') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Reporte demografico') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Analisis de edades, genero y generaciones') }}</p>
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
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['total_persons']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Total personas') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ number_format($stats['living']) }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Con vida') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ $stats['average_age'] ?? '-' }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Edad promedio') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-theme">{{ $stats['generations'] }}</div>
                    <div class="text-sm text-theme-muted">{{ __('Generaciones') }}</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Piramide de edades -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Piramide de edades') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($agePyramid as $range => $data)
                            <div class="flex items-center gap-2">
                                <div class="w-20 flex justify-end">
                                    <div class="bg-blue-500 h-6 rounded-l" style="width: {{ ($data['M'] / max(1, max(array_column($agePyramid, 'M')))) * 100 }}%"></div>
                                </div>
                                <div class="w-16 text-center text-sm font-medium">{{ $range }}</div>
                                <div class="w-20">
                                    <div class="bg-pink-500 h-6 rounded-r" style="width: {{ ($data['F'] / max(1, max(array_column($agePyramid, 'F')))) * 100 }}%"></div>
                                </div>
                                <div class="text-xs text-theme-muted w-16">
                                    {{ $data['M'] }}M / {{ $data['F'] }}F
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-center gap-8 mt-4 pt-4 border-t border-theme">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                            <span class="text-sm text-theme-secondary">{{ __('Masculino') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-pink-500 rounded"></div>
                            <span class="text-sm text-theme-secondary">{{ __('Femenino') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribucion por genero -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Distribucion por genero') }}</h2>
                </div>
                <div class="card-body">
                    <div class="flex items-center justify-center gap-12 py-8">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-theme">{{ $genderStats['M'] ?? 0 }}</div>
                            <div class="text-sm text-theme-muted">{{ __('Masculino') }}</div>
                            <div class="text-xs text-theme-muted">
                                {{ $stats['total_persons'] > 0 ? number_format((($genderStats['M'] ?? 0) / $stats['total_persons']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-24 h-24 bg-pink-100 dark:bg-pink-900/30 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-12 h-12 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-theme">{{ $genderStats['F'] ?? 0 }}</div>
                            <div class="text-sm text-theme-muted">{{ __('Femenino') }}</div>
                            <div class="text-xs text-theme-muted">
                                {{ $stats['total_persons'] > 0 ? number_format((($genderStats['F'] ?? 0) / $stats['total_persons']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribucion por decada de nacimiento -->
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Nacimientos por decada') }}</h2>
                </div>
                <div class="card-body">
                    <div class="flex items-end gap-2 h-48">
                        @foreach($birthsByDecade as $decade => $count)
                            @php
                                $maxCount = max(array_values($birthsByDecade));
                                $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-mf-primary rounded-t" style="height: {{ $height }}%"></div>
                                <span class="text-xs text-theme-muted mt-2 transform -rotate-45 origin-top-left">{{ $decade }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
