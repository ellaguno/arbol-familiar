<x-app-layout>
    <x-slot name="title">{{ __('Reporte de Descendientes') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <li><span class="text-theme-secondary font-medium">{{ __('Reporte de Descendientes') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Reporte de Descendientes') }}</h1>
                <p class="text-theme-muted mt-1">{{ __('Arbol interactivo - :count descendientes encontrados', ['count' => $totalDescendants]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.descendants.pdf', ['person' => $person, 'generations' => $generations]) }}"
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
                <form method="GET" action="{{ route('reports.descendants', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Arbol D3.js interactivo -->
        <div class="card">
            <div class="card-body p-0 relative">
                {{-- Zoom controls --}}
                <div class="absolute top-3 right-3 z-10 flex flex-col gap-1">
                    <button id="zoom-in" class="w-8 h-8 rounded-lg bg-theme-card border border-theme shadow-sm flex items-center justify-center text-theme hover:bg-theme-hover transition-colors" title="{{ __('Acercar') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                    </button>
                    <button id="zoom-out" class="w-8 h-8 rounded-lg bg-theme-card border border-theme shadow-sm flex items-center justify-center text-theme hover:bg-theme-hover transition-colors" title="{{ __('Alejar') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/></svg>
                    </button>
                    <button id="zoom-reset" class="w-8 h-8 rounded-lg bg-theme-card border border-theme shadow-sm flex items-center justify-center text-theme hover:bg-theme-hover transition-colors" title="{{ __('Restablecer zoom') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </button>
                    <button id="expand-all" class="w-8 h-8 rounded-lg bg-theme-card border border-theme shadow-sm flex items-center justify-center text-theme hover:bg-theme-hover transition-colors" title="{{ __('Expandir todo') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8l8 8 8-8"/></svg>
                    </button>
                    <button id="collapse-all" class="w-8 h-8 rounded-lg bg-theme-card border border-theme shadow-sm flex items-center justify-center text-theme hover:bg-theme-hover transition-colors" title="{{ __('Colapsar todo') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l8-8 8 8"/></svg>
                    </button>
                </div>

                {{-- Legend --}}
                <div class="absolute bottom-3 left-3 z-10 flex flex-wrap gap-3 text-xs text-theme-muted bg-theme-card bg-opacity-90 rounded-lg px-3 py-2 border border-theme shadow-sm">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#dbeafe;border:1px solid #3b82f6;"></span> {{ __('Masculino') }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#fce7f3;border:1px solid #ec4899;"></span> {{ __('Femenino') }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#f3f4f6;border:1px solid #9ca3af;"></span> {{ __('Otro') }}</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" viewBox="0 0 12 12"><line x1="0" y1="6" x2="12" y2="6" stroke="#9ca3af" stroke-width="2" stroke-dasharray="3,2"/></svg>
                        {{ __('Conyuge') }}
                    </span>
                    <span class="flex items-center gap-1">{{ __('Clic: expandir/colapsar') }}</span>
                    <span class="flex items-center gap-1">{{ __('Doble clic: ver perfil') }}</span>
                </div>

                {{-- D3 Chart container --}}
                <div id="descendant-chart" style="min-height: 500px; width: 100%; overflow: hidden;"></div>

                {{-- Tooltip --}}
                <div id="chart-tooltip" class="fixed z-50 pointer-events-none opacity-0 transition-opacity duration-200 bg-theme-card border border-theme rounded-lg shadow-xl px-4 py-3 text-sm max-w-xs" style="display:none;">
                    <div id="tooltip-name" class="font-bold text-theme mb-1"></div>
                    <div id="tooltip-dates" class="text-theme-muted text-xs mb-1"></div>
                    <div id="tooltip-spouses" class="text-theme-muted text-xs"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- D3.js v7 --}}
    <script src="https://d3js.org/d3.v7.min.js"></script>

    <script>
    (function() {
        'use strict';

        // ============================================================
        // Data & Configuration
        // ============================================================
        const treeData = @json($descendantTree);
        const INITIAL_EXPAND_DEPTH = 3;
        const DURATION = 750;
        const NODE_WIDTH = 170;
        const NODE_HEIGHT = 65;
        const SPOUSE_WIDTH = 150;
        const SPOUSE_HEIGHT = 50;
        const SPOUSE_GAP = 15;
        const HORIZONTAL_SPACING = 220;
        const VERTICAL_SPACING = 120;

        // Detect dark mode
        function isDarkMode() {
            return document.documentElement.classList.contains('dark');
        }

        // Color scheme by gender
        function genderColors(gender) {
            const dark = isDarkMode();
            switch (gender) {
                case 'M':
                    return {
                        fill: dark ? '#1e3a5f' : '#dbeafe',
                        stroke: dark ? '#60a5fa' : '#3b82f6',
                        text: dark ? '#bfdbfe' : '#1e3a8a',
                        subtext: dark ? '#93c5fd' : '#3b82f6'
                    };
                case 'F':
                    return {
                        fill: dark ? '#4a1942' : '#fce7f3',
                        stroke: dark ? '#f472b6' : '#ec4899',
                        text: dark ? '#fbcfe8' : '#831843',
                        subtext: dark ? '#f9a8d4' : '#ec4899'
                    };
                default:
                    return {
                        fill: dark ? '#374151' : '#f3f4f6',
                        stroke: dark ? '#6b7280' : '#9ca3af',
                        text: dark ? '#d1d5db' : '#374151',
                        subtext: dark ? '#9ca3af' : '#6b7280'
                    };
            }
        }

        function linkColor() {
            return isDarkMode() ? '#4b5563' : '#d1d5db';
        }

        function badgeBg() {
            return isDarkMode() ? '#4b5563' : '#e5e7eb';
        }

        function badgeText() {
            return isDarkMode() ? '#d1d5db' : '#374151';
        }

        function spouseLinkColor() {
            return isDarkMode() ? '#6b7280' : '#9ca3af';
        }

        // ============================================================
        // Container setup
        // ============================================================
        const container = document.getElementById('descendant-chart');
        const containerRect = container.getBoundingClientRect();
        const width = container.clientWidth || 900;
        const height = Math.max(500, window.innerHeight - 300);

        container.style.height = height + 'px';

        const svg = d3.select('#descendant-chart')
            .append('svg')
            .attr('width', '100%')
            .attr('height', '100%')
            .attr('viewBox', [0, 0, width, height])
            .style('font-family', 'var(--mf-font, Ubuntu, sans-serif)');

        // Zoom group
        const g = svg.append('g')
            .attr('class', 'chart-group');

        // Arrow marker for spouse lines
        svg.append('defs').append('marker')
            .attr('id', 'spouse-marker')
            .attr('viewBox', '0 0 10 10')
            .attr('refX', 5)
            .attr('refY', 5)
            .attr('markerWidth', 6)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('circle')
            .attr('cx', 5)
            .attr('cy', 5)
            .attr('r', 3)
            .attr('fill', spouseLinkColor());

        // ============================================================
        // Zoom behavior
        // ============================================================
        const zoomBehavior = d3.zoom()
            .scaleExtent([0.1, 3])
            .on('zoom', (event) => {
                g.attr('transform', event.transform);
            });

        svg.call(zoomBehavior);

        // Zoom controls
        document.getElementById('zoom-in').addEventListener('click', () => {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 1.3);
        });
        document.getElementById('zoom-out').addEventListener('click', () => {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 0.7);
        });
        document.getElementById('zoom-reset').addEventListener('click', () => {
            centerTree();
        });
        document.getElementById('expand-all').addEventListener('click', () => {
            expandAll(root);
            update(root);
        });
        document.getElementById('collapse-all').addEventListener('click', () => {
            collapseAllExceptRoot(root);
            update(root);
        });

        // ============================================================
        // D3 Hierarchy & Tree Layout
        // ============================================================
        const root = d3.hierarchy(treeData, d => d.children);
        root.x0 = 0;
        root.y0 = 0;

        // Tree layout (top to bottom: x = horizontal, y = vertical)
        const treeLayout = d3.tree()
            .nodeSize([HORIZONTAL_SPACING, VERTICAL_SPACING])
            .separation((a, b) => {
                // Add extra separation when nodes have spouses
                const aSpouses = (a.data._spouses && a.data._spouses.length) ? a.data._spouses.length : 0;
                const bSpouses = (b.data._spouses && b.data._spouses.length) ? b.data._spouses.length : 0;
                const extra = Math.max(aSpouses, bSpouses) * 0.4;
                return (a.parent === b.parent ? 1.0 : 1.2) + extra;
            });

        // Collapse nodes deeper than initial depth
        function collapseAfterDepth(node, maxDepth) {
            if (node.children) {
                if (node.depth >= maxDepth) {
                    node._children = node.children;
                    node.children = null;
                    // Recursively collapse hidden children too
                    node._children.forEach(c => collapseAfterDepth(c, maxDepth));
                } else {
                    node.children.forEach(c => collapseAfterDepth(c, maxDepth));
                }
            }
        }

        collapseAfterDepth(root, INITIAL_EXPAND_DEPTH);

        // ============================================================
        // Helper: count all descendants of a node
        // ============================================================
        function countHiddenDescendants(node) {
            if (!node._children) return 0;
            let count = 0;
            function recurse(n) {
                if (!n) return;
                const kids = n.children || n._children;
                if (kids) {
                    count += kids.length;
                    kids.forEach(recurse);
                }
            }
            recurse({ children: node._children, _children: null });
            return count;
        }

        // ============================================================
        // Toggle children
        // ============================================================
        function toggle(d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
            } else if (d._children) {
                d.children = d._children;
                d._children = null;
            }
        }

        // ============================================================
        // Expand all
        // ============================================================
        function expandAll(node) {
            if (node._children) {
                node.children = node._children;
                node._children = null;
            }
            if (node.children) {
                node.children.forEach(expandAll);
            }
        }

        // ============================================================
        // Collapse all except root
        // ============================================================
        function collapseAllExceptRoot(node) {
            if (node.children && node.depth > 0) {
                node._children = node.children;
                node.children = null;
                if (node._children) node._children.forEach(c => collapseAllExceptRoot(c));
            } else if (node.children) {
                node.children.forEach(c => collapseAllExceptRoot(c));
            }
        }

        // ============================================================
        // Truncate text
        // ============================================================
        function truncate(text, maxLen) {
            if (!text) return '';
            return text.length > maxLen ? text.substring(0, maxLen - 1) + '...' : text;
        }

        // ============================================================
        // Format date string
        // ============================================================
        function formatDates(d) {
            const data = d.data ? d.data : d;
            const personData = data.data || {};
            let str = '';
            if (personData.birthDate) {
                str += personData.birthDate;
            }
            if (personData.isLiving) {
                str += str ? ' - ' : '';
                str += '{{ __("Vivo/a") }}';
            } else if (personData.deathDate) {
                str += str ? ' - ' : '';
                str += personData.deathDate;
            }
            return str;
        }

        // ============================================================
        // Tooltip
        // ============================================================
        const tooltip = document.getElementById('chart-tooltip');
        const tooltipName = document.getElementById('tooltip-name');
        const tooltipDates = document.getElementById('tooltip-dates');
        const tooltipSpouses = document.getElementById('tooltip-spouses');

        function showTooltip(event, d) {
            const personData = d.data.data || {};
            tooltipName.textContent = personData.name || d.data.name || '';

            let dates = '';
            if (personData.birthDate) dates += '{{ __("Nac.") }}: ' + personData.birthDate;
            if (personData.isLiving) {
                dates += dates ? ' | ' : '';
                dates += '{{ __("Vivo/a") }}';
            } else if (personData.deathDate) {
                dates += dates ? ' | ' : '';
                dates += '{{ __("Fall.") }}: ' + personData.deathDate;
            }
            tooltipDates.textContent = dates;

            let spouseInfo = '';
            if (d.data._spouses && d.data._spouses.length > 0) {
                const spouseNames = d.data._spouses.map(s => {
                    let sn = s.data.name || '';
                    if (s.marriageDate) sn += ' (m. ' + s.marriageDate + ')';
                    return sn;
                });
                spouseInfo = '{{ __("Conyuge(s)") }}: ' + spouseNames.join(', ');
            }
            tooltipSpouses.textContent = spouseInfo;

            tooltip.style.display = 'block';
            tooltip.style.left = (event.pageX + 15) + 'px';
            tooltip.style.top = (event.pageY - 10) + 'px';
            tooltip.style.opacity = '1';
        }

        function moveTooltip(event) {
            tooltip.style.left = (event.pageX + 15) + 'px';
            tooltip.style.top = (event.pageY - 10) + 'px';
        }

        function hideTooltip() {
            tooltip.style.opacity = '0';
            setTimeout(() => { tooltip.style.display = 'none'; }, 200);
        }

        // ============================================================
        // Unique node ID counter
        // ============================================================
        let nodeIdCounter = 0;

        // ============================================================
        // UPDATE function - Main render
        // ============================================================
        function update(source) {
            // Compute tree layout
            const treeInfo = treeLayout(root);
            const nodes = treeInfo.descendants();
            const links = treeInfo.links();

            // Normalize for fixed vertical depth
            nodes.forEach(d => {
                d.y = d.depth * VERTICAL_SPACING;
            });

            // -------------------------------------------------------
            // LINKS
            // -------------------------------------------------------
            const link = g.selectAll('path.tree-link')
                .data(links, d => d.target.data.data ? d.target.data.data.id : d.target.data.name);

            // Enter links
            const linkEnter = link.enter()
                .insert('path', 'g')
                .attr('class', 'tree-link')
                .attr('fill', 'none')
                .attr('stroke', linkColor())
                .attr('stroke-width', 2)
                .attr('d', () => {
                    const o = { x: source.x0, y: source.y0 };
                    return diagonal({ source: o, target: o });
                });

            // Update + Enter
            const linkUpdate = linkEnter.merge(link);

            linkUpdate.transition()
                .duration(DURATION)
                .attr('d', d => diagonal(d))
                .attr('stroke', linkColor());

            // Exit links
            link.exit().transition()
                .duration(DURATION)
                .attr('d', () => {
                    const o = { x: source.x, y: source.y };
                    return diagonal({ source: o, target: o });
                })
                .remove();

            // -------------------------------------------------------
            // NODES
            // -------------------------------------------------------
            const node = g.selectAll('g.node')
                .data(nodes, d => {
                    if (!d.id) d.id = ++nodeIdCounter;
                    return d.id;
                });

            // Enter nodes
            const nodeEnter = node.enter()
                .append('g')
                .attr('class', 'node')
                .attr('transform', `translate(${source.x0},${source.y0})`)
                .style('cursor', 'pointer');

            // -- Person rectangle --
            nodeEnter.append('rect')
                .attr('class', 'node-rect')
                .attr('x', -NODE_WIDTH / 2)
                .attr('y', -NODE_HEIGHT / 2)
                .attr('width', NODE_WIDTH)
                .attr('height', NODE_HEIGHT)
                .attr('rx', 8)
                .attr('ry', 8)
                .attr('fill', d => genderColors(d.data.data?.gender).fill)
                .attr('stroke', d => genderColors(d.data.data?.gender).stroke)
                .attr('stroke-width', 2)
                .style('filter', 'drop-shadow(0 1px 2px rgba(0,0,0,0.1))');

            // -- Photo clipPath & circle --
            nodeEnter.append('clipPath')
                .attr('id', d => 'clip-photo-' + d.id)
                .append('circle')
                .attr('cx', -NODE_WIDTH / 2 + 20)
                .attr('cy', -4)
                .attr('r', 12);

            nodeEnter.each(function(d) {
                const photoUrl = d.data.data?.photo;
                if (photoUrl) {
                    d3.select(this).append('image')
                        .attr('class', 'node-photo')
                        .attr('x', -NODE_WIDTH / 2 + 8)
                        .attr('y', -16)
                        .attr('width', 24)
                        .attr('height', 24)
                        .attr('clip-path', 'url(#clip-photo-' + d.id + ')')
                        .attr('href', photoUrl)
                        .attr('preserveAspectRatio', 'xMidYMid slice');
                } else {
                    // Default avatar circle
                    d3.select(this).append('circle')
                        .attr('class', 'node-avatar')
                        .attr('cx', -NODE_WIDTH / 2 + 20)
                        .attr('cy', -4)
                        .attr('r', 12)
                        .attr('fill', d => genderColors(d.data.data?.gender).stroke)
                        .attr('opacity', 0.3);

                    // Avatar icon
                    d3.select(this).append('text')
                        .attr('class', 'node-avatar-icon')
                        .attr('x', -NODE_WIDTH / 2 + 20)
                        .attr('y', 0)
                        .attr('text-anchor', 'middle')
                        .attr('font-size', '12px')
                        .attr('fill', d => genderColors(d.data.data?.gender).stroke)
                        .text(d => {
                            const g = d.data.data?.gender;
                            return g === 'M' ? '\u2642' : (g === 'F' ? '\u2640' : '\u26A5');
                        });
                }
            });

            // -- Name text --
            nodeEnter.append('text')
                .attr('class', 'node-name')
                .attr('x', -NODE_WIDTH / 2 + 38)
                .attr('y', -6)
                .attr('text-anchor', 'start')
                .attr('font-size', '12px')
                .attr('font-weight', 'bold')
                .attr('fill', d => genderColors(d.data.data?.gender).text)
                .text(d => truncate(d.data.data?.name || d.data.name, 16));

            // -- Dates text --
            nodeEnter.append('text')
                .attr('class', 'node-dates')
                .attr('x', -NODE_WIDTH / 2 + 38)
                .attr('y', 10)
                .attr('text-anchor', 'start')
                .attr('font-size', '10px')
                .attr('fill', d => genderColors(d.data.data?.gender).subtext)
                .text(d => truncate(formatDates(d), 20));

            // -- Living indicator --
            nodeEnter.each(function(d) {
                if (d.data.data?.isLiving) {
                    d3.select(this).append('circle')
                        .attr('class', 'living-dot')
                        .attr('cx', NODE_WIDTH / 2 - 10)
                        .attr('cy', -NODE_HEIGHT / 2 + 10)
                        .attr('r', 4)
                        .attr('fill', '#22c55e');
                }
            });

            // -- Collapsed children badge --
            nodeEnter.append('g')
                .attr('class', 'collapse-badge')
                .style('display', 'none');

            // -- Event handlers --
            nodeEnter
                .on('click', function(event, d) {
                    event.stopPropagation();
                    if (d.children || d._children) {
                        toggle(d);
                        update(d);
                    }
                })
                .on('dblclick', function(event, d) {
                    event.stopPropagation();
                    event.preventDefault();
                    const url = d.data.data?.url;
                    if (url) {
                        window.location.href = url;
                    }
                })
                .on('mouseenter', function(event, d) {
                    showTooltip(event, d);
                    d3.select(this).select('.node-rect')
                        .transition().duration(150)
                        .attr('stroke-width', 3);
                })
                .on('mousemove', moveTooltip)
                .on('mouseleave', function() {
                    hideTooltip();
                    d3.select(this).select('.node-rect')
                        .transition().duration(150)
                        .attr('stroke-width', 2);
                });

            // -------------------------------------------------------
            // UPDATE existing + entering nodes
            // -------------------------------------------------------
            const nodeUpdate = nodeEnter.merge(node);

            nodeUpdate.transition()
                .duration(DURATION)
                .attr('transform', d => `translate(${d.x},${d.y})`);

            // Update node visual state (collapsed vs expanded)
            nodeUpdate.select('.node-rect')
                .attr('fill', d => genderColors(d.data.data?.gender).fill)
                .attr('stroke', d => genderColors(d.data.data?.gender).stroke)
                .attr('stroke-dasharray', d => d._children ? '5,3' : 'none')
                .attr('opacity', d => d._children ? 0.85 : 1);

            // Update name color
            nodeUpdate.select('.node-name')
                .attr('fill', d => genderColors(d.data.data?.gender).text);

            // Update dates color
            nodeUpdate.select('.node-dates')
                .attr('fill', d => genderColors(d.data.data?.gender).subtext);

            // Update collapse badge
            nodeUpdate.each(function(d) {
                const badge = d3.select(this).select('.collapse-badge');
                if (d._children && d._children.length > 0) {
                    const count = countHiddenDescendants(d);
                    badge.style('display', null);
                    badge.selectAll('*').remove();

                    badge.append('circle')
                        .attr('cx', 0)
                        .attr('cy', NODE_HEIGHT / 2 + 12)
                        .attr('r', 10)
                        .attr('fill', badgeBg())
                        .attr('stroke', genderColors(d.data.data?.gender).stroke)
                        .attr('stroke-width', 1.5);

                    badge.append('text')
                        .attr('x', 0)
                        .attr('y', NODE_HEIGHT / 2 + 16)
                        .attr('text-anchor', 'middle')
                        .attr('font-size', '9px')
                        .attr('font-weight', 'bold')
                        .attr('fill', badgeText())
                        .text('+' + count);
                } else {
                    badge.style('display', 'none');
                }
            });

            // -------------------------------------------------------
            // SPOUSE nodes
            // -------------------------------------------------------
            nodeUpdate.each(function(d) {
                const parentG = d3.select(this);
                // Remove old spouse groups
                parentG.selectAll('.spouse-group').remove();

                const spouses = d.data._spouses;
                if (!spouses || spouses.length === 0) return;

                spouses.forEach((spouse, idx) => {
                    const spouseG = parentG.append('g')
                        .attr('class', 'spouse-group')
                        .style('cursor', 'pointer');

                    const spouseX = NODE_WIDTH / 2 + SPOUSE_GAP;
                    const spouseY = -SPOUSE_HEIGHT / 2 + idx * (SPOUSE_HEIGHT + 8);
                    const colors = genderColors(spouse.data?.gender);

                    // Dashed connecting line
                    spouseG.append('line')
                        .attr('x1', NODE_WIDTH / 2)
                        .attr('y1', spouseY + SPOUSE_HEIGHT / 2)
                        .attr('x2', spouseX)
                        .attr('y2', spouseY + SPOUSE_HEIGHT / 2)
                        .attr('stroke', spouseLinkColor())
                        .attr('stroke-width', 1.5)
                        .attr('stroke-dasharray', '4,3');

                    // Marriage symbol "="
                    spouseG.append('text')
                        .attr('x', NODE_WIDTH / 2 + SPOUSE_GAP / 2)
                        .attr('y', spouseY + SPOUSE_HEIGHT / 2 - 4)
                        .attr('text-anchor', 'middle')
                        .attr('font-size', '10px')
                        .attr('font-weight', 'bold')
                        .attr('fill', spouseLinkColor())
                        .text('=');

                    // Marriage year below "="
                    if (spouse.marriageDate) {
                        spouseG.append('text')
                            .attr('x', NODE_WIDTH / 2 + SPOUSE_GAP / 2)
                            .attr('y', spouseY + SPOUSE_HEIGHT / 2 + 8)
                            .attr('text-anchor', 'middle')
                            .attr('font-size', '8px')
                            .attr('fill', spouseLinkColor())
                            .text(spouse.marriageDate);
                    }

                    // Spouse rectangle
                    spouseG.append('rect')
                        .attr('x', spouseX)
                        .attr('y', spouseY)
                        .attr('width', SPOUSE_WIDTH)
                        .attr('height', SPOUSE_HEIGHT)
                        .attr('rx', 6)
                        .attr('ry', 6)
                        .attr('fill', colors.fill)
                        .attr('stroke', colors.stroke)
                        .attr('stroke-width', 1.5)
                        .attr('stroke-dasharray', '6,3')
                        .style('filter', 'drop-shadow(0 1px 1px rgba(0,0,0,0.05))');

                    // Spouse photo or avatar
                    const spPhotoX = spouseX + 8;
                    const spPhotoY = spouseY + SPOUSE_HEIGHT / 2;
                    if (spouse.data?.photo) {
                        const clipId = 'clip-spouse-' + d.id + '-' + idx;
                        spouseG.append('clipPath')
                            .attr('id', clipId)
                            .append('circle')
                            .attr('cx', spPhotoX + 10)
                            .attr('cy', spPhotoY)
                            .attr('r', 10);
                        spouseG.append('image')
                            .attr('x', spPhotoX)
                            .attr('y', spPhotoY - 10)
                            .attr('width', 20)
                            .attr('height', 20)
                            .attr('clip-path', 'url(#' + clipId + ')')
                            .attr('href', spouse.data.photo)
                            .attr('preserveAspectRatio', 'xMidYMid slice');
                    } else {
                        spouseG.append('circle')
                            .attr('cx', spPhotoX + 10)
                            .attr('cy', spPhotoY)
                            .attr('r', 10)
                            .attr('fill', colors.stroke)
                            .attr('opacity', 0.25);
                        spouseG.append('text')
                            .attr('x', spPhotoX + 10)
                            .attr('y', spPhotoY + 4)
                            .attr('text-anchor', 'middle')
                            .attr('font-size', '10px')
                            .attr('fill', colors.stroke)
                            .text(spouse.data?.gender === 'M' ? '\u2642' : (spouse.data?.gender === 'F' ? '\u2640' : '\u26A5'));
                    }

                    // Spouse name
                    spouseG.append('text')
                        .attr('x', spPhotoX + 26)
                        .attr('y', spouseY + SPOUSE_HEIGHT / 2 - 3)
                        .attr('text-anchor', 'start')
                        .attr('font-size', '11px')
                        .attr('font-weight', '600')
                        .attr('fill', colors.text)
                        .text(truncate(spouse.data?.name || '', 14));

                    // Spouse dates
                    const spouseDates = formatDates({ data: spouse });
                    if (spouseDates) {
                        spouseG.append('text')
                            .attr('x', spPhotoX + 26)
                            .attr('y', spouseY + SPOUSE_HEIGHT / 2 + 10)
                            .attr('text-anchor', 'start')
                            .attr('font-size', '9px')
                            .attr('fill', colors.subtext)
                            .text(truncate(spouseDates, 16));
                    }

                    // Living dot for spouse
                    if (spouse.data?.isLiving) {
                        spouseG.append('circle')
                            .attr('cx', spouseX + SPOUSE_WIDTH - 8)
                            .attr('cy', spouseY + 8)
                            .attr('r', 3)
                            .attr('fill', '#22c55e');
                    }

                    // Spouse double-click navigates
                    spouseG.on('dblclick', function(event) {
                        event.stopPropagation();
                        event.preventDefault();
                        const url = spouse.data?.url;
                        if (url) window.location.href = url;
                    });

                    // Spouse hover tooltip
                    spouseG
                        .on('mouseenter', function(event) {
                            const sd = spouse.data || {};
                            tooltipName.textContent = sd.name || '';
                            let dates = '';
                            if (sd.birthDate) dates += '{{ __("Nac.") }}: ' + sd.birthDate;
                            if (sd.isLiving) {
                                dates += dates ? ' | ' : '';
                                dates += '{{ __("Vivo/a") }}';
                            } else if (sd.deathDate) {
                                dates += dates ? ' | ' : '';
                                dates += '{{ __("Fall.") }}: ' + sd.deathDate;
                            }
                            tooltipDates.textContent = dates;
                            tooltipSpouses.textContent = spouse.marriageDate ? '{{ __("Matrimonio") }}: ' + spouse.marriageDate : '';
                            tooltip.style.display = 'block';
                            tooltip.style.left = (event.pageX + 15) + 'px';
                            tooltip.style.top = (event.pageY - 10) + 'px';
                            tooltip.style.opacity = '1';
                        })
                        .on('mousemove', moveTooltip)
                        .on('mouseleave', hideTooltip);
                });
            });

            // -------------------------------------------------------
            // EXIT nodes
            // -------------------------------------------------------
            const nodeExit = node.exit().transition()
                .duration(DURATION)
                .attr('transform', `translate(${source.x},${source.y})`)
                .remove();

            nodeExit.select('.node-rect')
                .attr('width', 0)
                .attr('height', 0)
                .attr('x', 0)
                .attr('y', 0);

            nodeExit.selectAll('text')
                .style('fill-opacity', 0);

            // -------------------------------------------------------
            // Stash old positions for transition
            // -------------------------------------------------------
            nodes.forEach(d => {
                d.x0 = d.x;
                d.y0 = d.y;
            });
        }

        // ============================================================
        // Diagonal link generator (vertical)
        // ============================================================
        function diagonal(d) {
            return d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y)
                ({ source: d.source, target: d.target });
        }

        // ============================================================
        // Center tree in viewport
        // ============================================================
        function centerTree() {
            const treeInfo = treeLayout(root);
            const nodes = treeInfo.descendants();
            if (nodes.length === 0) return;

            let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
            nodes.forEach(d => {
                if (d.x < minX) minX = d.x;
                if (d.x > maxX) maxX = d.x;
                if (d.y < minY) minY = d.y;
                if (d.y > maxY) maxY = d.y;
            });

            const treeWidth = maxX - minX + NODE_WIDTH * 2;
            const treeHeight = maxY - minY + NODE_HEIGHT * 2;
            const scale = Math.min(
                width / (treeWidth + 100),
                height / (treeHeight + 100),
                1.0
            );
            const centerX = width / 2 - ((minX + maxX) / 2) * scale;
            const centerY = 60 - minY * scale;

            svg.transition()
                .duration(DURATION)
                .call(
                    zoomBehavior.transform,
                    d3.zoomIdentity.translate(centerX, centerY).scale(scale)
                );
        }

        // ============================================================
        // Dark mode observer
        // ============================================================
        const darkModeObserver = new MutationObserver(() => {
            // Re-render colors when dark mode toggles
            update(root);
            // Update link colors
            g.selectAll('path.tree-link').attr('stroke', linkColor());
        });

        darkModeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // ============================================================
        // Window resize
        // ============================================================
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const newWidth = container.clientWidth || 900;
                const newHeight = Math.max(500, window.innerHeight - 300);
                container.style.height = newHeight + 'px';
                svg.attr('viewBox', [0, 0, newWidth, newHeight]);
                centerTree();
            }, 250);
        });

        // ============================================================
        // Initial render
        // ============================================================
        update(root);
        // Center after initial render
        setTimeout(centerTree, 100);

    })();
    </script>
</x-app-layout>
