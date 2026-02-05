<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Cuadro de Pedigri') }} - {{ $person->full_name }}</title>
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
        .generation-header {
            background-color: #f3f4f6;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            color: #374151;
            margin-top: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f9fafb;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
        }
        .num {
            color: #9ca3af;
            font-family: monospace;
            width: 30px;
        }
        .name {
            font-weight: 600;
        }
        .gender-m {
            color: #3b82f6;
        }
        .gender-f {
            color: #ec4899;
        }
        .living {
            color: #059669;
        }
        .place {
            color: #9ca3af;
            font-size: 9px;
        }
        .relation {
            color: #6b7280;
            font-size: 9px;
            font-style: italic;
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
    <h1>{{ __('Cuadro de Pedigri') }}</h1>
    <p class="subtitle">
        {{ $person->full_name }} &mdash;
        {{ __(':count ancestros encontrados', ['count' => $totalFound - 1]) }} &mdash;
        {{ __(':gen generaciones', ['gen' => $generations]) }} &mdash;
        {{ now()->format('d/m/Y') }}
    </p>

    {{-- Grafico SVG del pedigri --}}
    @if(!empty($svgDataUri))
    @php
        // Calcular dimensiones del SVG para escalar correctamente
        $boxWidth = 160;
        $boxHeight = 50;
        $horizontalGap = 40;
        $baseVerticalGap = 10;
        $totalSlots = pow(2, $generations);
        $slotHeight = $boxHeight + $baseVerticalGap;
        $svgHeight = $totalSlots * $slotHeight;
        $svgWidth = ($generations + 1) * ($boxWidth + $horizontalGap) + 20;
        // Escalar para que quepa en la pagina (max 700px de ancho)
        $scale = min(1, 700 / $svgWidth);
        $displayWidth = $svgWidth * $scale;
        $displayHeight = $svgHeight * $scale;
    @endphp
    <div class="chart-container">
        <img src="{{ $svgDataUri }}" style="width: {{ $displayWidth }}px; height: {{ $displayHeight }}px;">
    </div>
    @endif

    {{-- Datos tabulares --}}
    <h1>{{ __('Datos del Pedigri') }}</h1>
    <table>
        <thead>
            <tr>
                <th style="width: 30px">#</th>
                <th>{{ __('Nombre') }}</th>
                <th>{{ __('Relacion') }}</th>
                <th>{{ __('Nacimiento') }}</th>
                <th>{{ __('Defuncion') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentGen = -1;
                $genLabels = [
                    0 => __('Persona raiz'),
                    1 => __('Padres'),
                    2 => __('Abuelos'),
                    3 => __('Bisabuelos'),
                    4 => __('Tatarabuelos'),
                ];
            @endphp
            @foreach($ahnentafel as $num => $entry)
                @if($entry['generation'] !== $currentGen)
                    @php
                        $currentGen = $entry['generation'];
                        $genLabel = $genLabels[$currentGen] ?? __(':n° generacion', ['n' => $currentGen]);
                    @endphp
                    <tr>
                        <td colspan="5" class="generation-header">
                            {{ $genLabel }}
                        </td>
                    </tr>
                @endif
                @php
                    $p = $entry['person'];
                    $isProtected = $p->shouldProtectMinorData();
                    $relation = '';
                    if ($num === 1) {
                        $relation = __('Persona raiz');
                    } elseif ($num % 2 === 0) {
                        $relation = __('Padre');
                    } else {
                        $relation = __('Madre');
                    }
                @endphp
                <tr>
                    <td class="num">{{ $num }}</td>
                    <td>
                        <span class="name">
                            {{ $isProtected ? $p->first_name : $p->full_name }}
                        </span>
                        @if($entry['gender'] === 'M')
                            <span class="gender-m">&#9794;</span>
                        @elseif($entry['gender'] === 'F')
                            <span class="gender-f">&#9792;</span>
                        @endif
                    </td>
                    <td>
                        <span class="relation">{{ $relation }}</span>
                    </td>
                    <td>
                        @if(!$isProtected)
                            {{ $p->birth_date_formatted ?? $entry['birth_year'] ?? '' }}
                            @if($p->birth_place)
                                <br><span class="place">{{ $p->birth_place }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if(!$p->is_living && !$isProtected)
                            {{ $p->death_date_formatted ?? $entry['death_year'] ?? '' }}
                            @if($p->death_place)
                                <br><span class="place">{{ $p->death_place }}</span>
                            @endif
                        @elseif($p->is_living)
                            <span class="living">{{ __('Vivo/a') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ config('app.name') }} &mdash; {{ __('Generado el') }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
