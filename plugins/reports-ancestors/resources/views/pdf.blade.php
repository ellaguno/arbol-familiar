<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Reporte de Ancestros') }} - {{ $person->full_name }}</title>
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
        .place {
            color: #9ca3af;
            font-size: 9px;
        }
        .living {
            color: #059669;
        }
        .gender {
            color: #9ca3af;
            font-size: 9px;
        }
        .heritage {
            border-left: 3px solid #2563eb;
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
    <h1>{{ __('Reporte de Ancestros') }}</h1>
    <p class="subtitle">
        {{ $person->full_name }} &mdash;
        {{ __(':count ancestros encontrados', ['count' => $totalFound - 1]) }} &mdash;
        {{ __(':gen generaciones', ['gen' => $generations]) }} &mdash;
        {{ now()->format('d/m/Y') }}
    </p>

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
            @foreach($ahnentafel as $num => $entry)
                @if($entry['generation'] !== $currentGen)
                    @php $currentGen = $entry['generation']; @endphp
                    <tr>
                        <td colspan="4" class="generation-header">
                            {{ $entry['generation_label'] }}
                        </td>
                    </tr>
                @endif
                <tr class="{{ $entry['person']->has_ethnic_heritage ? 'heritage' : '' }}">
                    <td class="num">{{ $num }}</td>
                    <td>
                        <span class="name">
                            {{ $entry['person']->shouldProtectMinorData() ? $entry['person']->first_name : $entry['person']->full_name }}
                        </span>
                        @if($entry['person']->gender)
                            <span class="gender">
                                {{ $entry['person']->gender === 'M' ? '♂' : ($entry['person']->gender === 'F' ? '♀' : '') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if(!$entry['person']->shouldProtectMinorData())
                            {{ $entry['person']->birth_date_formatted ?? $entry['person']->birth_year ?? '' }}
                            @if($entry['person']->birth_place)
                                <br><span class="place">{{ $entry['person']->birth_place }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if(!$entry['person']->is_living && !$entry['person']->shouldProtectMinorData())
                            {{ $entry['person']->death_date_formatted ?? $entry['person']->death_year ?? '' }}
                            @if($entry['person']->death_place)
                                <br><span class="place">{{ $entry['person']->death_place }}</span>
                            @endif
                        @elseif($entry['person']->is_living)
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
