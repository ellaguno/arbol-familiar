<x-app-layout>
    <x-slot name="title">{{ __('Reporte de familias') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Estructura familiar') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Analisis de las relaciones familiares') }}</p>
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
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_families']) }}</div>
                    <div class="text-sm text-gray-500">{{ __('Total familias') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['with_children'], 1) }}%</div>
                    <div class="text-sm text-gray-500">{{ __('Con hijos') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['average_children'], 1) }}</div>
                    <div class="text-sm text-gray-500">{{ __('Hijos promedio') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['married']) }}</div>
                    <div class="text-sm text-gray-500">{{ __('Matrimonios') }}</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Distribucion por numero de hijos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Familias por numero de hijos') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        @foreach($childrenDistribution as $children => $count)
                            @php
                                $maxCount = max(array_values($childrenDistribution));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-900">
                                        @if($children == 0)
                                            {{ __('Sin hijos') }}
                                        @elseif($children == 1)
                                            {{ __('1 hijo') }}
                                        @elseif($children >= 10)
                                            {{ __('10+ hijos') }}
                                        @else
                                            {{ $children }} {{ __('hijos') }}
                                        @endif
                                    </span>
                                    <span class="text-gray-500">{{ number_format($count) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Familias mas grandes -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Familias mas numerosas') }}</h2>
                </div>
                <div class="card-body">
                    @if(count($largestFamilies) > 0)
                        <div class="space-y-4">
                            @foreach($largestFamilies as $family)
                                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-lg font-bold text-purple-600">{{ $family->children_count }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            {{ $family->husband?->full_name ?? __('Desconocido') }}
                                            &
                                            {{ $family->wife?->full_name ?? __('Desconocida') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $family->children_count }} {{ __('hijos') }}
                                            @if($family->marriage_date)
                                                | {{ __('Casados') }}: {{ $family->marriage_date->format('Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('families.show', $family) }}" class="text-mf-primary hover:underline text-sm">
                                        {{ __('Ver') }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">{{ __('No hay datos disponibles') }}</p>
                    @endif
                </div>
            </div>

            <!-- Matrimonios por decada -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Matrimonios por decada') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-2">
                        @foreach($marriagesByDecade as $decade => $count)
                            @php
                                $maxCount = max(array_values($marriagesByDecade));
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-sm text-gray-500">{{ $decade }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-4">
                                    <div class="bg-pink-500 h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-10 text-sm text-gray-700 text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tipos de relacion -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Estado de las familias') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-700">{{ $familyStatus['complete'] ?? 0 }}</div>
                            <div class="text-sm text-blue-600">{{ __('Completas') }}</div>
                            <div class="text-xs text-blue-500">{{ __('Ambos padres') }}</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-700">{{ $familyStatus['single_parent'] ?? 0 }}</div>
                            <div class="text-sm text-yellow-600">{{ __('Un padre') }}</div>
                            <div class="text-xs text-yellow-500">{{ __('Monoparental') }}</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-700">{{ $familyStatus['with_marriage'] ?? 0 }}</div>
                            <div class="text-sm text-blue-600">{{ __('Con matrimonio') }}</div>
                            <div class="text-xs text-blue-500">{{ __('Fecha registrada') }}</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-700">{{ $familyStatus['with_children'] ?? 0 }}</div>
                            <div class="text-sm text-purple-600">{{ __('Con hijos') }}</div>
                            <div class="text-xs text-purple-500">{{ __('Al menos uno') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
