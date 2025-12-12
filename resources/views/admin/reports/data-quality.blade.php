<x-app-layout>
    <x-slot name="title">{{ __('Calidad de datos') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Calidad de datos') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Analisis de completitud y consistencia') }}</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <!-- Puntuacion general -->
        <div class="card mb-8">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Puntuacion de calidad') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('Basado en completitud de campos') }}</p>
                    </div>
                    <div class="text-center">
                        @php
                            $qualityScore = $stats['quality_score'] ?? 0;
                            $scoreColor = $qualityScore >= 80 ? 'green' : ($qualityScore >= 60 ? 'yellow' : 'red');
                        @endphp
                        <div class="w-24 h-24 rounded-full bg-{{ $scoreColor }}-100 flex items-center justify-center">
                            <span class="text-3xl font-bold text-{{ $scoreColor }}-600">{{ number_format($qualityScore) }}%</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-{{ $scoreColor }}-500 h-4 rounded-full transition-all" style="width: {{ $qualityScore }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Completitud de campos en Personas -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Completitud - Personas') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @foreach($personCompleteness as $field => $percentage)
                            @php
                                $color = $percentage >= 80 ? 'green' : ($percentage >= 50 ? 'yellow' : 'red');
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-900">{{ __($field) }}</span>
                                    <span class="text-{{ $color }}-600">{{ number_format($percentage, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Completitud de campos en Familias -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Completitud - Familias') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @foreach($familyCompleteness as $field => $percentage)
                            @php
                                $color = $percentage >= 80 ? 'green' : ($percentage >= 50 ? 'yellow' : 'red');
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-900">{{ __($field) }}</span>
                                    <span class="text-{{ $color }}-600">{{ number_format($percentage, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Problemas detectados -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Problemas detectados') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        @forelse($issues as $issue)
                            <div class="flex items-start gap-3 p-3 bg-{{ $issue['severity'] === 'high' ? 'red' : ($issue['severity'] === 'medium' ? 'yellow' : 'blue') }}-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    @if($issue['severity'] === 'high')
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @elseif($issue['severity'] === 'medium')
                                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $issue['title'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $issue['description'] }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $issue['count'] }} {{ __('registros afectados') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-green-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('No se detectaron problemas') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recomendaciones -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Recomendaciones') }}</h2>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        @forelse($recommendations as $rec)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $rec['title'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $rec['description'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                {{ __('No hay recomendaciones en este momento') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Registros duplicados -->
            <div class="card lg:col-span-2">
                <div class="card-header flex justify-between items-center">
                    <h2 class="text-lg font-semibold">{{ __('Posibles duplicados') }}</h2>
                    <span class="text-sm text-gray-500">{{ count($duplicates) }} {{ __('encontrados') }}</span>
                </div>
                <div class="card-body">
                    @if(count($duplicates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Persona 1') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Persona 2') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Similitud') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Razon') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($duplicates as $dup)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <a href="{{ route('persons.show', $dup['person1']) }}" class="text-mf-primary hover:underline">
                                                    {{ $dup['person1']->full_name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('persons.show', $dup['person2']) }}" class="text-mf-primary hover:underline">
                                                    {{ $dup['person2']->full_name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                                    {{ $dup['similarity'] }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $dup['reason'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-green-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('No se encontraron registros duplicados') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
