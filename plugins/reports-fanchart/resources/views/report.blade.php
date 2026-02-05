<x-app-layout>
    <x-slot name="title">{{ __('Grafico de Abanico') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <li><span class="text-theme-secondary font-medium">{{ __('Grafico de Abanico') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Grafico de Abanico') }}</h1>
                <p class="text-theme-muted mt-1">{{ __('Diagrama semicircular de ancestros - :gen generaciones', ['gen' => $generations]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.fanchart.svg', ['person' => $person, 'generations' => $generations]) }}"
                   class="btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Descargar SVG') }}
                </a>
                <a href="{{ route('reports.fanchart.pdf', ['person' => $person, 'generations' => $generations]) }}"
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
                <form method="GET" action="{{ route('reports.fanchart', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Grafico de Abanico -->
        <div class="card">
            <div class="card-body p-4">
                <div class="overflow-x-auto" id="fan-chart-container">
                    @php
                        $width = 800;
                        $height = 450;
                        $centerX = $width / 2;
                        $centerY = $height - 30;
                        $maxRadius = 380;
                        $minRadius = 60;

                        function flattenFanDataHtml($node, $index = 1, $generation = 0, &$result = []) {
                            $result[$index] = [
                                'data' => $node['data'] ?? $node,
                                'name' => $node['name'] ?? ($node['data']['name'] ?? ''),
                                'generation' => $generation,
                                'index' => $index,
                            ];
                            if (!empty($node['children'])) {
                                foreach ($node['children'] as $i => $child) {
                                    flattenFanDataHtml($child, $index * 2 + $i, $generation + 1, $result);
                                }
                            }
                            return $result;
                        }

                        function polarToCartesianHtml($cx, $cy, $radius, $angleRad) {
                            return [
                                'x' => $cx + $radius * cos($angleRad),
                                'y' => $cy - $radius * sin($angleRad),
                            ];
                        }

                        function arcPathHtml($cx, $cy, $innerR, $outerR, $startAngle, $endAngle) {
                            $outerStart = polarToCartesianHtml($cx, $cy, $outerR, $startAngle);
                            $outerEnd = polarToCartesianHtml($cx, $cy, $outerR, $endAngle);
                            $innerStart = polarToCartesianHtml($cx, $cy, $innerR, $endAngle);
                            $innerEnd = polarToCartesianHtml($cx, $cy, $innerR, $startAngle);
                            $largeArc = ($endAngle - $startAngle) > M_PI ? 1 : 0;

                            return sprintf(
                                'M %.2f %.2f A %.2f %.2f 0 %d 0 %.2f %.2f L %.2f %.2f A %.2f %.2f 0 %d 1 %.2f %.2f Z',
                                $outerStart['x'], $outerStart['y'],
                                $outerR, $outerR, $largeArc, $outerEnd['x'], $outerEnd['y'],
                                $innerStart['x'], $innerStart['y'],
                                $innerR, $innerR, $largeArc, $innerEnd['x'], $innerEnd['y']
                            );
                        }

                        function truncateNameHtml($name, $maxChars) {
                            if (mb_strlen($name) <= $maxChars) {
                                return $name;
                            }
                            return mb_substr($name, 0, $maxChars - 1) . "\u{2026}";
                        }

                        function genderColorHtml($gender) {
                            return match ($gender) {
                                'M' => '#dbeafe',
                                'F' => '#fce7f3',
                                default => '#f3f4f6',
                            };
                        }

                        function genderStrokeHtml($gender) {
                            return match ($gender) {
                                'M' => '#93c5fd',
                                'F' => '#f9a8d4',
                                default => '#d1d5db',
                            };
                        }

                        function genderHoverHtml($gender) {
                            return match ($gender) {
                                'M' => '#bfdbfe',
                                'F' => '#fbcfe8',
                                default => '#e5e7eb',
                            };
                        }

                        $flatData = flattenFanDataHtml($fanData);
                        $totalGenerations = $generations;
                        $ringWidth = ($maxRadius - $minRadius) / max($totalGenerations, 1);
                    @endphp

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $width }} {{ $height }}" class="w-full max-w-4xl mx-auto" id="fan-chart-svg">
                        <defs>
                            <style>
                                .fan-text { font-family: var(--mf-font, 'Ubuntu'), Arial, sans-serif; fill: #1f2937; }
                                .fan-text-sm { font-size: 9px; }
                                .fan-text-md { font-size: 11px; }
                                .fan-text-lg { font-size: 13px; }
                                .fan-text-root { font-size: 11px; font-weight: bold; }
                                .fan-dates { font-size: 7px; fill: #6b7280; }
                                .fan-arc { cursor: pointer; transition: opacity 0.2s; }
                                .fan-arc:hover { opacity: 0.8; }
                            </style>
                        </defs>

                        <rect width="{{ $width }}" height="{{ $height }}" fill="white"/>

                        {{-- Persona raiz --}}
                        @if(isset($flatData[1]))
                            @php
                                $rootData = $flatData[1]['data'];
                                $rootGender = $rootData['gender'] ?? null;
                                $rootUrl = $rootData['url'] ?? '#';
                            @endphp
                            <a href="{{ $rootUrl }}" class="fan-arc">
                                <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="{{ $minRadius }}" fill="{{ genderColorHtml($rootGender) }}" stroke="{{ genderStrokeHtml($rootGender) }}" stroke-width="1.5"/>
                                <text x="{{ $centerX }}" y="{{ $centerY - 12 }}" text-anchor="middle" class="fan-text fan-text-root">{{ truncateNameHtml($flatData[1]['name'], 20) }}</text>
                                @php
                                    $rootBirth = $rootData['birthDate'] ?? '';
                                    $rootDeath = $rootData['deathDate'] ?? '';
                                    $rootDates = '';
                                    if ($rootBirth || $rootDeath) {
                                        $rootDates = ($rootBirth ?: '?') . ' - ' . ($rootDeath ?: ($rootData['isLiving'] ? __('vivo/a') : '?'));
                                    }
                                @endphp
                                @if($rootDates)
                                    <text x="{{ $centerX }}" y="{{ $centerY + 4 }}" text-anchor="middle" class="fan-dates" style="font-size: 9px;">{{ $rootDates }}</text>
                                @endif
                            </a>
                        @endif

                        {{-- Arcos de ancestros --}}
                        @foreach($flatData as $index => $entry)
                            @if($entry['generation'] > 0 && $entry['generation'] <= $totalGenerations)
                                @php
                                    $gen = $entry['generation'];
                                    $posInGen = $index - pow(2, $gen);
                                    $totalInGen = pow(2, $gen);
                                    $angleSpan = M_PI / $totalInGen;
                                    $startAngle = M_PI - ($posInGen + 1) * $angleSpan;
                                    $endAngle = $startAngle + $angleSpan;
                                    $innerR = $minRadius + ($gen - 1) * $ringWidth;
                                    $outerR = $innerR + $ringWidth;
                                    $midAngle = ($startAngle + $endAngle) / 2;
                                    $midR = ($innerR + $outerR) / 2;
                                    $data = $entry['data'];
                                    $gender = $data['gender'] ?? null;
                                    $personUrl = $data['url'] ?? '#';

                                    $arcLength = $midR * $angleSpan;
                                    $maxChars = max(3, (int)($arcLength / 6));
                                    $name = truncateNameHtml($entry['name'], $maxChars);

                                    $textPos = polarToCartesianHtml($centerX, $centerY, $midR, $midAngle);

                                    $textAngleDeg = -rad2deg($midAngle) + 180;
                                    if ($midAngle > M_PI / 2) {
                                        $textAngleDeg = -rad2deg($midAngle);
                                    }

                                    $birthYear = $data['birthDate'] ?? '';
                                    $deathYear = $data['deathDate'] ?? '';
                                    $dates = '';
                                    if ($birthYear || $deathYear) {
                                        $dates = ($birthYear ?: '?') . '-' . ($deathYear ?: ($data['isLiving'] ? __('v') : '?'));
                                    }

                                    $textClass = 'fan-text-md';
                                    if ($gen >= 4) $textClass = 'fan-text-sm';

                                    $showDates = $gen <= 4 && $dates;
                                @endphp

                                <a href="{{ $personUrl }}" class="fan-arc">
                                    <path d="{{ arcPathHtml($centerX, $centerY, $innerR, $outerR, $startAngle, $endAngle) }}"
                                          fill="{{ genderColorHtml($gender) }}"
                                          stroke="{{ genderStrokeHtml($gender) }}"
                                          stroke-width="1"
                                          data-name="{{ $entry['name'] }}"
                                          data-gender="{{ $gender }}"/>

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
                                </a>
                            @endif
                        @endforeach

                        {{-- Titulo --}}
                        <text x="{{ $centerX }}" y="{{ $height - 8 }}" text-anchor="middle" class="fan-text" style="font-size: 10px; fill: #9ca3af;">
                            {{ config('app.name') }} - {{ now()->format('d/m/Y') }}
                        </text>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Leyenda -->
        <div class="card mt-4">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-theme mb-3">{{ __('Leyenda') }}</h3>
                <div class="flex flex-wrap gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded" style="background-color: #dbeafe; border: 1px solid #93c5fd;"></span>
                        <span class="text-theme-muted">{{ __('Masculino') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded" style="background-color: #fce7f3; border: 1px solid #f9a8d4;"></span>
                        <span class="text-theme-muted">{{ __('Femenino') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded" style="background-color: #f3f4f6; border: 1px solid #d1d5db;"></span>
                        <span class="text-theme-muted">{{ __('Desconocido') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tooltip al pasar sobre un arco
        document.querySelectorAll('.fan-arc path[data-name]').forEach(function(path) {
            path.addEventListener('mouseenter', function(e) {
                var name = this.getAttribute('data-name');
                if (name) {
                    this.style.opacity = '0.7';
                }
            });
            path.addEventListener('mouseleave', function(e) {
                this.style.opacity = '1';
            });
        });
    </script>
    @endpush
</x-app-layout>
