<x-app-layout>
    <x-slot name="title">{{ __('Familias') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Familias') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Unidades familiares en tu arbol genealogico') }}</p>
            </div>
            <a href="{{ route('families.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Nueva familia') }}
            </a>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('families.index') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="{{ __('Buscar por apellido...') }}"
                               class="form-input">
                    </div>
                    <div>
                        <select name="ethnic_heritage" class="form-input">
                            <option value="">{{ __('Todas') }}</option>
                            <option value="yes" {{ request('ethnic_heritage') === 'yes' ? 'selected' : '' }}>{{ __('Con herencia cultural') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filtrar') }}</button>
                    <a href="{{ route('families.index') }}" class="btn-outline">{{ __('Limpiar') }}</a>
                </form>
            </div>
        </div>

        @if($families->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-theme-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-theme mb-2">{{ __('No hay familias') }}</h3>
                    <p class="text-theme-muted mb-4">{{ __('Crea una familia para organizar tu arbol genealogico.') }}</p>
                    <a href="{{ route('families.create') }}" class="btn-primary">{{ __('Crear primera familia') }}</a>
                </div>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($families as $family)
                    <div class="card hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            <!-- Conyuges -->
                            <div class="flex items-center justify-center gap-4 mb-4">
                                @if($family->husband)
                                    <div class="text-center">
                                        @if($family->husband->photo_path)
                                            <img src="{{ Storage::url($family->husband->photo_path) }}" class="w-16 h-16 rounded-full object-cover mx-auto">
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto">
                                                <span class="text-blue-600 dark:text-blue-400 font-bold text-xl">{{ substr($family->husband->first_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <p class="text-sm font-medium mt-1">{{ $family->husband->first_name }}</p>
                                    </div>
                                @endif
                                @if($family->husband && $family->wife)
                                    <div class="text-theme-muted">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </div>
                                @endif
                                @if($family->wife)
                                    <div class="text-center">
                                        @if($family->wife->photo_path)
                                            <img src="{{ Storage::url($family->wife->photo_path) }}" class="w-16 h-16 rounded-full object-cover mx-auto">
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mx-auto">
                                                <span class="text-pink-600 dark:text-pink-400 font-bold text-xl">{{ substr($family->wife->first_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <p class="text-sm font-medium mt-1">{{ $family->wife->first_name }}</p>
                                    </div>
                                @endif
                            </div>

                            <h3 class="text-lg font-semibold text-center text-theme mb-2">
                                {{ $family->label }}
                            </h3>

                            <div class="flex justify-center gap-2 mb-4">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-theme-secondary text-theme-secondary">
                                    @switch($family->status)
                                        @case('married') {{ __('Casados') }} @break
                                        @case('divorced') {{ __('Divorciados') }} @break
                                        @case('widowed') {{ __('Viudo/a') }} @break
                                        @case('separated') {{ __('Separados') }} @break
                                        @case('partners') {{ __('Pareja') }} @break
                                        @default {{ __('Desconocido') }}
                                    @endswitch
                                </span>
                                @if($family->hasEthnicHeritage())
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">*</span>
                                @endif
                            </div>

                            @if($family->marriage_date)
                                <p class="text-sm text-theme-muted text-center mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $family->marriage_date->format('d/m/Y') }}
                                </p>
                            @endif

                            @if($family->children->isNotEmpty())
                                <div class="border-t border-theme pt-4 mt-4">
                                    <p class="text-sm text-theme-muted mb-2">{{ __('Hijos') }}: {{ $family->children->count() }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($family->children->take(4) as $child)
                                            <div class="w-8 h-8 rounded-full bg-theme-secondary flex items-center justify-center text-xs font-medium" title="{{ $child->full_name }}">
                                                {{ substr($child->first_name, 0, 1) }}
                                            </div>
                                        @endforeach
                                        @if($family->children->count() > 4)
                                            <div class="w-8 h-8 rounded-full bg-theme-secondary flex items-center justify-center text-xs">
                                                +{{ $family->children->count() - 4 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 pt-4 border-t border-theme flex justify-between">
                                <a href="{{ route('families.show', $family) }}" class="text-mf-primary hover:underline text-sm">
                                    {{ __('Ver detalle') }}
                                </a>
                                <a href="{{ route('families.edit', $family) }}" class="text-theme-muted hover:text-theme-secondary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $families->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
