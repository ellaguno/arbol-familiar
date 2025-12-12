<x-app-layout>
    <x-slot name="title">{{ __('Reporte geografico') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Reporte geografico') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Lugares de nacimiento y residencia') }}</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Paises de nacimiento -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Paises de nacimiento') }}</h2>
                </div>
                <div class="card-body">
                    @if(count($birthCountries) > 0)
                        <div class="space-y-3">
                            @foreach($birthCountries as $country)
                                @php
                                    $maxCount = $birthCountries->max('total');
                                    $percentage = $maxCount > 0 ? ($country->total / $maxCount) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-900">{{ $country->birth_country ?: __('Sin especificar') }}</span>
                                        <span class="text-gray-500">{{ number_format($country->total) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">{{ __('No hay datos disponibles') }}</p>
                    @endif
                </div>
            </div>

            <!-- Ciudades de nacimiento -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Principales ciudades de nacimiento') }}</h2>
                </div>
                <div class="card-body">
                    @if(count($birthCities) > 0)
                        <div class="space-y-3">
                            @foreach($birthCities as $city)
                                @php
                                    $maxCount = $birthCities->max('total');
                                    $percentage = $maxCount > 0 ? ($city->total / $maxCount) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-900">{{ $city->birth_city ?: __('Sin especificar') }}</span>
                                        <span class="text-gray-500">{{ number_format($city->total) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">{{ __('No hay datos disponibles') }}</p>
                    @endif
                </div>
            </div>

            <!-- Patrones de migracion -->
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Patrones de migracion') }}</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-3 gap-8">
                        @foreach($migrationStats['top_countries'] ?? [] as $country => $count)
                            <div class="text-center p-6 bg-blue-50 rounded-lg">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-blue-900">{{ $count }}</div>
                                <div class="text-sm text-blue-600">{{ $country ?: __('Sin especificar') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Regiones de origen -->
            @if(count($heritageRegions ?? []) > 0)
                <div class="card lg:col-span-2">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Regiones de origen') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($heritageRegions as $region)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $region->birth_city }}</div>
                                        <div class="text-sm text-gray-500">{{ $region->total }} {{ __('personas') }}</div>
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
