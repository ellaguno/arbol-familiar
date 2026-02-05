<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Grafico de Abanico') }} - {{ $person->full_name }}</title>
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
        .note {
            margin-top: 15px;
            padding: 8px 12px;
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            font-size: 10px;
            color: #374151;
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
    <h1>{{ __('Grafico de Abanico') }}</h1>
    <p class="subtitle">
        {{ $person->full_name }} &mdash;
        {{ __(':count ancestros encontrados', ['count' => $totalFound - 1]) }} &mdash;
        {{ __(':gen generaciones', ['gen' => $generations]) }} &mdash;
        {{ now()->format('d/m/Y') }}
    </p>

    <div class="note">
        {{ __('Este PDF contiene los datos del grafico de abanico en formato tabular. Para ver el grafico visual, descargue la version SVG.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px">#</th>
                <th>{{ __('Nombre') }}</th>
                <th>{{ __('Nacimiento') }}</th>
                <th>{{ __('Defuncion') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $currentGen = -1; @endphp
            @foreach($flatData as $num => $entry)
                @php $data = $entry['data']; @endphp
                @if($entry['generation'] !== $currentGen)
                    @php
                        $currentGen = $entry['generation'];
                        $genLabels = [
                            0 => __('Persona raiz'),
                            1 => __('Padres'),
                            2 => __('Abuelos'),
                            3 => __('Bisabuelos'),
                            4 => __('Tatarabuelos'),
                        ];
                        $genLabel = $genLabels[$currentGen] ?? __(':n° generacion', ['n' => $currentGen]);
                    @endphp
                    <tr>
                        <td colspan="4" class="generation-header">
                            {{ $genLabel }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="num">{{ $num }}</td>
                    <td>
                        <span class="name">{{ $entry['name'] }}</span>
                        @if(($data['gender'] ?? null) === 'M')
                            <span class="gender-m">&#9794;</span>
                        @elseif(($data['gender'] ?? null) === 'F')
                            <span class="gender-f">&#9792;</span>
                        @endif
                    </td>
                    <td>
                        {{ $data['birthDate'] ?? '' }}
                    </td>
                    <td>
                        @if(!($data['isLiving'] ?? false))
                            {{ $data['deathDate'] ?? '' }}
                        @else
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
