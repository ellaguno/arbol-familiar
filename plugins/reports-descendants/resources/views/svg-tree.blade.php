@php
    /**
     * SVG del arbol de descendientes para inclusion en PDF.
     * Dibuja un arbol vertical simple con nodos rectangulares y lineas de conexion.
     */
    $nodeWidth = 140;
    $nodeHeight = 36;
    $horizontalGap = 20;
    $verticalGap = 50;

    // Construir nodos posicionados desde el flatList
    $nodes = [];
    $levelCounts = [];
    $levelPositions = [];

    // Contar nodos por nivel (solo personas, no conyuges)
    foreach ($flatList as $entry) {
        if ($entry['type'] === 'person') {
            $level = $entry['level'];
            if (!isset($levelCounts[$level])) {
                $levelCounts[$level] = 0;
            }
            $levelCounts[$level]++;
        }
    }

    // Calcular ancho maximo necesario
    $maxNodesInLevel = max($levelCounts ?: [1]);
    $svgWidth = max(600, $maxNodesInLevel * ($nodeWidth + $horizontalGap) + 40);

    // Posicionar nodos
    $levelIndex = [];
    foreach ($flatList as $idx => $entry) {
        if ($entry['type'] !== 'person') continue;

        $level = $entry['level'];
        if (!isset($levelIndex[$level])) {
            $levelIndex[$level] = 0;
        }

        $nodesInLevel = $levelCounts[$level];
        $totalWidth = $nodesInLevel * ($nodeWidth + $horizontalGap) - $horizontalGap;
        $startX = ($svgWidth - $totalWidth) / 2;

        $x = $startX + $levelIndex[$level] * ($nodeWidth + $horizontalGap);
        $y = 30 + $level * ($nodeHeight + $verticalGap);

        $p = $entry['person'];
        $isProtected = $p->shouldProtectMinorData();
        $name = $isProtected ? $p->first_name : $p->full_name;
        $gender = $p->gender;

        $dates = '';
        if (!$isProtected) {
            $birthYear = $p->birth_year ?? ($p->birth_date?->format('Y'));
            $deathYear = $p->death_year ?? ($p->death_date?->format('Y'));
            if ($birthYear || $deathYear) {
                $b = $birthYear ?: '?';
                $d = $deathYear ?: ($p->is_living ? e(__('v')) : '?');
                $dates = $b . ' - ' . $d;
            }
        }

        $nodes[$idx] = [
            'x' => $x,
            'y' => $y,
            'centerX' => $x + $nodeWidth / 2,
            'centerY' => $y + $nodeHeight / 2,
            'name' => $name,
            'dates' => $dates,
            'gender' => $gender,
            'level' => $level,
            'personId' => $p->id,
        ];

        $levelIndex[$level]++;
    }

    // Encontrar conyuges y asociarlos al nodo persona
    $spousesByPerson = [];
    foreach ($flatList as $entry) {
        if ($entry['type'] === 'spouse') {
            $p = $entry['person'];
            $isProtected = $p->shouldProtectMinorData();
            $spousesByPerson[] = [
                'name' => $isProtected ? $p->first_name : $p->full_name,
                'gender' => $p->gender,
                'marriageYear' => $entry['marriageYear'] ?? null,
                'level' => $entry['level'],
            ];
        }
    }

    // Calcular altura total
    $maxLevel = max(array_keys($levelCounts));
    $svgHeight = 30 + ($maxLevel + 1) * ($nodeHeight + $verticalGap) + 40;

    // Limitar tamaño razonable para PDF
    $svgWidth = min($svgWidth, 1100);
    $svgHeight = min($svgHeight, 800);

    // Colores por genero
    if (!function_exists('descGenderColors')) {
        function descGenderColors($gender) {
            return match($gender) {
                'M' => ['fill' => '#dbeafe', 'stroke' => '#3b82f6'],
                'F' => ['fill' => '#fce7f3', 'stroke' => '#ec4899'],
                default => ['fill' => '#f3f4f6', 'stroke' => '#9ca3af'],
            };
        }
    }

    if (!function_exists('descTruncate')) {
        function descTruncate($text, $maxChars = 20) {
            if (!$text) return '';
            return mb_strlen($text) > $maxChars ? mb_substr($text, 0, $maxChars - 1) . '...' : $text;
        }
    }
@endphp
<svg xmlns="http://www.w3.org/2000/svg"
     width="{{ $svgWidth }}"
     height="{{ $svgHeight }}"
     viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}">

    <defs>
        <style>
            .desc-name { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; font-weight: bold; fill: #1f2937; }
            .desc-dates { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8px; fill: #6b7280; }
            .desc-line { stroke: #d1d5db; stroke-width: 1.5; fill: none; }
            .desc-footer { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8px; fill: #9ca3af; }
        </style>
    </defs>

    <rect width="{{ $svgWidth }}" height="{{ $svgHeight }}" fill="white"/>

    {{-- Lineas de conexion padre-hijo --}}
    @php
        // Reconstruir relaciones padre-hijo a partir del flatList
        $personNodes = [];
        $parentStack = [];
        foreach ($flatList as $idx => $entry) {
            if ($entry['type'] !== 'person') continue;
            if (!isset($nodes[$idx])) continue;

            $level = $entry['level'];
            $personNodes[$level][] = $nodes[$idx];

            // Encontrar padre (ultimo nodo persona en nivel - 1)
            if ($level > 0 && isset($personNodes[$level - 1])) {
                $parentNode = end($personNodes[$level - 1]);
                if ($parentNode) {
                    $childNode = $nodes[$idx];
                    // Linea vertical desde padre
                    $midY = ($parentNode['y'] + $parentNode['centerY'] + $nodeHeight / 2 + $childNode['y']) / 2;
                    echo sprintf(
                        '<path class="desc-line" d="M %.1f %.1f V %.1f H %.1f V %.1f"/>',
                        $parentNode['centerX'], $parentNode['y'] + $nodeHeight,
                        $midY,
                        $childNode['centerX'],
                        $childNode['y']
                    );
                }
            }
        }
    @endphp

    {{-- Nodos persona --}}
    @foreach($nodes as $node)
        @php $colors = descGenderColors($node['gender']); @endphp
        <rect x="{{ $node['x'] }}" y="{{ $node['y'] }}"
              width="{{ $nodeWidth }}" height="{{ $nodeHeight }}"
              rx="4" ry="4"
              fill="{{ $colors['fill'] }}" stroke="{{ $colors['stroke'] }}" stroke-width="1.5"/>

        <text x="{{ $node['centerX'] }}" y="{{ $node['y'] + 15 }}"
              text-anchor="middle" class="desc-name">{{ descTruncate($node['name']) }}</text>

        @if($node['dates'])
            <text x="{{ $node['centerX'] }}" y="{{ $node['y'] + 28 }}"
                  text-anchor="middle" class="desc-dates">{{ $node['dates'] }}</text>
        @endif
    @endforeach

    {{-- Pie de pagina --}}
    <text x="{{ $svgWidth / 2 }}" y="{{ $svgHeight - 10 }}"
          text-anchor="middle" class="desc-footer">
        {{ config('app.name') }} - {{ now()->format('d/m/Y') }}
    </text>
</svg>
