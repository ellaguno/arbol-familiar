<x-app-layout>
    <x-slot name="title">{{ __('Buscar') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Buscar') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Encuentra personas, familias, lugares y mas') }}</p>
        </div>

        <!-- Barra de busqueda -->
        <div class="card mb-8">
            <div class="card-body">
                <form action="{{ route('search.index') }}" method="GET" class="flex gap-4">
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="q" value="{{ $query ?? '' }}"
                               placeholder="{{ __('Buscar por nombre, apellido, lugar...') }}"
                               class="form-input pl-10"
                               autofocus>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Buscar') }}</button>
                    <a href="{{ route('search.advanced') }}" class="btn-outline">{{ __('Avanzada') }}</a>
                </form>
            </div>
        </div>

        @if($query)
            <!-- Filtros por tipo -->
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'all']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'all' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Todos') }}
                    @if(isset($counts))
                        <span class="ml-1 text-xs opacity-75">({{ array_sum($counts) }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'persons']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'persons' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Personas') }}
                    @if(isset($counts['persons']))
                        <span class="ml-1 text-xs opacity-75">({{ $counts['persons'] }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'families']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'families' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Familias') }}
                    @if(isset($counts['families']))
                        <span class="ml-1 text-xs opacity-75">({{ $counts['families'] }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'places']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'places' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Lugares') }}
                    @if(isset($counts['places']))
                        <span class="ml-1 text-xs opacity-75">({{ $counts['places'] }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'events']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'events' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Eventos') }}
                    @if(isset($counts['events']))
                        <span class="ml-1 text-xs opacity-75">({{ $counts['events'] }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'media']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'media' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Media') }}
                    @if(isset($counts['media']))
                        <span class="ml-1 text-xs opacity-75">({{ $counts['media'] }})</span>
                    @endif
                </a>
                <a href="{{ route('search.index', ['q' => $query, 'type' => 'surnames']) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                       {{ $type === 'surnames' ? 'bg-mf-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('Apellidos') }}
                </a>
            </div>

            <!-- Resultados -->
            @if($results)
                <!-- Personas -->
                @if(isset($results['persons']) && ($type === 'all' || $type === 'persons'))
                    @if($results['persons']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Personas') }}
                                <span class="text-sm font-normal text-gray-500">({{ $results['persons']->total() }})</span>
                            </h2>
                            <div class="card divide-y divide-gray-100">
                                @foreach($results['persons'] as $person)
                                    <a href="{{ route('persons.show', $person) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            @if($person->photo_path)
                                                <img src="{{ Storage::url($person->photo_path) }}" class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                                    <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold">
                                                        {{ substr($person->first_name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900">{{ $person->full_name }}</h3>
                                                <p class="text-sm text-gray-500">
                                                    @if($person->birth_date)
                                                        {{ $person->birth_date->format('d/m/Y') }}
                                                    @endif
                                                    @if($person->birth_place)
                                                        - {{ $person->birth_place }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if($person->heritage_region)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                    {{ $person->heritage_region }}
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @if($type === 'persons')
                                <div class="mt-4">
                                    {{ $results['persons']->appends(request()->query())->links() }}
                                </div>
                            @elseif($results['persons']->hasMorePages())
                                <div class="mt-4">
                                    <a href="{{ route('search.index', ['q' => $query, 'type' => 'persons']) }}"
                                       class="text-mf-primary hover:underline text-sm">
                                        {{ __('Ver todos los resultados de personas') }} &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                <!-- Familias -->
                @if(isset($results['families']) && ($type === 'all' || $type === 'families'))
                    @if($results['families']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ __('Familias') }}
                                <span class="text-sm font-normal text-gray-500">({{ $results['families']->total() }})</span>
                            </h2>
                            <div class="card divide-y divide-gray-100">
                                @foreach($results['families'] as $family)
                                    <a href="{{ route('families.show', $family) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900">{{ $family->display_name }}</h3>
                                                <p class="text-sm text-gray-500">
                                                    @if($family->marriage_date)
                                                        {{ __('Casados:') }} {{ $family->marriage_date->format('d/m/Y') }}
                                                    @endif
                                                    @if($family->marriage_place)
                                                        - {{ $family->marriage_place }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @if($type === 'families')
                                <div class="mt-4">
                                    {{ $results['families']->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                <!-- Lugares -->
                @if(isset($results['places']) && ($type === 'all' || $type === 'places'))
                    @if($results['places']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('Lugares') }}
                            </h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($results['places'] as $place)
                                    <div class="card">
                                        <div class="card-body">
                                            <h3 class="font-medium text-gray-900 mb-2">{{ $place['name'] }}</h3>
                                            <div class="flex gap-4 text-sm text-gray-500">
                                                <span>{{ $place['person_count'] }} {{ __('personas') }}</span>
                                                <span>{{ $place['family_count'] }} {{ __('familias') }}</span>
                                                <span>{{ $place['event_count'] }} {{ __('eventos') }}</span>
                                            </div>
                                            <a href="{{ route('search.index', ['q' => $place['name'], 'type' => 'persons']) }}"
                                               class="text-sm text-mf-primary hover:underline mt-2 inline-block">
                                                {{ __('Ver personas de este lugar') }} &rarr;
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Eventos -->
                @if(isset($results['events']) && ($type === 'all' || $type === 'events'))
                    @if($results['events']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ __('Eventos') }}
                                <span class="text-sm font-normal text-gray-500">({{ $results['events']->total() }})</span>
                            </h2>
                            <div class="card divide-y divide-gray-100">
                                @foreach($results['events'] as $event)
                                    <div class="p-4">
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-blue-600 text-xs font-medium">{{ strtoupper(substr($event->type, 0, 3)) }}</span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">{{ $event->type_label ?? $event->type }}</span>
                                                    @if($event->person)
                                                        <span class="text-gray-500">-</span>
                                                        <a href="{{ route('persons.show', $event->person) }}" class="text-mf-primary hover:underline">
                                                            {{ $event->person->full_name }}
                                                        </a>
                                                    @endif
                                                </div>
                                                @if($event->date)
                                                    <p class="text-sm text-gray-500">{{ $event->date->format('d/m/Y') }}</p>
                                                @endif
                                                @if($event->place)
                                                    <p class="text-sm text-gray-500">{{ $event->place }}</p>
                                                @endif
                                                @if($event->description)
                                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($event->description, 150) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($type === 'events')
                                <div class="mt-4">
                                    {{ $results['events']->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                <!-- Media -->
                @if(isset($results['media']) && ($type === 'all' || $type === 'media'))
                    @if($results['media']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ __('Media') }}
                                <span class="text-sm font-normal text-gray-500">({{ $results['media']->total() }})</span>
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach($results['media'] as $item)
                                    <a href="{{ route('media.show', $item) }}" class="group">
                                        @if($item->isImage())
                                            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                                <img src="{{ $item->url }}" alt="{{ $item->title }}"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                            </div>
                                        @else
                                            <div class="aspect-square rounded-lg bg-gray-100 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <p class="mt-2 text-sm font-medium text-gray-900 truncate">{{ $item->title }}</p>
                                    </a>
                                @endforeach
                            </div>
                            @if($type === 'media')
                                <div class="mt-4">
                                    {{ $results['media']->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                <!-- Apellidos -->
                @if(isset($results['surnames']) && ($type === 'all' || $type === 'surnames'))
                    @if($results['surnames']['surnames']->isNotEmpty() || $results['surnames']['variants']->isNotEmpty())
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ __('Apellidos') }}
                            </h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($results['surnames']['surnames'] as $item)
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="flex items-center justify-between mb-2">
                                                <h3 class="font-bold text-gray-900 text-lg">{{ $item['surname'] }}</h3>
                                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                                                    {{ $item['count'] }} {{ __('personas') }}
                                                </span>
                                            </div>
                                            @if($item['variants']->isNotEmpty())
                                                <div class="text-sm text-gray-600">
                                                    <span class="font-medium">{{ __('Variantes:') }}</span>
                                                    @foreach($item['variants'] as $variant)
                                                        <span class="inline-block bg-blue-50 text-blue-700 px-2 py-0.5 rounded mr-1 mt-1">
                                                            {{ $variant->variant }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <a href="{{ route('search.index', ['q' => $item['surname'], 'type' => 'persons']) }}"
                                               class="text-sm text-mf-primary hover:underline mt-2 inline-block">
                                                {{ __('Ver todas las personas') }} &rarr;
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Sin resultados -->
                @if(($type === 'all' && $results->flatten()->isEmpty()) ||
                    ($type !== 'all' && isset($results[$type]) &&
                        (is_object($results[$type]) && method_exists($results[$type], 'isEmpty') ? $results[$type]->isEmpty() :
                        (is_array($results[$type]) ? empty($results[$type]) : true))))
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Sin resultados') }}</h3>
                            <p class="text-gray-500">{{ __('No se encontraron resultados para') }} "{{ $query }}"</p>
                            <p class="text-sm text-gray-400 mt-2">{{ __('Intenta con otros terminos o usa la busqueda avanzada.') }}</p>
                        </div>
                    </div>
                @endif
            @endif
        @else
            <!-- Estado inicial sin busqueda -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Busquedas recientes -->
                @if(!empty($recentSearches))
                    <div class="card">
                        <div class="card-header flex justify-between items-center">
                            <h2 class="text-lg font-semibold">{{ __('Busquedas recientes') }}</h2>
                            <form action="{{ route('search.clearRecent') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                    {{ __('Limpiar') }}
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="flex flex-wrap gap-2">
                                @foreach($recentSearches as $search)
                                    <a href="{{ route('search.index', ['q' => $search]) }}"
                                       class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $search }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Sugerencias -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Sugerencias') }}</h2>
                    </div>
                    <div class="card-body space-y-4">
                        @if(!empty($suggestions['surnames']))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Apellidos populares') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($suggestions['surnames'] as $surname)
                                        <a href="{{ route('search.index', ['q' => $surname]) }}"
                                           class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm">
                                            {{ $surname }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($suggestions['places']))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Lugares frecuentes') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($suggestions['places'] as $place)
                                        <a href="{{ route('search.index', ['q' => $place, 'type' => 'places']) }}"
                                           class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm">
                                            {{ $place }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($suggestions['regions']))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Regiones de origen') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($suggestions['regions'] as $region)
                                        <a href="{{ route('search.index', ['q' => $region]) }}"
                                           class="px-3 py-1 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100 text-sm">
                                            {{ $region }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
