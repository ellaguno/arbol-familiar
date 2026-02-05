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

        /* Zoom controls */
        .zoom-controls {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            z-index: 10;
        }
        .zoom-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid var(--mf-border, #e5e7eb);
            background-color: var(--mf-bg-card, #ffffff);
            color: var(--mf-text-secondary, #4b5563);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease;
            line-height: 1;
            user-select: none;
        }
        .zoom-btn:hover {
            background-color: var(--mf-bg-hover, #f3f4f6);
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

        {{-- Selector de generaciones --}}
        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.fanchart', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        {{-- Grafico de Abanico D3.js --}}
        <div class="card mb-4">
            <div class="card-body p-2 sm:p-4">
                <div id="fan-chart-container">
                    <svg id="fan-chart-svg"></svg>
                    <div class="fan-tooltip" id="fan-tooltip"></div>
                    <div class="zoom-controls">
                        <button class="zoom-btn" id="zoom-in" title="{{ __('Acercar') }}">+</button>
                        <button class="zoom-btn" id="zoom-out" title="{{ __('Alejar') }}">&minus;</button>
                        <button class="zoom-btn" id="zoom-reset" title="{{ __('Restablecer') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Leyenda --}}
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
        const appName = @json(config('app.name'));
        const livingLabel = @json(__('vivo/a'));
        const todayLabel = @json(now()->format('d/m/Y'));

        // ========================================
        // Dark mode detection
        // ========================================
        function isDarkMode() {
            return document.documentElement.classList.contains('dark');
        }

        // ========================================
        // Gender color palettes
        // ========================================
        function genderFill(gender) {
            const dark = isDarkMode();
            if (gender === 'M') return dark ? '#1e3a5f' : '#dbeafe';
            if (gender === 'F') return dark ? '#4a1942' : '#fce7f3';
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
                index: index
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
        var svgHeight = 520;
        var centerX = svgWidth / 2;
        var centerY = svgHeight - 40;
        var minRadius = 65;
        var maxRadius = Math.min(svgHeight - 60, svgWidth / 2 - 20);
        var ringWidth = (maxRadius - minRadius) / Math.max(totalGenerations, 1);

        // ========================================
        // Build the D3 SVG
        // ========================================
        var svg = d3.select('#fan-chart-svg')
            .attr('viewBox', '0 0 ' + svgWidth + ' ' + svgHeight)
            .attr('preserveAspectRatio', 'xMidYMax meet');

        // Main group for zoom/pan
        var mainGroup = svg.append('g').attr('class', 'fan-main-group');

        // Background rect (for drag target and color)
        mainGroup.append('rect')
            .attr('width', svgWidth)
            .attr('height', svgHeight)
            .attr('fill', bgColor())
            .attr('class', 'fan-bg');

        // ========================================
        // Zoom behavior
        // ========================================
        var zoomBehavior = d3.zoom()
            .scaleExtent([0.3, 5])
            .on('zoom', function(event) {
                chartGroup.attr('transform', event.transform);
            });

        svg.call(zoomBehavior);

        // Chart group (holds all chart elements, transformed by zoom)
        var chartGroup = mainGroup.append('g').attr('class', 'fan-chart-group');

        // ========================================
        // Flatten data
        // ========================================
        var flatData = flattenTree(treeData);

        // ========================================
        // Draw ancestor arcs (generations 1+)
        // ========================================
        var keys = Object.keys(flatData).map(Number).sort(function(a, b) { return a - b; });

        keys.forEach(function(idx) {
            var entry = flatData[idx];
            if (entry.generation === 0 || entry.generation > totalGenerations) return;

            var gen = entry.generation;
            var totalInGen = Math.pow(2, gen);
            var posInGen = idx - totalInGen;
            var angleSpan = Math.PI / totalInGen;
            var startAngle = Math.PI - (posInGen + 1) * angleSpan;
            var endAngle = startAngle + angleSpan;
            var innerR = minRadius + (gen - 1) * ringWidth;
            var outerR = innerR + ringWidth;
            var midAngle = (startAngle + endAngle) / 2;
            var midR = (innerR + outerR) / 2;
            var data = entry.data;
            var gender = data.gender || null;
            var url = data.url || '#';

            // Calculate max chars that fit in arc
            var arcLength = midR * angleSpan;
            var charWidth = gen <= 3 ? 7 : 5.5;
            var maxChars = Math.max(3, Math.floor(arcLength / charWidth));
            var name = truncateName(entry.name, maxChars);

            // Dates for inner generations
            var dates = formatDates(data);
            var showDates = gen <= 4 && dates;

            // Font sizes by generation
            var fontSize = gen <= 2 ? 11 : (gen <= 4 ? 9 : 7);
            var dateFontSize = gen <= 2 ? 8 : 7;

            // Draw arc group
            var arcGroup = chartGroup.append('g')
                .attr('class', 'fan-arc')
                .style('cursor', 'pointer');

            // Arc path
            arcGroup.append('path')
                .attr('d', arcPath(centerX, centerY, innerR, outerR, startAngle, endAngle))
                .attr('fill', genderFill(gender))
                .attr('stroke', genderStroke(gender))
                .attr('stroke-width', 1);

            // Text position
            var textPos = polarToCartesian(centerX, centerY, midR, midAngle);
            var textAngleDeg = -midAngle * (180 / Math.PI) + 180;
            if (midAngle > Math.PI / 2) {
                textAngleDeg = -midAngle * (180 / Math.PI);
            }

            // Name text (show for gen <= 6)
            if (gen <= 6 && name) {
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
            }

            // Date text
            if (showDates) {
                var dateMaxChars = Math.max(3, Math.floor(arcLength / (dateFontSize * 0.65)));
                var dateText = truncateName(dates, dateMaxChars);
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

            // Tooltip and click events
            arcGroup
                .on('mouseenter', function(event) {
                    showTooltip(event, data, entry.name);
                })
                .on('mousemove', function(event) {
                    moveTooltip(event);
                })
                .on('mouseleave', function() {
                    hideTooltip();
                })
                .on('click', function() {
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                });
        });

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

            // Clip path for photo
            var clipId = 'root-clip-' + Date.now();
            chartGroup.append('defs').append('clipPath')
                .attr('id', clipId)
                .append('circle')
                .attr('cx', centerX)
                .attr('cy', centerY)
                .attr('r', minRadius - 2);

            // Background circle
            rootGroup.append('circle')
                .attr('cx', centerX)
                .attr('cy', centerY)
                .attr('r', minRadius)
                .attr('fill', genderFill(rootGender))
                .attr('stroke', genderStroke(rootGender))
                .attr('stroke-width', 2);

            // Photo or initial
            if (rootPhoto) {
                rootGroup.append('image')
                    .attr('x', centerX - minRadius + 2)
                    .attr('y', centerY - minRadius + 2)
                    .attr('width', (minRadius - 2) * 2)
                    .attr('height', (minRadius - 2) * 2)
                    .attr('href', rootPhoto)
                    .attr('clip-path', 'url(#' + clipId + ')')
                    .attr('preserveAspectRatio', 'xMidYMid slice');

                // Semi-transparent overlay at bottom for text
                rootGroup.append('rect')
                    .attr('x', centerX - minRadius)
                    .attr('y', centerY + minRadius * 0.2)
                    .attr('width', minRadius * 2)
                    .attr('height', minRadius * 0.8)
                    .attr('fill', isDarkMode() ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.75)')
                    .attr('clip-path', 'url(#' + clipId + ')');
            } else {
                // Initial letter
                var initial = rootName ? rootName.charAt(0).toUpperCase() : '?';
                rootGroup.append('text')
                    .attr('x', centerX)
                    .attr('y', centerY - 16)
                    .attr('text-anchor', 'middle')
                    .attr('dominant-baseline', 'central')
                    .attr('fill', mutedTextColor())
                    .attr('font-size', '28px')
                    .attr('font-weight', '300')
                    .attr('class', 'fan-text')
                    .text(initial);
            }

            // Root name
            var rootNameTruncated = truncateName(rootName, 18);
            var rootNameY = rootPhoto ? (centerY + minRadius * 0.45) : (centerY + 4);
            rootGroup.append('text')
                .attr('x', centerX)
                .attr('y', rootNameY)
                .attr('text-anchor', 'middle')
                .attr('dominant-baseline', 'central')
                .attr('fill', rootPhoto ? (isDarkMode() ? '#f3f4f6' : '#1f2937') : textColor())
                .attr('font-size', '11px')
                .attr('font-weight', '600')
                .attr('class', 'fan-text')
                .text(rootNameTruncated);

            // Root dates
            if (rootDates) {
                var rootDatesY = rootNameY + 14;
                rootGroup.append('text')
                    .attr('x', centerX)
                    .attr('y', rootDatesY)
                    .attr('text-anchor', 'middle')
                    .attr('dominant-baseline', 'central')
                    .attr('fill', rootPhoto ? (isDarkMode() ? '#d1d5db' : '#4b5563') : mutedTextColor())
                    .attr('font-size', '9px')
                    .attr('class', 'fan-dates')
                    .text(rootDates);
            }

            // Root click and tooltip
            rootGroup
                .on('mouseenter', function(event) {
                    showTooltip(event, rootData, rootName);
                })
                .on('mousemove', function(event) {
                    moveTooltip(event);
                })
                .on('mouseleave', function() {
                    hideTooltip();
                })
                .on('click', function() {
                    if (rootUrl && rootUrl !== '#') {
                        window.location.href = rootUrl;
                    }
                });
        }

        // ========================================
        // Footer text
        // ========================================
        chartGroup.append('text')
            .attr('x', centerX)
            .attr('y', svgHeight - 8)
            .attr('text-anchor', 'middle')
            .attr('fill', mutedTextColor())
            .attr('font-size', '10px')
            .attr('class', 'fan-text')
            .text(appName + ' - ' + todayLabel);

        // ========================================
        // Tooltip logic
        // ========================================
        var tooltipEl = document.getElementById('fan-tooltip');
        var containerEl = document.getElementById('fan-chart-container');

        function showTooltip(event, data, name) {
            var birthPlace = data.birthPlace || '';
            var dates = formatDates(data);

            var html = '<div class="tooltip-name">' + escapeHtml(name) + '</div>';
            if (dates) {
                html += '<div class="tooltip-detail">' + escapeHtml(dates) + '</div>';
            }
            if (birthPlace) {
                html += '<div class="tooltip-detail">' + escapeHtml(birthPlace) + '</div>';
            }

            tooltipEl.innerHTML = html;
            tooltipEl.classList.add('visible');
            moveTooltip(event);
        }

        function moveTooltip(event) {
            var rect = containerEl.getBoundingClientRect();
            var x = event.clientX - rect.left + 14;
            var y = event.clientY - rect.top - 10;

            // Keep tooltip within container bounds
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
        // Zoom control buttons
        // ========================================
        var zoomInBtn = document.getElementById('zoom-in');
        var zoomOutBtn = document.getElementById('zoom-out');
        var zoomResetBtn = document.getElementById('zoom-reset');

        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', function() {
                svg.transition().duration(300).call(zoomBehavior.scaleBy, 1.3);
            });
        }

        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', function() {
                svg.transition().duration(300).call(zoomBehavior.scaleBy, 0.7);
            });
        }

        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', function() {
                svg.transition().duration(400).call(zoomBehavior.transform, d3.zoomIdentity);
            });
        }

        // ========================================
        // Observe dark mode changes and re-color
        // ========================================
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    updateColors();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });

        function updateColors() {
            // Background
            d3.select('.fan-bg').attr('fill', bgColor());

            // Re-color ancestor arcs using stored data-gender attribute
            chartGroup.selectAll('.fan-arc path').each(function() {
                var el = d3.select(this);
                var gender = el.attr('data-gender');
                if (gender !== null) {
                    el.attr('fill', genderFill(gender))
                      .attr('stroke', genderStroke(gender));
                }
            });

            chartGroup.selectAll('.fan-text').attr('fill', textColor());
            chartGroup.selectAll('.fan-dates').attr('fill', mutedTextColor());

            // Root circle
            chartGroup.selectAll('.root-circle circle').each(function() {
                var el = d3.select(this);
                var gender = el.attr('data-gender');
                if (gender !== null) {
                    el.attr('fill', genderFill(gender))
                      .attr('stroke', genderStroke(gender));
                }
            });

            // Footer
            chartGroup.selectAll('text').filter(function() {
                return d3.select(this).text().indexOf(appName) !== -1;
            }).attr('fill', mutedTextColor());
        }

        // ========================================
        // Store gender data attributes for dark mode re-coloring
        // ========================================
        (function addGenderAttributes() {
            // Set data-gender on ancestor arcs
            var arcGroups = chartGroup.selectAll('.fan-arc').nodes();
            var arcIndex = 0;

            keys.forEach(function(idx) {
                var entry = flatData[idx];
                if (entry.generation === 0 || entry.generation > totalGenerations) return;
                if (arcIndex < arcGroups.length) {
                    var group = d3.select(arcGroups[arcIndex]);
                    var path = group.select('path');
                    if (!path.empty()) {
                        path.attr('data-gender', entry.data.gender || '');
                    }
                    arcIndex++;
                }
            });

            // Root circle
            if (flatData[1]) {
                chartGroup.selectAll('.root-circle circle')
                    .attr('data-gender', flatData[1].data.gender || '');
            }
        })();

    })();
    </script>
    @endpush
</x-app-layout>
