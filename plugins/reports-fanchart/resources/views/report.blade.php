<x-app-layout>
    <x-slot name="title">{{ __('Grafico de Abanico') }} - {{ $person->full_name }}</x-slot>

    @push('styles')
    <style>
        #fan-chart-container {
            position: relative;
            min-height: 550px;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        #fan-chart-svg {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 550px;
            cursor: grab;
            font-family: var(--mf-font, 'Ubuntu'), Arial, sans-serif;
        }
        #fan-chart-svg:active {
            cursor: grabbing;
        }
        #fan-chart-svg .fan-arc {
            cursor: pointer;
            transition: filter 0.15s ease;
        }
        #fan-chart-svg .fan-arc:hover {
            filter: brightness(0.92);
        }
        #fan-chart-svg .fan-text {
            pointer-events: none;
            font-family: var(--mf-font, 'Ubuntu'), Arial, sans-serif;
        }
        #fan-chart-svg .fan-dates {
            pointer-events: none;
            font-family: var(--mf-font, 'Ubuntu'), Arial, sans-serif;
        }
        #fan-chart-svg .root-circle {
            cursor: pointer;
            transition: filter 0.15s ease;
        }
        #fan-chart-svg .root-circle:hover {
            filter: brightness(0.92);
        }

        /* Tooltip */
        .fan-tooltip {
            position: absolute;
            pointer-events: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.4;
            max-width: 260px;
            z-index: 50;
            opacity: 0;
            transition: opacity 0.15s ease;
            font-family: var(--mf-font, 'Ubuntu'), Arial, sans-serif;
            background-color: var(--mf-bg-card, #ffffff);
            color: var(--mf-text, #1f2937);
            border: 1px solid var(--mf-border, #e5e7eb);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .fan-tooltip.visible {
            opacity: 1;
        }
        .fan-tooltip .tooltip-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        .fan-tooltip .tooltip-detail {
            color: var(--mf-text-muted, #6b7280);
            font-size: 12px;
        }

        /* Blur intro overlay */
        .fan-blur-overlay {
            position: absolute;
            inset: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
            background: rgba(255,255,255,0.3);
            transition: opacity 0.5s ease;
            cursor: pointer;
        }
        .dark .fan-blur-overlay {
            background: rgba(0,0,0,0.3);
        }
        .fan-blur-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .fan-blur-overlay .blur-message {
            background: var(--mf-bg-card, #ffffff);
            color: var(--mf-text, #1f2937);
            border: 1px solid var(--mf-border, #e5e7eb);
            padding: 16px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Toolbar buttons */
        .fan-toolbar {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .fan-toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid var(--mf-border, #e5e7eb);
            background-color: var(--mf-bg-card, #ffffff);
            color: var(--mf-text-secondary, #4b5563);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
            white-space: nowrap;
            user-select: none;
        }
        .fan-toolbar-btn:hover {
            background-color: var(--mf-bg-hover, #f3f4f6);
        }
        .fan-toolbar-btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .fan-toolbar-btn.active {
            background-color: var(--mf-primary, #3b82f6);
            color: white;
            border-color: var(--mf-primary, #3b82f6);
        }

        /* Options panel */
        .fan-options-panel {
            transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
            max-height: 500px;
            opacity: 1;
            overflow: hidden;
        }
        .fan-options-panel.collapsed {
            max-height: 0;
            opacity: 0;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Range slider styling */
        .fan-range {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: var(--mf-border, #e5e7eb);
            outline: none;
        }
        .fan-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--mf-primary, #3b82f6);
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .fan-range::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--mf-primary, #3b82f6);
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Custom select fix - no arrow overlap */
        .fan-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 16px;
            padding-right: 30px;
        }
    </style>
    @endpush

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
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
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="card mb-4">
            <div class="card-body py-3 px-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    {{-- Left: Options toggle --}}
                    <div class="fan-toolbar">
                        <button class="fan-toolbar-btn active" id="btn-toggle-options" title="{{ __('Ocultar opciones') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            <span id="btn-toggle-options-text">{{ __('Opciones') }}</span>
                        </button>
                    </div>

                    {{-- Right: Action buttons --}}
                    <div class="fan-toolbar">
                        <a href="{{ route('persons.show', $person) }}" class="fan-toolbar-btn" title="{{ __('Ver perfil') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="hidden sm:inline">{{ __('Ver perfil') }}</span>
                        </a>
                        <button class="fan-toolbar-btn" id="btn-recenter" title="{{ __('Volver a centrar') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span class="hidden sm:inline">{{ __('Volver a centrar') }}</span>
                        </button>
                        <button class="fan-toolbar-btn" id="btn-export-png" title="{{ __('Exportar como PNG') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="hidden sm:inline">PNG</span>
                        </button>
                        <a href="{{ route('reports.fanchart.svg', ['person' => $person, 'generations' => $generations]) }}" class="fan-toolbar-btn" title="{{ __('Exportar como SVG') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="hidden sm:inline">SVG</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Options panel (collapsible) --}}
        <div class="card mb-4" id="options-card">
            <div class="card-body fan-options-panel" id="options-panel">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Fan angle --}}
                    <div>
                        <label class="form-label text-sm mb-2 flex items-center justify-between">
                            {{ __('Angulo del abanico') }}
                            <span class="text-theme-muted font-normal" id="angle-value">210°</span>
                        </label>
                        <input type="range" id="fan-angle" class="fan-range" min="90" max="360" value="210" step="10">
                    </div>

                    {{-- Generations --}}
                    <div>
                        <label for="fan-generations" class="form-label text-sm mb-2">{{ __('Generaciones') }}</label>
                        <select id="fan-generations" class="form-select fan-select text-sm w-full">
                            @for($i = 2; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Inner levels --}}
                    <div>
                        <label class="form-label text-sm mb-2 flex items-center justify-between">
                            {{ __('Niveles internos') }}
                            <span class="text-theme-muted font-normal" id="inner-levels-value">5</span>
                        </label>
                        <input type="range" id="fan-inner-levels" class="fan-range" min="0" max="10" value="5" step="1">
                        <p class="text-xs text-theme-muted mt-1">{{ __('Especifica el numero de niveles en los que se escribe el texto a lo largo de un arco') }}</p>
                    </div>

                    {{-- Font size --}}
                    <div>
                        <label class="form-label text-sm mb-2 flex items-center justify-between">
                            {{ __('Tamano de tipografia') }}
                            <span class="text-theme-muted font-normal" id="font-size-value">100%</span>
                        </label>
                        <input type="range" id="fan-font-size" class="fan-range" min="50" max="150" value="100" step="10">
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 pt-4 border-t border-theme-light">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" id="chk-hide-empty" class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                        {{ __('Ocultar segmentos vacios') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" id="chk-gradients" class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                        {{ __('Mostrar degradados de color') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" id="chk-marriage-dates" class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                        {{ __('Mostrar fecha de bodas de padres') }}
                    </label>
                </div>
            </div>
        </div>

        {{-- Fan Chart D3.js --}}
        <div class="card mb-4">
            <div class="card-body p-2 sm:p-4">
                <div id="fan-chart-container">
                    <svg id="fan-chart-svg"></svg>
                    <div class="fan-tooltip" id="fan-tooltip"></div>

                    {{-- Blur intro overlay --}}
                    <div class="fan-blur-overlay" id="fan-blur-overlay">
                        <div class="blur-message">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mf-primary, #3b82f6)">
                                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6"/><path d="M8 11h6"/>
                            </svg>
                            {{ __('Usa Ctrl+Scroll para acercar la vista') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="card">
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
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
    (function() {
        'use strict';

        // ========================================
        // Data from Laravel
        // ========================================
        const treeData = @json($fanData);
        const totalGenerations = @json((int)$generations);
        const personUrl = @json(route('persons.show', $person));
        const personName = @json($person->full_name);
        const baseUrl = @json(url('/'));
        const appName = @json(config('app.name'));
        const livingLabel = @json(__('vivo/a'));
        const marriedLabel = @json(__('casados'));
        const todayLabel = @json(now()->format('d/m/Y'));
        const svgExportBaseUrl = @json(route('reports.fanchart.svg', ['person' => $person, 'generations' => '']));

        // ========================================
        // State
        // ========================================
        let fanAngle = 210;
        let innerArcLevels = 5;
        let fontSizePercent = 100;
        let hideEmpty = false;
        let showGradients = false;
        let showMarriageDates = false;

        // ========================================
        // Dark mode detection
        // ========================================
        function isDarkMode() {
            return document.documentElement.classList.contains('dark');
        }

        // ========================================
        // Gender color palettes
        // ========================================
        function genderFill(gender, generation) {
            const dark = isDarkMode();
            if (!showGradients || generation === undefined) {
                if (gender === 'M') return dark ? '#1e3a5f' : '#dbeafe';
                if (gender === 'F') return dark ? '#4a1942' : '#fce7f3';
                return dark ? '#374151' : '#f3f4f6';
            }
            // Gradient: darken/lighten by generation
            const t = Math.min(generation / Math.max(totalGenerations, 1), 1);
            if (gender === 'M') {
                if (dark) {
                    const r = Math.round(30 + t * 20);
                    const g = Math.round(58 + t * 30);
                    const b = Math.round(95 + t * 40);
                    return `rgb(${r},${g},${b})`;
                } else {
                    const r = Math.round(219 - t * 60);
                    const g = Math.round(234 - t * 40);
                    const b = Math.round(254 - t * 20);
                    return `rgb(${r},${g},${b})`;
                }
            }
            if (gender === 'F') {
                if (dark) {
                    const r = Math.round(74 + t * 30);
                    const g = Math.round(25 + t * 20);
                    const b = Math.round(66 + t * 30);
                    return `rgb(${r},${g},${b})`;
                } else {
                    const r = Math.round(252 - t * 40);
                    const g = Math.round(231 - t * 60);
                    const b = Math.round(243 - t * 30);
                    return `rgb(${r},${g},${b})`;
                }
            }
            return dark ? '#374151' : '#f3f4f6';
        }

        function genderStroke(gender) {
            const dark = isDarkMode();
            if (gender === 'M') return dark ? '#3b82f6' : '#93c5fd';
            if (gender === 'F') return dark ? '#ec4899' : '#f9a8d4';
            return dark ? '#6b7280' : '#d1d5db';
        }

        function textColor() {
            return isDarkMode() ? '#f3f4f6' : '#1f2937';
        }

        function mutedTextColor() {
            return isDarkMode() ? '#9ca3af' : '#6b7280';
        }

        function bgColor() {
            return isDarkMode() ? '#1f2937' : '#ffffff';
        }

        // ========================================
        // Flatten tree into Ahnentafel indexed map
        // ========================================
        function flattenTree(node, index, generation, result) {
            index = index || 1;
            generation = generation || 0;
            result = result || {};

            result[index] = {
                data: node.data || node,
                name: node.name || (node.data ? node.data.name : ''),
                generation: generation,
                index: index,
                marriageDate: node.marriageDate || null,
                marriagePlace: node.marriagePlace || null
            };

            if (node.children && node.children.length > 0) {
                for (var i = 0; i < node.children.length; i++) {
                    flattenTree(node.children[i], index * 2 + i, generation + 1, result);
                }
            }

            return result;
        }

        // ========================================
        // Geometry helpers
        // ========================================
        function polarToCartesian(cx, cy, radius, angleRad) {
            return {
                x: cx + radius * Math.cos(angleRad),
                y: cy - radius * Math.sin(angleRad)
            };
        }

        function arcPath(cx, cy, innerR, outerR, startAngle, endAngle) {
            var oStart = polarToCartesian(cx, cy, outerR, startAngle);
            var oEnd   = polarToCartesian(cx, cy, outerR, endAngle);
            var iStart = polarToCartesian(cx, cy, innerR, endAngle);
            var iEnd   = polarToCartesian(cx, cy, innerR, startAngle);
            var large  = (endAngle - startAngle) > Math.PI ? 1 : 0;

            return [
                'M', oStart.x.toFixed(2), oStart.y.toFixed(2),
                'A', outerR.toFixed(2), outerR.toFixed(2), 0, large, 0, oEnd.x.toFixed(2), oEnd.y.toFixed(2),
                'L', iStart.x.toFixed(2), iStart.y.toFixed(2),
                'A', innerR.toFixed(2), innerR.toFixed(2), 0, large, 1, iEnd.x.toFixed(2), iEnd.y.toFixed(2),
                'Z'
            ].join(' ');
        }

        // Arc path for text-along-arc
        function textArcPath(cx, cy, radius, startAngle, endAngle) {
            var midAngle = (startAngle + endAngle) / 2;
            var large = (endAngle - startAngle) > Math.PI ? 1 : 0;

            // For arcs in upper half (midAngle between 0 and PI), text reads right-to-left in SVG,
            // so we draw from endAngle to startAngle (sweep=1, clockwise).
            // For arcs in lower half (midAngle < 0 or > PI), we flip to keep text upright.
            if (midAngle >= 0 && midAngle <= Math.PI) {
                var s = polarToCartesian(cx, cy, radius, endAngle);
                var e = polarToCartesian(cx, cy, radius, startAngle);
                return [
                    'M', s.x.toFixed(2), s.y.toFixed(2),
                    'A', radius.toFixed(2), radius.toFixed(2), 0, large, 1, e.x.toFixed(2), e.y.toFixed(2)
                ].join(' ');
            } else {
                // Lower half: draw from startAngle to endAngle (counterclockwise in math = clockwise SVG)
                var s = polarToCartesian(cx, cy, radius, startAngle);
                var e = polarToCartesian(cx, cy, radius, endAngle);
                return [
                    'M', s.x.toFixed(2), s.y.toFixed(2),
                    'A', radius.toFixed(2), radius.toFixed(2), 0, large, 0, e.x.toFixed(2), e.y.toFixed(2)
                ].join(' ');
            }
        }

        function truncateName(name, maxChars) {
            if (!name) return '';
            if (name.length <= maxChars) return name;
            return name.substring(0, maxChars - 1) + '\u2026';
        }

        function formatDates(data) {
            var birth = data.birthDate || '';
            var death = data.deathDate || '';
            if (!birth && !death) return '';
            var b = birth || '?';
            var d = death || (data.isLiving ? livingLabel : '?');
            return b + ' - ' + d;
        }

        // ========================================
        // SVG dimensions and layout
        // ========================================
        var svgWidth = 900;
        var svgHeight = 560;

        // ========================================
        // Build the D3 SVG
        // ========================================
        var svg = d3.select('#fan-chart-svg')
            .attr('viewBox', '0 0 ' + svgWidth + ' ' + svgHeight)
            .attr('preserveAspectRatio', 'xMidYMid meet');

        // Main group for zoom/pan
        var mainGroup = svg.append('g').attr('class', 'fan-main-group');

        // Background rect
        mainGroup.append('rect')
            .attr('width', svgWidth * 3)
            .attr('height', svgHeight * 3)
            .attr('x', -svgWidth)
            .attr('y', -svgHeight)
            .attr('fill', bgColor())
            .attr('class', 'fan-bg');

        // ========================================
        // Zoom behavior (Ctrl+Scroll only)
        // ========================================
        var zoomBehavior = d3.zoom()
            .scaleExtent([0.3, 5])
            .filter(function(event) {
                // Allow programmatic zoom (buttons)
                if (event.type === 'start' || event.type === 'zoom' || event.type === 'end') return true;
                // Allow drag/pan always
                if (event.type === 'mousedown' || event.type === 'mousemove' || event.type === 'mouseup') return true;
                if (event.type === 'touchstart' || event.type === 'touchmove' || event.type === 'touchend') return true;
                // Wheel: only with Ctrl
                if (event.type === 'wheel') {
                    return event.ctrlKey || event.metaKey;
                }
                return true;
            })
            .on('zoom', function(event) {
                chartGroup.attr('transform', event.transform);
            });

        svg.call(zoomBehavior);

        // Prevent default scroll when Ctrl+Wheel on chart
        document.getElementById('fan-chart-svg').addEventListener('wheel', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
            }
        }, { passive: false });

        // Chart group
        var chartGroup = mainGroup.append('g').attr('class', 'fan-chart-group');

        // Defs for text paths and gradients
        var defs = chartGroup.append('defs');

        // ========================================
        // Render function (called on init and option changes)
        // ========================================
        function renderChart() {
            // Clear previous chart content
            chartGroup.selectAll('g, text, path, circle, rect, image, line').remove();
            defs.selectAll('*').remove();

            var angleRad = fanAngle * Math.PI / 180;
            var startOffset = (Math.PI - angleRad) / 2; // Center the fan

            // Compute center based on angle
            var centerX = svgWidth / 2;
            var centerY;
            if (fanAngle <= 180) {
                centerY = svgHeight - 40;
            } else if (fanAngle <= 270) {
                centerY = svgHeight * 0.6;
            } else {
                centerY = svgHeight / 2;
            }

            var minRadius = 65;
            var availableRadius;
            if (fanAngle <= 180) {
                availableRadius = Math.min(svgHeight - 60, svgWidth / 2 - 20);
            } else {
                availableRadius = Math.min(svgHeight / 2 - 20, svgWidth / 2 - 20);
            }
            var maxRadius = availableRadius;
            var ringWidth = (maxRadius - minRadius) / Math.max(totalGenerations, 1);

            var fontScale = fontSizePercent / 100;

            // Flatten data
            var flatData = flattenTree(treeData);

            // Draw ancestor arcs
            var keys = Object.keys(flatData).map(Number).sort(function(a, b) { return a - b; });

            keys.forEach(function(idx) {
                var entry = flatData[idx];
                if (entry.generation === 0 || entry.generation > totalGenerations) return;

                var gen = entry.generation;
                var totalInGen = Math.pow(2, gen);
                var posInGen = idx - totalInGen;
                var angleSpan = angleRad / totalInGen;
                // Father left, mother right
                var startAngle = Math.PI - startOffset - (posInGen + 1) * angleSpan;
                var endAngle = startAngle + angleSpan;
                var innerR = minRadius + (gen - 1) * ringWidth;
                var outerR = innerR + ringWidth;
                var midAngle = (startAngle + endAngle) / 2;
                var midR = (innerR + outerR) / 2;
                var data = entry.data;
                var gender = data.gender || null;
                var url = data.url || '#';
                var hasData = !!(entry.name && entry.name.trim());

                // Skip empty segments if option enabled
                if (hideEmpty && !hasData) return;

                // Calculate max chars
                var arcLength = midR * angleSpan;
                var baseFontSize = gen <= 2 ? 11 : (gen <= 4 ? 9 : 7);
                var fontSize = baseFontSize * fontScale;
                var dateFontSize = (gen <= 2 ? 8 : 7) * fontScale;
                var charWidth = gen <= 3 ? 7 : 5.5;
                var maxChars = Math.max(3, Math.floor(arcLength / (charWidth * fontScale)));
                var name = truncateName(entry.name, maxChars);

                // Dates for inner generations
                var dates = formatDates(data);
                var showDates = gen <= 4 && dates;

                // Draw arc group
                var arcGroup = chartGroup.append('g')
                    .attr('class', 'fan-arc')
                    .attr('data-idx', idx)
                    .style('cursor', hasData ? 'pointer' : 'default');

                // Arc path
                arcGroup.append('path')
                    .attr('d', arcPath(centerX, centerY, innerR, outerR, startAngle, endAngle))
                    .attr('fill', hasData ? genderFill(gender, gen) : (isDarkMode() ? '#2d3748' : '#f9fafb'))
                    .attr('stroke', hasData ? genderStroke(gender) : (isDarkMode() ? '#4a5568' : '#e5e7eb'))
                    .attr('stroke-width', 1)
                    .attr('data-gender', gender || '')
                    .attr('data-gen', gen)
                    .attr('data-has-data', hasData ? '1' : '0');

                if (!hasData) return; // No text for empty segments

                // Decide text rendering: along arc or straight
                var useArcText = gen <= innerArcLevels;

                if (useArcText && gen <= 6 && name) {
                    // Text along arc path
                    var pathId = 'arc-text-' + idx;
                    defs.append('path')
                        .attr('id', pathId)
                        .attr('d', textArcPath(centerX, centerY, midR - (showDates ? 3 : 0), startAngle, endAngle));

                    arcGroup.append('text')
                        .attr('fill', textColor())
                        .attr('font-size', fontSize + 'px')
                        .attr('class', 'fan-text')
                        .append('textPath')
                        .attr('href', '#' + pathId)
                        .attr('startOffset', '50%')
                        .attr('text-anchor', 'middle')
                        .text(name);

                    // Dates along arc
                    if (showDates) {
                        var datePathId = 'arc-date-' + idx;
                        defs.append('path')
                            .attr('id', datePathId)
                            .attr('d', textArcPath(centerX, centerY, midR + fontSize * 0.6, startAngle, endAngle));

                        var dateMaxChars = Math.max(3, Math.floor(arcLength / (dateFontSize * 0.65)));
                        arcGroup.append('text')
                            .attr('fill', mutedTextColor())
                            .attr('font-size', dateFontSize + 'px')
                            .attr('class', 'fan-dates')
                            .append('textPath')
                            .attr('href', '#' + datePathId)
                            .attr('startOffset', '50%')
                            .attr('text-anchor', 'middle')
                            .text(truncateName(dates, dateMaxChars));
                    }
                } else if (gen <= 6 && name) {
                    // Straight text (rotated)
                    var textPos = polarToCartesian(centerX, centerY, midR, midAngle);
                    var textAngleDeg = -midAngle * (180 / Math.PI) + 180;
                    if (midAngle > Math.PI / 2) {
                        textAngleDeg = -midAngle * (180 / Math.PI);
                    }

                    var nameYOffset = showDates ? -3 : 0;
                    arcGroup.append('text')
                        .attr('x', textPos.x)
                        .attr('y', textPos.y + nameYOffset)
                        .attr('text-anchor', 'middle')
                        .attr('dominant-baseline', 'central')
                        .attr('transform', 'rotate(' + textAngleDeg + ',' + textPos.x + ',' + (textPos.y + nameYOffset) + ')')
                        .attr('fill', textColor())
                        .attr('font-size', fontSize + 'px')
                        .attr('class', 'fan-text')
                        .text(name);

                    if (showDates) {
                        var dateMaxChars2 = Math.max(3, Math.floor(arcLength / (dateFontSize * 0.65)));
                        var dateText = truncateName(dates, dateMaxChars2);
                        var dateYPos = textPos.y + fontSize - 1;
                        arcGroup.append('text')
                            .attr('x', textPos.x)
                            .attr('y', dateYPos)
                            .attr('text-anchor', 'middle')
                            .attr('dominant-baseline', 'central')
                            .attr('transform', 'rotate(' + textAngleDeg + ',' + textPos.x + ',' + dateYPos + ')')
                            .attr('fill', mutedTextColor())
                            .attr('font-size', dateFontSize + 'px')
                            .attr('class', 'fan-dates')
                            .text(dateText);
                    }
                }

                // Tooltip and click
                arcGroup
                    .on('mouseenter', function(event) { showTooltip(event, data, entry.name, entry.marriageDate); })
                    .on('mousemove', function(event) { moveTooltip(event); })
                    .on('mouseleave', function() { hideTooltip(); })
                    .on('click', function() {
                        if (url && url !== '#') window.location.href = url;
                    });
            });

            // ========================================
            // Draw marriage date markers between parent pairs
            // ========================================
            if (showMarriageDates) {
                keys.forEach(function(idx) {
                    var entry = flatData[idx];
                    // Marriage date appears at even indices (father), connecting father+mother pair
                    if (entry.generation === 0 || entry.generation > totalGenerations) return;
                    if (!entry.marriageDate) return;
                    // Only show on the root person's node (idx=1) since marriageDate is parents' marriage
                    // Actually marriageDate is on each person meaning *their parents'* marriage
                    // We want to show it between parent pairs: show on even-indexed persons
                    if (idx % 2 !== 0) return; // Only on "father" (even) entries

                    var gen = entry.generation;
                    var totalInGen = Math.pow(2, gen);
                    var posInGen = idx - totalInGen;
                    var angleSpan = angleRad / totalInGen;
                    var startAngle = Math.PI - startOffset - (posInGen + 1) * angleSpan;
                    var endAngle = startAngle + angleSpan;
                    // The marriage marker goes between this arc and next (mother)
                    var innerR = minRadius + (gen - 1) * ringWidth;
                    var markerAngle = endAngle; // Border between father and mother
                    var markerR = innerR + ringWidth * 0.5;

                    var pos = polarToCartesian(centerX, centerY, markerR, markerAngle);
                    var markerFontSize = Math.max(6, 7 * fontScale);

                    chartGroup.append('text')
                        .attr('x', pos.x)
                        .attr('y', pos.y)
                        .attr('text-anchor', 'middle')
                        .attr('dominant-baseline', 'central')
                        .attr('fill', isDarkMode() ? '#fbbf24' : '#b45309')
                        .attr('font-size', markerFontSize + 'px')
                        .attr('font-weight', '600')
                        .attr('class', 'fan-text marriage-marker')
                        .text('\u2665 ' + entry.marriageDate);
                });
            }

            // ========================================
            // Draw root person (center circle)
            // ========================================
            if (flatData[1]) {
                var rootEntry = flatData[1];
                var rootData = rootEntry.data;
                var rootGender = rootData.gender || null;
                var rootUrl = rootData.url || '#';
                var rootPhoto = rootData.photo || null;
                var rootName = rootEntry.name || '';
                var rootDates = formatDates(rootData);

                var rootGroup = chartGroup.append('g')
                    .attr('class', 'root-circle')
                    .style('cursor', 'pointer');

                var clipId = 'root-clip-' + Date.now();
                defs.append('clipPath')
                    .attr('id', clipId)
                    .append('circle')
                    .attr('cx', centerX)
                    .attr('cy', centerY)
                    .attr('r', minRadius - 2);

                rootGroup.append('circle')
                    .attr('cx', centerX)
                    .attr('cy', centerY)
                    .attr('r', minRadius)
                    .attr('fill', genderFill(rootGender, 0))
                    .attr('stroke', genderStroke(rootGender))
                    .attr('stroke-width', 2)
                    .attr('data-gender', rootGender || '');

                if (rootPhoto) {
                    rootGroup.append('image')
                        .attr('x', centerX - minRadius + 2)
                        .attr('y', centerY - minRadius + 2)
                        .attr('width', (minRadius - 2) * 2)
                        .attr('height', (minRadius - 2) * 2)
                        .attr('href', rootPhoto)
                        .attr('clip-path', 'url(#' + clipId + ')')
                        .attr('preserveAspectRatio', 'xMidYMid slice');

                    rootGroup.append('rect')
                        .attr('x', centerX - minRadius)
                        .attr('y', centerY + minRadius * 0.2)
                        .attr('width', minRadius * 2)
                        .attr('height', minRadius * 0.8)
                        .attr('fill', isDarkMode() ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.75)')
                        .attr('clip-path', 'url(#' + clipId + ')');
                } else {
                    var initial = rootName ? rootName.charAt(0).toUpperCase() : '?';
                    rootGroup.append('text')
                        .attr('x', centerX)
                        .attr('y', centerY - 16)
                        .attr('text-anchor', 'middle')
                        .attr('dominant-baseline', 'central')
                        .attr('fill', mutedTextColor())
                        .attr('font-size', (28 * fontScale) + 'px')
                        .attr('font-weight', '300')
                        .attr('class', 'fan-text')
                        .text(initial);
                }

                var rootNameTruncated = truncateName(rootName, 18);
                var rootNameY = rootPhoto ? (centerY + minRadius * 0.45) : (centerY + 4);
                rootGroup.append('text')
                    .attr('x', centerX)
                    .attr('y', rootNameY)
                    .attr('text-anchor', 'middle')
                    .attr('dominant-baseline', 'central')
                    .attr('fill', rootPhoto ? (isDarkMode() ? '#f3f4f6' : '#1f2937') : textColor())
                    .attr('font-size', (11 * fontScale) + 'px')
                    .attr('font-weight', '600')
                    .attr('class', 'fan-text')
                    .text(rootNameTruncated);

                if (rootDates) {
                    var rootDatesY = rootNameY + 14 * fontScale;
                    rootGroup.append('text')
                        .attr('x', centerX)
                        .attr('y', rootDatesY)
                        .attr('text-anchor', 'middle')
                        .attr('dominant-baseline', 'central')
                        .attr('fill', rootPhoto ? (isDarkMode() ? '#d1d5db' : '#4b5563') : mutedTextColor())
                        .attr('font-size', (9 * fontScale) + 'px')
                        .attr('class', 'fan-dates')
                        .text(rootDates);
                }

                rootGroup
                    .on('mouseenter', function(event) { showTooltip(event, rootData, rootName, rootEntry.marriageDate); })
                    .on('mousemove', function(event) { moveTooltip(event); })
                    .on('mouseleave', function() { hideTooltip(); })
                    .on('click', function() {
                        if (rootUrl && rootUrl !== '#') window.location.href = rootUrl;
                    });
            }

            // Footer
            chartGroup.append('text')
                .attr('x', svgWidth / 2)
                .attr('y', svgHeight - 8)
                .attr('text-anchor', 'middle')
                .attr('fill', mutedTextColor())
                .attr('font-size', '10px')
                .attr('class', 'fan-text')
                .text(appName + ' - ' + todayLabel);
        }

        // Initial render
        renderChart();

        // ========================================
        // Tooltip logic
        // ========================================
        var tooltipEl = document.getElementById('fan-tooltip');
        var containerEl = document.getElementById('fan-chart-container');

        function showTooltip(event, data, name, marriageDate) {
            var birthPlace = data.birthPlace || '';
            var dates = formatDates(data);

            var html = '<div class="tooltip-name">' + escapeHtml(name) + '</div>';
            if (dates) {
                html += '<div class="tooltip-detail">' + escapeHtml(dates) + '</div>';
            }
            if (birthPlace) {
                html += '<div class="tooltip-detail">' + escapeHtml(birthPlace) + '</div>';
            }
            if (marriageDate) {
                html += '<div class="tooltip-detail">\u2665 ' + marriedLabel + ' ' + escapeHtml(marriageDate) + '</div>';
            }

            tooltipEl.innerHTML = html;
            tooltipEl.classList.add('visible');
            moveTooltip(event);
        }

        function moveTooltip(event) {
            var rect = containerEl.getBoundingClientRect();
            var x = event.clientX - rect.left + 14;
            var y = event.clientY - rect.top - 10;

            var tw = tooltipEl.offsetWidth;
            var th = tooltipEl.offsetHeight;
            if (x + tw > rect.width - 8) {
                x = event.clientX - rect.left - tw - 14;
            }
            if (y + th > rect.height - 8) {
                y = rect.height - th - 8;
            }
            if (y < 8) y = 8;

            tooltipEl.style.left = x + 'px';
            tooltipEl.style.top = y + 'px';
        }

        function hideTooltip() {
            tooltipEl.classList.remove('visible');
        }

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // ========================================
        // Blur intro overlay
        // ========================================
        var blurOverlay = document.getElementById('fan-blur-overlay');

        function dismissBlur() {
            blurOverlay.classList.add('hidden');
            setTimeout(function() { blurOverlay.style.display = 'none'; }, 500);
        }

        // Dismiss on click
        blurOverlay.addEventListener('click', dismissBlur);

        // Dismiss on Ctrl+Scroll
        blurOverlay.addEventListener('wheel', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                dismissBlur();
            }
        }, { passive: false });

        // Auto-dismiss after 4 seconds
        setTimeout(dismissBlur, 4000);

        // ========================================
        // Options panel toggle
        // ========================================
        var toggleBtn = document.getElementById('btn-toggle-options');
        var toggleText = document.getElementById('btn-toggle-options-text');
        var optionsPanel = document.getElementById('options-panel');
        var optionsCard = document.getElementById('options-card');

        toggleBtn.addEventListener('click', function() {
            var isCollapsed = optionsPanel.classList.toggle('collapsed');
            toggleBtn.classList.toggle('active', !isCollapsed);
            if (isCollapsed) {
                optionsCard.style.marginBottom = '0';
                optionsCard.style.overflow = 'hidden';
                setTimeout(function() { optionsCard.style.display = 'none'; }, 300);
            } else {
                optionsCard.style.display = '';
                optionsCard.style.marginBottom = '';
                optionsCard.style.overflow = '';
            }
        });

        // ========================================
        // Fan angle slider
        // ========================================
        var angleSlider = document.getElementById('fan-angle');
        var angleValue = document.getElementById('angle-value');

        angleSlider.addEventListener('input', function() {
            fanAngle = parseInt(this.value);
            angleValue.textContent = fanAngle + '°';
        });
        angleSlider.addEventListener('change', function() {
            renderChart();
        });

        // ========================================
        // Generations selector (reload page)
        // ========================================
        document.getElementById('fan-generations').addEventListener('change', function() {
            var gen = this.value;
            var url = new URL(window.location.href);
            url.searchParams.set('generations', gen);
            window.location.href = url.toString();
        });

        // ========================================
        // Inner levels slider
        // ========================================
        var innerLevelsSlider = document.getElementById('fan-inner-levels');
        var innerLevelsValue = document.getElementById('inner-levels-value');

        innerLevelsSlider.addEventListener('input', function() {
            innerArcLevels = parseInt(this.value);
            innerLevelsValue.textContent = innerArcLevels;
        });
        innerLevelsSlider.addEventListener('change', function() {
            renderChart();
        });

        // ========================================
        // Font size slider
        // ========================================
        var fontSizeSlider = document.getElementById('fan-font-size');
        var fontSizeValue = document.getElementById('font-size-value');

        fontSizeSlider.addEventListener('input', function() {
            fontSizePercent = parseInt(this.value);
            fontSizeValue.textContent = fontSizePercent + '%';
        });
        fontSizeSlider.addEventListener('change', function() {
            renderChart();
        });

        // ========================================
        // Checkboxes
        // ========================================
        document.getElementById('chk-hide-empty').addEventListener('change', function() {
            hideEmpty = this.checked;
            renderChart();
        });
        document.getElementById('chk-gradients').addEventListener('change', function() {
            showGradients = this.checked;
            renderChart();
        });
        document.getElementById('chk-marriage-dates').addEventListener('change', function() {
            showMarriageDates = this.checked;
            renderChart();
        });

        // ========================================
        // Recenter button
        // ========================================
        document.getElementById('btn-recenter').addEventListener('click', function() {
            svg.transition().duration(400).call(zoomBehavior.transform, d3.zoomIdentity);
        });

        // ========================================
        // PNG Export
        // ========================================
        document.getElementById('btn-export-png').addEventListener('click', function() {
            // Reset zoom/pan to identity before export
            svg.call(zoomBehavior.transform, d3.zoomIdentity);

            var svgEl = document.getElementById('fan-chart-svg');
            var svgData = new XMLSerializer().serializeToString(svgEl);

            // Ensure xmlns is present (XMLSerializer already adds it, but guard against edge cases)
            if (svgData.indexOf('xmlns="http://www.w3.org/2000/svg"') === -1) {
                svgData = svgData.replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"');
            }

            var canvas = document.createElement('canvas');
            var scale = 2; // 2x for retina
            canvas.width = svgWidth * scale;
            canvas.height = svgHeight * scale;
            var ctx = canvas.getContext('2d');
            ctx.scale(scale, scale);

            // Fill background
            ctx.fillStyle = bgColor();
            ctx.fillRect(0, 0, svgWidth, svgHeight);

            function downloadPng() {
                try {
                    var a = document.createElement('a');
                    a.download = personName + ' - ' + appName + '.png';
                    a.href = canvas.toDataURL('image/png');
                    a.click();
                } catch (e) {
                    // Canvas tainted by cross-origin image
                    alert('No se pudo exportar la imagen. La foto del perfil puede ser de un origen externo.');
                }
            }

            var img = new Image();
            img.crossOrigin = 'anonymous';
            var blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            var url = URL.createObjectURL(blob);

            img.onload = function() {
                ctx.drawImage(img, 0, 0, svgWidth, svgHeight);
                URL.revokeObjectURL(url);
                downloadPng();
            };
            img.onerror = function() {
                URL.revokeObjectURL(url);
                // Fallback: try with data URI
                var encoded = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
                var img2 = new Image();
                img2.crossOrigin = 'anonymous';
                img2.onload = function() {
                    ctx.drawImage(img2, 0, 0, svgWidth, svgHeight);
                    downloadPng();
                };
                img2.src = encoded;
            };
            img.src = url;
        });

        // ========================================
        // Dark mode observer
        // ========================================
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    renderChart();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });

    })();
    </script>
    @endpush
</x-app-layout>
