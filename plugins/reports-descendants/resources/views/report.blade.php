<x-app-layout>
    <x-slot name="title">{{ __('Reporte de Descendientes') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-1">
                        <li><a href="{{ route('persons.show', $person) }}" class="text-theme-muted hover:text-theme-secondary">{{ $person->full_name }}</a></li>
                        <li>
                            <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </li>
                        <li><span class="text-theme-secondary font-medium">{{ __('Reporte de Descendientes') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Reporte de Descendientes') }}</h1>
                <p class="text-theme-muted mt-1">{{ __('Arbol indentado - :count descendientes encontrados', ['count' => $totalDescendants]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.descendants.pdf', ['person' => $person, 'generations' => $generations]) }}"
                   class="btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Descargar PDF') }}
                </a>
                <a href="{{ route('persons.show', $person) }}" class="btn-outline btn-sm">
                    {{ __('Volver') }}
                </a>
            </div>
        </div>

        <!-- Selector de generaciones -->
        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.descendants', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Arbol de descendientes -->
        <div class="card">
            <div class="card-body">
                <div class="descendant-tree">
                    @foreach($flatList as $index => $entry)
                        @php
                            $p = $entry['person'];
                            $isSpouse = $entry['type'] === 'spouse';
                            $indent = $entry['level'] * 2;
                        @endphp
                        <div class="flex items-start py-1.5 {{ $index > 0 ? 'border-t border-theme-secondary/20' : '' }}"
                             style="padding-left: {{ $indent }}rem;">
                            @if($isSpouse)
                                {{-- Conyuge --}}
                                <span class="text-mf-secondary font-bold mr-2 flex-shrink-0">+</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap gap-x-2">
                                        <a href="{{ route('persons.show', $p) }}" class="text-mf-secondary hover:underline font-medium">
                                            {{ $p->shouldProtectMinorData() ? $p->first_name : $p->full_name }}
                                        </a>
                                        @if($p->gender)
                                            <span class="text-theme-muted text-xs">
                                                {{ $p->gender === 'M' ? '&#9794;' : ($p->gender === 'F' ? '&#9792;' : '') }}
                                            </span>
                                        @endif
                                        @if($entry['marriageYear'])
                                            <span class="text-theme-muted text-xs">({{ __('m.') }} {{ $entry['marriageYear'] }})</span>
                                        @endif
                                    </div>
                                    @if(!$p->shouldProtectMinorData())
                                        <div class="text-xs text-theme-muted">
                                            @if($p->birth_date_formatted || $p->birth_year)
                                                {{ $p->birth_date_formatted ?? $p->birth_year }}
                                            @endif
                                            @if(!$p->is_living && ($p->death_date_formatted || $p->death_year))
                                                &ndash; {{ $p->death_date_formatted ?? $p->death_year }}
                                            @elseif($p->is_living)
                                                &ndash; <span class="text-green-600">{{ __('Vivo/a') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Persona (raiz o descendiente) --}}
                                @if($entry['level'] > 0)
                                    <span class="text-theme-muted mr-2 flex-shrink-0">&bull;</span>
                                @else
                                    <span class="text-mf-primary mr-2 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap gap-x-2">
                                        <a href="{{ route('persons.show', $p) }}" class="text-mf-primary hover:underline font-medium {{ $entry['level'] === 0 ? 'text-lg' : '' }}">
                                            {{ $p->shouldProtectMinorData() ? $p->first_name : $p->full_name }}
                                        </a>
                                        @if($p->gender)
                                            <span class="text-theme-muted text-xs">
                                                {{ $p->gender === 'M' ? '&#9794;' : ($p->gender === 'F' ? '&#9792;' : '') }}
                                            </span>
                                        @endif
                                        @if($p->has_ethnic_heritage)
                                            <span class="inline-block w-2 h-2 rounded-full bg-mf-secondary flex-shrink-0" title="{{ __('Herencia etnica') }}"></span>
                                        @endif
                                        @if($entry['level'] === 0)
                                            <span class="text-xs text-theme-muted bg-theme-secondary px-2 py-0.5 rounded-full">{{ __('Persona raiz') }}</span>
                                        @endif
                                    </div>
                                    @if(!$p->shouldProtectMinorData())
                                        <div class="text-xs text-theme-muted">
                                            @if($p->birth_date_formatted || $p->birth_year)
                                                {{ $p->birth_date_formatted ?? $p->birth_year }}
                                                @if($p->birth_place)
                                                    , {{ $p->birth_place }}
                                                @endif
                                            @endif
                                            @if(!$p->is_living && ($p->death_date_formatted || $p->death_year))
                                                &ndash; {{ $p->death_date_formatted ?? $p->death_year }}
                                                @if($p->death_place)
                                                    , {{ $p->death_place }}
                                                @endif
                                            @elseif($p->is_living)
                                                &ndash; <span class="text-green-600">{{ __('Vivo/a') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
