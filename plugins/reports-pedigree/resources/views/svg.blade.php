@php
    $boxWidth = 160;
    $boxHeight = 50;
    $horizontalGap = 40;
    $baseVerticalGap = 10;

    $totalSlots = pow(2, $generations);
    $slotHeight = $boxHeight + $baseVerticalGap;
    $svgHeight = $totalSlots * $slotHeight;
    $svgWidth = ($generations + 1) * ($boxWidth + $horizontalGap) + 20;

    // Position each person based on Ahnentafel number
    // Number 1 = root (gen 0), 2-3 = gen 1, 4-7 = gen 2, etc.
    $positions = [];
    foreach ($ahnentafel as $num => $entry) {
        $gen = $entry['generation'];
        $x = $gen * ($boxWidth + $horizontalGap) + 10;

        // For generation N, there are 2^N slots
        // The slot index within generation is: num - 2^gen
        $slotsInGen = pow(2, $gen);
        $slotIndex = $num - $slotsInGen;

        // Each slot takes up totalSlots / slotsInGen vertical units
        $unitsPerSlot = $totalSlots / $slotsInGen;
        $y = ($slotIndex + 0.5) * $unitsPerSlot * $slotHeight - $boxHeight / 2;

        $positions[$num] = [
            'x' => $x,
            'y' => $y,
            'centerX' => $x + $boxWidth / 2,
            'centerY' => $y + $boxHeight / 2,
            'entry' => $entry,
        ];
    }

    // Color scheme by gender
    if (!function_exists('pedigreeBoxColors')) {
        function pedigreeBoxColors($gender) {
            return match($gender) {
                'M' => ['fill' => '#dbeafe', 'stroke' => '#3b82f6'],
                'F' => ['fill' => '#fce7f3', 'stroke' => '#ec4899'],
                default => ['fill' => '#f3f4f6', 'stroke' => '#9ca3af'],
            };
        }
    }

    // Truncate text to fit box
    if (!function_exists('pedigreeTruncate')) {
        function pedigreeTruncate($text, $maxChars = 22) {
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
            .pedigree-name { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; font-weight: bold; fill: #1f2937; }
            .pedigree-dates { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; fill: #6b7280; }
            .pedigree-line { stroke: #d1d5db; stroke-width: 1.5; fill: none; }
            .pedigree-num { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 7px; opacity: 0.6; }
        </style>
    </defs>

    <!-- Background -->
    <rect width="{{ $svgWidth }}" height="{{ $svgHeight }}" fill="white"/>

    <!-- Connector lines -->
    @foreach($positions as $num => $pos)
        @php
            // Draw line from this person to father (num*2) and mother (num*2+1)
            $fatherNum = $num * 2;
            $motherNum = $num * 2 + 1;
        @endphp

        @if(isset($positions[$fatherNum]))
            @php
                $parentPos = $positions[$fatherNum];
                $startX = $pos['x'] + $boxWidth;
                $startY = $pos['centerY'];
                $midX = $startX + $horizontalGap / 2;
                $endX = $parentPos['x'];
                $endY = $parentPos['centerY'];
            @endphp
            <path class="pedigree-line"
                  d="M {{ $startX }} {{ $startY }} H {{ $midX }} V {{ $endY }} H {{ $endX }}" />
        @endif

        @if(isset($positions[$motherNum]))
            @php
                $parentPos = $positions[$motherNum];
                $startX = $pos['x'] + $boxWidth;
                $startY = $pos['centerY'];
                $midX = $startX + $horizontalGap / 2;
                $endX = $parentPos['x'];
                $endY = $parentPos['centerY'];
            @endphp
            <path class="pedigree-line"
                  d="M {{ $startX }} {{ $startY }} H {{ $midX }} V {{ $endY }} H {{ $endX }}" />
        @endif
    @endforeach

    <!-- Person boxes -->
    @foreach($positions as $num => $pos)
        @php
            $entry = $pos['entry'];
            $colors = pedigreeBoxColors($entry['gender']);
            $name = pedigreeTruncate($entry['name']);
            $dates = '';
            if ($entry['birth_year']) {
                $dates = $entry['birth_year'];
                if (!$entry['is_living'] && $entry['death_year']) {
                    $dates .= ' - ' . $entry['death_year'];
                } elseif ($entry['is_living']) {
                    $dates .= ' - ';
                }
            } elseif ($entry['is_living']) {
                $dates = __('Vivo/a');
            }
        @endphp

        <rect x="{{ $pos['x'] }}" y="{{ $pos['y'] }}"
              width="{{ $boxWidth }}" height="{{ $boxHeight }}"
              rx="4" ry="4"
              fill="{{ $colors['fill'] }}" stroke="{{ $colors['stroke'] }}" stroke-width="1.5"/>

        <text x="{{ $pos['x'] + $boxWidth / 2 }}" y="{{ $pos['y'] + 20 }}"
              text-anchor="middle" class="pedigree-name">{{ $name }}</text>

        <text x="{{ $pos['x'] + $boxWidth / 2 }}" y="{{ $pos['y'] + 35 }}"
              text-anchor="middle" class="pedigree-dates">{{ $dates }}</text>

        {{-- Ahnentafel number indicator --}}
        <text x="{{ $pos['x'] + 6 }}" y="{{ $pos['y'] + $boxHeight - 5 }}"
              class="pedigree-num" fill="{{ $colors['stroke'] }}">{{ $num }}</text>
    @endforeach
</svg>
