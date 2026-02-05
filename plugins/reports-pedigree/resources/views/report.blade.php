<x-app-layout>
    <x-slot name="title">{{ __('Cuadro de Pedigri') }} - {{ $person->full_name }}</x-slot>

    @push('styles')
    <style>
        #pedigree-tooltip {
            position: fixed;
            pointer-events: none;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            font-family: var(--mf-font, 'Ubuntu'), sans-serif;
            font-size: 13px;
            color: #1f2937;
            z-index: 9999;
            max-width: 280px;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        .dark #pedigree-tooltip {
            background: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        #pedigree-tooltip.visible {
            opacity: 1;
        }
        #pedigree-tooltip .tt-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
        }
        #pedigree-tooltip .tt-dates {
            font-size: 12px;
            color: #6b7280;
        }
        .dark #pedigree-tooltip .tt-dates {
            color: #9ca3af;
        }
        #pedigree-tooltip .tt-gender {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }
        #pedigree-container {
            min-height: 500px;
            overflow: hidden;
            position: relative;
            cursor: grab;
        }
        #pedigree-container:active {
            cursor: grabbing;
        }
        #pedigree-container svg {
            display: block;
            width: 100%;
            height: 100%;
        }
        .pedigree-zoom-controls {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            z-index: 10;
        }
        .pedigree-zoom-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            font-size: 18px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, border-color 0.15s;
        }
        .pedigree-zoom-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        .dark .pedigree-zoom-btn {
            background: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }
        .dark .pedigree-zoom-btn:hover {
            background: #4b5563;
            border-color: #6b7280;
        }
        .pedigree-node rect {
            transition: filter 0.15s ease, stroke-width 0.15s ease;
        }
        .pedigree-node:hover rect {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.18));
        }
        .pedigree-node {
            cursor: pointer;
        }
    </style>
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <li><span class="text-theme-secondary font-medium">{{ __('Cuadro de Pedigri') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Cuadro de Pedigri') }}</h1>
                <p class="text-theme-muted mt-1">{{ __(':count ancestros encontrados en :gen generaciones', ['count' => $totalFound - 1, 'gen' => $generations]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.pedigree.svg', ['person' => $person, 'generations' => $generations]) }}"
                   class="btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Descargar SVG') }}
                </a>
                <a href="{{ route('reports.pedigree.pdf', ['person' => $person, 'generations' => $generations]) }}"
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

        {{-- Generation selector --}}
        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.pedigree', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        {{-- D3 Pedigree Chart --}}
        <div class="card">
            <div class="card-body p-0">
                <div id="pedigree-container">
                    <div class="pedigree-zoom-controls">
                        <button class="pedigree-zoom-btn" id="zoom-in" title="{{ __('Acercar') }}">+</button>
                        <button class="pedigree-zoom-btn" id="zoom-out" title="{{ __('Alejar') }}">&minus;</button>
                        <button class="pedigree-zoom-btn" id="zoom-reset" title="{{ __('Restablecer') }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tooltip --}}
        <div id="pedigree-tooltip">
            <div class="tt-name"></div>
            <div class="tt-dates"></div>
            <div class="tt-gender"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
    (function() {
        'use strict';

        // --- Data & configuration ---
        const treeData = @json($fanData);
        const generations = {{ $generations }};
        const isDark = document.documentElement.classList.contains('dark');

        const BOX_W = 200;
        const BOX_H = 75;
        const BOX_RX = 8;
        const PHOTO_R = 14;
        const NODE_V_SPACING = 90;
        const NODE_H_SPACING = 220;
        const FONT_FAMILY = "var(--mf-font, 'Ubuntu'), sans-serif";

        // Gender color palettes
        const COLORS = {
            light: {
                M: { fill: '#dbeafe', stroke: '#93c5fd' },
                F: { fill: '#fce7f3', stroke: '#f9a8d4' },
                U: { fill: '#f3f4f6', stroke: '#d1d5db' }
            },
            dark: {
                M: { fill: '#1e3a5f', stroke: '#3b82f6' },
                F: { fill: '#4a1942', stroke: '#ec4899' },
                U: { fill: '#374151', stroke: '#6b7280' }
            }
        };

        const palette = isDark ? COLORS.dark : COLORS.light;

        function genderColors(gender) {
            if (gender === 'M') return palette.M;
            if (gender === 'F') return palette.F;
            return palette.U;
        }

        function truncate(str, max) {
            if (!str) return '';
            return str.length > max ? str.substring(0, max - 1) + '\u2026' : str;
        }

        function formatDates(d) {
            if (!d) return '';
            const b = d.birthDate || '';
            const dd = d.deathDate || '';
            if (!b && !dd) return '';
            const birth = b || '?';
            const death = dd || (d.isLiving ? '{{ __("vivo/a") }}' : '?');
            return birth + ' \u2013 ' + death;
        }

        // --- Container setup ---
        const container = document.getElementById('pedigree-container');
        const containerRect = container.getBoundingClientRect();
        const width = container.clientWidth || 900;
        const height = Math.max(500, window.innerHeight - 300);

        container.style.height = height + 'px';

        // --- SVG creation ---
        const svg = d3.select('#pedigree-container')
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g');

        // --- Zoom behavior ---
        const zoom = d3.zoom()
            .scaleExtent([0.3, 3])
            .on('zoom', function(event) {
                g.attr('transform', event.transform);
            });

        svg.call(zoom);

        // Zoom control buttons
        d3.select('#zoom-in').on('click', function() {
            svg.transition().duration(300).call(zoom.scaleBy, 1.3);
        });
        d3.select('#zoom-out').on('click', function() {
            svg.transition().duration(300).call(zoom.scaleBy, 0.7);
        });
        d3.select('#zoom-reset').on('click', function() {
            svg.transition().duration(500).call(zoom.transform, initialTransform);
        });

        // --- Build hierarchy ---
        const root = d3.hierarchy(treeData, function(d) {
            if (!d.children) return null;
            // Filter out null children but keep structure
            const kids = d.children.filter(function(c) { return c !== null; });
            return kids.length > 0 ? kids : null;
        });

        // --- Tree layout (horizontal: x = vertical position, y = horizontal depth) ---
        const treeLayout = d3.tree()
            .nodeSize([NODE_V_SPACING, NODE_H_SPACING])
            .separation(function(a, b) {
                return a.parent === b.parent ? 1 : 1.2;
            });

        treeLayout(root);

        // --- Compute bounds for centering ---
        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        root.each(function(d) {
            if (d.x < minX) minX = d.x;
            if (d.x > maxX) maxX = d.x;
            if (d.y < minY) minY = d.y;
            if (d.y > maxY) maxY = d.y;
        });

        const treeW = (maxY - minY) + BOX_W + 60;
        const treeH = (maxX - minX) + BOX_H + 60;

        // Scale to fit, with some padding
        const scaleX = width / treeW;
        const scaleY = height / treeH;
        const scale = Math.min(scaleX, scaleY, 1) * 0.9;

        const centerX = width / 2;
        const centerY = height / 2;
        const treeCenterY = (minY + maxY) / 2;
        const treeCenterX = (minX + maxX) / 2;

        const tx = centerX - treeCenterY * scale;
        const ty = centerY - treeCenterX * scale;

        const initialTransform = d3.zoomIdentity.translate(tx, ty).scale(scale);
        svg.call(zoom.transform, initialTransform);

        // --- Links (horizontal bezier curves) ---
        const linkGenerator = d3.linkHorizontal()
            .x(function(d) { return d.y; })
            .y(function(d) { return d.x; });

        g.selectAll('.pedigree-link')
            .data(root.links())
            .join('path')
            .attr('class', 'pedigree-link')
            .attr('d', function(d) {
                return linkGenerator({
                    source: { x: d.source.x, y: d.source.y + BOX_W },
                    target: { x: d.target.x, y: d.target.y }
                });
            })
            .attr('fill', 'none')
            .attr('stroke', isDark ? '#4b5563' : '#9ca3af')
            .attr('stroke-width', 2);

        // --- Tooltip ---
        const tooltip = d3.select('#pedigree-tooltip');
        const ttName = tooltip.select('.tt-name');
        const ttDates = tooltip.select('.tt-dates');
        const ttGender = tooltip.select('.tt-gender');

        function showTooltip(event, d) {
            const info = d.data.data;
            if (!info) return;

            ttName.text(info.name || '');
            ttDates.text(formatDates(info));

            const gLabel = info.gender === 'M' ? '{{ __("Masculino") }}'
                         : info.gender === 'F' ? '{{ __("Femenino") }}'
                         : '{{ __("Desconocido") }}';
            ttGender.text(gLabel);

            tooltip.classed('visible', true);
            positionTooltip(event);
        }

        function positionTooltip(event) {
            const tx = event.pageX + 14;
            const ty = event.pageY - 10;
            tooltip.style('left', tx + 'px').style('top', ty + 'px');
        }

        function hideTooltip() {
            tooltip.classed('visible', false);
        }

        // --- Nodes ---
        const nodes = g.selectAll('.pedigree-node')
            .data(root.descendants())
            .join('g')
            .attr('class', 'pedigree-node')
            .attr('transform', function(d) {
                return 'translate(' + d.y + ',' + (d.x - BOX_H / 2) + ')';
            })
            .on('mouseenter', function(event, d) {
                showTooltip(event, d);
            })
            .on('mousemove', function(event) {
                positionTooltip(event);
            })
            .on('mouseleave', function() {
                hideTooltip();
            })
            .on('click', function(event, d) {
                const url = d.data.data ? d.data.data.url : null;
                if (url) {
                    window.location.href = url;
                }
            });

        // Node background rectangle
        nodes.append('rect')
            .attr('width', BOX_W)
            .attr('height', BOX_H)
            .attr('rx', BOX_RX)
            .attr('ry', BOX_RX)
            .attr('fill', function(d) {
                const g = d.data.data ? d.data.data.gender : null;
                return genderColors(g).fill;
            })
            .attr('stroke', function(d) {
                const info = d.data.data;
                if (!info) return genderColors(null).stroke;
                // Root node gets accent-colored thicker border
                if (d.depth === 0) {
                    return isDark ? '#3b82f6' : 'var(--mf-primary, #3b82f6)';
                }
                return genderColors(info.gender).stroke;
            })
            .attr('stroke-width', function(d) {
                return d.depth === 0 ? 3 : 1.5;
            });

        // Clip path for photos (one per node)
        const defs = svg.append('defs');
        nodes.each(function(d, i) {
            if (d.data.data && d.data.data.photo) {
                defs.append('clipPath')
                    .attr('id', 'photo-clip-' + i)
                    .append('circle')
                    .attr('cx', 0)
                    .attr('cy', 0)
                    .attr('r', PHOTO_R);
            }
        });

        // Photo circle
        nodes.each(function(d, i) {
            const info = d.data.data;
            if (!info || !info.photo) return;

            const node = d3.select(this);
            const cx = 24;
            const cy = BOX_H / 2;

            // Photo border circle
            node.append('circle')
                .attr('cx', cx)
                .attr('cy', cy)
                .attr('r', PHOTO_R + 1)
                .attr('fill', 'none')
                .attr('stroke', isDark ? '#4b5563' : '#d1d5db')
                .attr('stroke-width', 1);

            // Photo image
            node.append('image')
                .attr('href', info.photo)
                .attr('x', cx - PHOTO_R)
                .attr('y', cy - PHOTO_R)
                .attr('width', PHOTO_R * 2)
                .attr('height', PHOTO_R * 2)
                .attr('clip-path', 'url(#photo-clip-' + i + ')')
                .attr('preserveAspectRatio', 'xMidYMid slice');
        });

        // Name text
        nodes.append('text')
            .attr('x', function(d) {
                const info = d.data.data;
                return (info && info.photo) ? 46 : 14;
            })
            .attr('y', function() {
                return BOX_H / 2 - 6;
            })
            .attr('font-family', FONT_FAMILY)
            .attr('font-size', '13px')
            .attr('font-weight', '700')
            .attr('fill', isDark ? '#f3f4f6' : '#1f2937')
            .text(function(d) {
                const info = d.data.data;
                if (!info) return truncate(d.data.name, 22);
                const maxChars = info.photo ? 18 : 22;
                return truncate(info.name || d.data.name, maxChars);
            });

        // Dates text
        nodes.append('text')
            .attr('x', function(d) {
                const info = d.data.data;
                return (info && info.photo) ? 46 : 14;
            })
            .attr('y', function() {
                return BOX_H / 2 + 10;
            })
            .attr('font-family', FONT_FAMILY)
            .attr('font-size', '10px')
            .attr('fill', isDark ? '#9ca3af' : '#6b7280')
            .text(function(d) {
                if (!d.data.data) return '';
                const dates = formatDates(d.data.data);
                return truncate(dates, 28);
            });

        // Gender indicator (small circle)
        nodes.append('circle')
            .attr('cx', BOX_W - 14)
            .attr('cy', 14)
            .attr('r', 5)
            .attr('fill', function(d) {
                const info = d.data.data;
                if (!info) return genderColors(null).stroke;
                return genderColors(info.gender).stroke;
            })
            .attr('opacity', 0.7);

        // Gender symbol in the indicator
        nodes.append('text')
            .attr('x', BOX_W - 14)
            .attr('y', 17)
            .attr('text-anchor', 'middle')
            .attr('font-size', '9px')
            .attr('fill', isDark ? '#e5e7eb' : '#374151')
            .text(function(d) {
                const info = d.data.data;
                if (!info) return '';
                if (info.gender === 'M') return '\u2642';
                if (info.gender === 'F') return '\u2640';
                return '?';
            });

        // --- Observe dark mode changes (MutationObserver) ---
        const htmlEl = document.documentElement;
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.attributeName === 'class') {
                    // Reload the page to re-render with correct colors
                    window.location.reload();
                }
            });
        });
        observer.observe(htmlEl, { attributes: true });

    })();
    </script>
    @endpush
</x-app-layout>
