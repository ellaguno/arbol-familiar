<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Reporte de Descendientes') }} - {{ $person->full_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 4px;
            color: #1e40af;
        }
        .subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        .chart-container {
            text-align: center;
            margin-bottom: 20px;
            page-break-after: always;
        }
        .chart-container svg {
            max-width: 100%;
            height: auto;
        }
        .tree-entry {
            padding: 3px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .entry-name {
            font-weight: 600;
            font-size: 10px;
        }
        .entry-name a {
            color: #1e40af;
            text-decoration: none;
        }
        .spouse-prefix {
            color: #2563eb;
            font-weight: bold;
            font-size: 11px;
        }
        .spouse-name {
            font-weight: 600;
            color: #2563eb;
            font-size: 10px;
        }
        .bullet {
            color: #9ca3af;
            font-size: 10px;
        }
        .dates {
            font-size: 9px;
            color: #6b7280;
        }
        .place {
            color: #9ca3af;
            font-size: 9px;
        }
        .living {
            color: #059669;
            font-size: 9px;
        }
        .marriage-year {
            color: #9ca3af;
            font-size: 9px;
        }
        .gender {
            color: #9ca3af;
            font-size: 9px;
        }
        .heritage {
            border-left: 3px solid #2563eb;
            padding-left: 4px;
        }
        .root-label {
            font-size: 9px;
            color: #6b7280;
            background-color: #f3f4f6;
            padding: 1px 6px;
            border-radius: 3px;
        }
        .root-entry {
            font-size: 12px;
            font-weight: bold;
            padding: 6px 0;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 4px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <h1>{{ __('Reporte de Descendientes') }}</h1>
    <p class="subtitle">
        {{ $person->full_name }} &mdash;
        {{ __(':count descendientes encontrados', ['count' => $totalDescendants]) }} &mdash;
        {{ __(':gen generaciones', ['gen' => $generations]) }} &mdash;
        {{ now()->format('d/m/Y') }}
    </p>

    {{-- Grafico SVG del arbol de descendientes --}}
    @if(count($flatList) <= 80)
        <div class="chart-container">
            @include('reports-descendants::svg-tree')
        </div>
    @endif

    {{-- Lista indentada detallada --}}
    <h1>{{ __('Detalle de Descendientes') }}</h1>

    @foreach($flatList as $index => $entry)
        @php
            $p = $entry['person'];
            $isSpouse = $entry['type'] === 'spouse';
            $indent = $entry['level'] * 20;
        @endphp

        @if($entry['level'] === 0 && $entry['type'] === 'person')
            {{-- Persona raiz --}}
            <div class="root-entry {{ $p->has_ethnic_heritage ? 'heritage' : '' }}" style="padding-left: {{ $indent }}px;">
                {{ $p->shouldProtectMinorData() ? $p->first_name : $p->full_name }}
                @if($p->gender)
                    <span class="gender">{{ $p->gender === 'M' ? '&#9794;' : ($p->gender === 'F' ? '&#9792;' : '') }}</span>
                @endif
                <span class="root-label">{{ __('Persona raiz') }}</span>
                @if(!$p->shouldProtectMinorData())
                    <br>
                    <span class="dates">
                        @if($p->birth_date_formatted || $p->birth_year)
                            {{ $p->birth_date_formatted ?? $p->birth_year }}
                            @if($p->birth_place)
                                , <span class="place">{{ $p->birth_place }}</span>
                            @endif
                        @endif
                        @if(!$p->is_living && ($p->death_date_formatted || $p->death_year))
                            &ndash; {{ $p->death_date_formatted ?? $p->death_year }}
                            @if($p->death_place)
                                , <span class="place">{{ $p->death_place }}</span>
                            @endif
                        @elseif($p->is_living)
                            &ndash; <span class="living">{{ __('Vivo/a') }}</span>
                        @endif
                    </span>
                @endif
            </div>
        @elseif($isSpouse)
            {{-- Conyuge --}}
            <div class="tree-entry" style="padding-left: {{ $indent }}px;">
                <span class="spouse-prefix">+</span>
                <span class="spouse-name">
                    {{ $p->shouldProtectMinorData() ? $p->first_name : $p->full_name }}
                </span>
                @if($p->gender)
                    <span class="gender">{{ $p->gender === 'M' ? '&#9794;' : ($p->gender === 'F' ? '&#9792;' : '') }}</span>
                @endif
                @if($entry['marriageYear'])
                    <span class="marriage-year">({{ __('m.') }} {{ $entry['marriageYear'] }})</span>
                @endif
                @if(!$p->shouldProtectMinorData())
                    <br>
                    <span class="dates" style="padding-left: {{ $indent + 14 }}px;">
                        @if($p->birth_date_formatted || $p->birth_year)
                            {{ $p->birth_date_formatted ?? $p->birth_year }}
                        @endif
                        @if(!$p->is_living && ($p->death_date_formatted || $p->death_year))
                            &ndash; {{ $p->death_date_formatted ?? $p->death_year }}
                        @elseif($p->is_living)
                            &ndash; <span class="living">{{ __('Vivo/a') }}</span>
                        @endif
                    </span>
                @endif
            </div>
        @else
            {{-- Descendiente --}}
            <div class="tree-entry {{ $p->has_ethnic_heritage ? 'heritage' : '' }}" style="padding-left: {{ $indent }}px;">
                <span class="bullet">&bull;</span>
                <span class="entry-name">
                    {{ $p->shouldProtectMinorData() ? $p->first_name : $p->full_name }}
                </span>
                @if($p->gender)
                    <span class="gender">{{ $p->gender === 'M' ? '&#9794;' : ($p->gender === 'F' ? '&#9792;' : '') }}</span>
                @endif
                @if(!$p->shouldProtectMinorData())
                    <br>
                    <span class="dates" style="padding-left: {{ $indent + 10 }}px;">
                        @if($p->birth_date_formatted || $p->birth_year)
                            {{ $p->birth_date_formatted ?? $p->birth_year }}
                            @if($p->birth_place)
                                , <span class="place">{{ $p->birth_place }}</span>
                            @endif
                        @endif
                        @if(!$p->is_living && ($p->death_date_formatted || $p->death_year))
                            &ndash; {{ $p->death_date_formatted ?? $p->death_year }}
                            @if($p->death_place)
                                , <span class="place">{{ $p->death_place }}</span>
                            @endif
                        @elseif($p->is_living)
                            &ndash; <span class="living">{{ __('Vivo/a') }}</span>
                        @endif
                    </span>
                @endif
            </div>
        @endif
    @endforeach

    <div class="footer">
        {{ config('app.name') }} &mdash; {{ __('Generado el') }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
