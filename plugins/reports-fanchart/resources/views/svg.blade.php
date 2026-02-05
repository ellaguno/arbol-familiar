@php
    $width = 800;
    $height = 450;
    $centerX = $width / 2;
    $centerY = $height - 30;
    $maxRadius = 380;
    $minRadius = 60;

    /**
     * Aplanar la estructura de arbol del abanico en un array indexado por posicion Ahnentafel.
     * index 1 = persona raiz, index 2 = padre, index 3 = madre, etc.
     */
    function flattenFanData($node, $index = 1, $generation = 0, &$result = []) {
        $result[$index] = [
            'data' => $node['data'] ?? $node,
            'name' => $node['name'] ?? ($node['data']['name'] ?? ''),
            'generation' => $generation,
            'index' => $index,
        ];
        if (!empty($node['children'])) {
            foreach ($node['children'] as $i => $child) {
                flattenFanData($child, $index * 2 + $i, $generation + 1, $result);
            }
        }
        return $result;
    }

    /**
     * Calcular el punto en coordenadas cartesianas dado angulo y radio.
     * Angulo 0 = izquierda (PI rad), angulo PI = derecha (0 rad).
     * El abanico va de PI a 0 (izquierda a derecha, semicirculo superior).
     */
    function polarToCartesian($cx, $cy, $radius, $angleRad) {
        return [
            'x' => $cx + $radius * cos($angleRad),
            'y' => $cy - $radius * sin($angleRad),
        ];
    }

    /**
     * Generar el path SVG de un arco (sector anular).
     */
    function arcPath($cx, $cy, $innerR, $outerR, $startAngle, $endAngle) {
        // Puntos del arco exterior (de startAngle a endAngle)
        $outerStart = polarToCartesian($cx, $cy, $outerR, $startAngle);
        $outerEnd = polarToCartesian($cx, $cy, $outerR, $endAngle);

        // Puntos del arco interior (de endAngle a startAngle, invertido)
        $innerStart = polarToCartesian($cx, $cy, $innerR, $endAngle);
        $innerEnd = polarToCartesian($cx, $cy, $innerR, $startAngle);

        $largeArc = ($endAngle - $startAngle) > M_PI ? 1 : 0;

        // Construir path: arco exterior -> linea -> arco interior -> cerrar
        $d = sprintf(
            'M %.2f %.2f A %.2f %.2f 0 %d 0 %.2f %.2f L %.2f %.2f A %.2f %.2f 0 %d 1 %.2f %.2f Z',
            $outerStart['x'], $outerStart['y'],
            $outerR, $outerR, $largeArc, $outerEnd['x'], $outerEnd['y'],
            $innerStart['x'], $innerStart['y'],
            $innerR, $innerR, $largeArc, $innerEnd['x'], $innerEnd['y']
        );

        return $d;
    }

    /**
     * Truncar nombre para que quepa en el arco.
     */
    function truncateName($name, $maxChars) {
        if (mb_strlen($name) <= $maxChars) {
            return $name;
        }
        return mb_substr($name, 0, $maxChars - 1) . "\u{2026}";
    }

    /**
     * Color de fondo segun genero.
     */
    function genderColor($gender) {
        return match ($gender) {
            'M' => '#dbeafe',  // blue-100
            'F' => '#fce7f3',  // pink-100
            default => '#f3f4f6', // gray-100
        };
    }

    /**
     * Color del borde segun genero.
     */
    function genderStroke($gender) {
        return match ($gender) {
            'M' => '#93c5fd',  // blue-300
            'F' => '#f9a8d4',  // pink-300
            default => '#d1d5db', // gray-300
        };
    }

    $flatData = flattenFanData($fanData);
    $totalGenerations = $generations;
    $ringWidth = ($maxRadius - $minRadius) / max($totalGenerations, 1);
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $width }} {{ $height }}" width="{{ $width }}" height="{{ $height }}">
    <defs>
        <style>
            .fan-text { font-family: 'DejaVu Sans', Arial, sans-serif; fill: #1f2937; }
            .fan-text-sm { font-size: 9px; }
            .fan-text-md { font-size: 11px; }
            .fan-text-lg { font-size: 13px; }
            .fan-text-root { font-size: 11px; font-weight: bold; }
            .fan-dates { font-size: 7px; fill: #6b7280; }
        </style>
    </defs>

    <!-- Fondo -->
    <rect width="{{ $width }}" height="{{ $height }}" fill="white"/>

    <!-- Persona raiz (circulo central) -->
    @if(isset($flatData[1]))
        @php
            $rootData = $flatData[1]['data'];
            $rootGender = $rootData['gender'] ?? null;
        @endphp
        <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="{{ $minRadius }}" fill="{{ genderColor($rootGender) }}" stroke="{{ genderStroke($rootGender) }}" stroke-width="1.5"/>
        <text x="{{ $centerX }}" y="{{ $centerY - 12 }}" text-anchor="middle" class="fan-text fan-text-root">{{ truncateName($flatData[1]['name'], 20) }}</text>
        @php
            $rootBirth = $rootData['birthDate'] ?? '';
            $rootDeath = $rootData['deathDate'] ?? '';
            $rootDates = '';
            if ($rootBirth || $rootDeath) {
                $rootDates = ($rootBirth ?: '?') . ' - ' . ($rootDeath ?: ($rootData['isLiving'] ? e(__('vivo/a')) : '?'));
            }
        @endphp
        @if($rootDates)
            <text x="{{ $centerX }}" y="{{ $centerY + 4 }}" text-anchor="middle" class="fan-dates" style="font-size: 9px;">{{ $rootDates }}</text>
        @endif
    @endif

    <!-- Arcos de ancestros -->
    @foreach($flatData as $index => $entry)
        @if($entry['generation'] > 0 && $entry['generation'] <= $totalGenerations)
            @php
                $gen = $entry['generation'];
                $posInGen = $index - pow(2, $gen);
                $totalInGen = pow(2, $gen);
                $angleSpan = M_PI / $totalInGen;
                // Invertir para que padre quede a la izquierda y madre a la derecha
                $startAngle = M_PI - ($posInGen + 1) * $angleSpan;
                $endAngle = $startAngle + $angleSpan;
                $innerR = $minRadius + ($gen - 1) * $ringWidth;
                $outerR = $innerR + $ringWidth;
                $midAngle = ($startAngle + $endAngle) / 2;
                $midR = ($innerR + $outerR) / 2;
                $data = $entry['data'];
                $gender = $data['gender'] ?? null;

                // Calcular cuantos caracteres caben
                $arcLength = $midR * $angleSpan;
                $maxChars = max(3, (int)($arcLength / 6));
                $name = truncateName($entry['name'], $maxChars);

                // Posicion del texto
                $textPos = polarToCartesian($centerX, $centerY, $midR, $midAngle);

                // Rotacion del texto para seguir el arco
                $textAngleDeg = -rad2deg($midAngle) + 180;
                // Corregir para que siempre sea legible (no al reves)
                if ($midAngle > M_PI / 2) {
                    $textAngleDeg = -rad2deg($midAngle);
                }

                // Fecha compacta
                $birthYear = $data['birthDate'] ?? '';
                $deathYear = $data['deathDate'] ?? '';
                $dates = '';
                if ($birthYear || $deathYear) {
                    $dates = ($birthYear ?: '?') . '-' . ($deathYear ?: ($data['isLiving'] ? e(__('v')) : '?'));
                }

                // Clase de texto segun generacion
                $textClass = 'fan-text-md';
                if ($gen >= 4) $textClass = 'fan-text-sm';
                if ($gen >= 6) $textClass = 'fan-text-sm';

                // Determinar si hay espacio para fechas
                $showDates = $gen <= 4 && $dates;
            @endphp

            <path d="{{ arcPath($centerX, $centerY, $innerR, $outerR, $startAngle, $endAngle) }}"
                  fill="{{ genderColor($gender) }}"
                  stroke="{{ genderStroke($gender) }}"
                  stroke-width="1"/>

            @if($gen <= 5)
                <text x="{{ $textPos['x'] }}" y="{{ $textPos['y'] - ($showDates ? 3 : 0) }}"
                      text-anchor="middle"
                      dominant-baseline="central"
                      transform="rotate({{ $textAngleDeg }}, {{ $textPos['x'] }}, {{ $textPos['y'] - ($showDates ? 3 : 0) }})"
                      class="fan-text {{ $textClass }}">{{ $name }}</text>
                @if($showDates)
                    <text x="{{ $textPos['x'] }}" y="{{ $textPos['y'] + 9 }}"
                          text-anchor="middle"
                          dominant-baseline="central"
                          transform="rotate({{ $textAngleDeg }}, {{ $textPos['x'] }}, {{ $textPos['y'] + 9 }})"
                          class="fan-dates">{{ $dates }}</text>
                @endif
            @endif
        @endif
    @endforeach

    <!-- Titulo -->
    <text x="{{ $centerX }}" y="{{ $height - 8 }}" text-anchor="middle" class="fan-text" style="font-size: 10px; fill: #9ca3af;">
        {{ config('app.name') }} - {{ now()->format('d/m/Y') }}
    </text>
</svg>
