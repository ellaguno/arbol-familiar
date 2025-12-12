<x-app-layout>
    <x-slot name="title">{{ __('Arbol de') }} {{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    @push('styles')
    <style>
        .tree-node {
            cursor: pointer;
        }
        .tree-node rect {
            transition: filter 0.2s ease, stroke-width 0.2s ease;
        }
        .tree-node:hover rect {
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
            stroke-width: 3px;
        }
        .connector {
            fill: none;
            stroke: #9ca3af;
            stroke-width: 2;
        }
        .connector-dashed {
            stroke-dasharray: 5, 5;
        }
        .node-selected rect {
            stroke: #EF4034 !important;
            stroke-width: 4px !important;
            filter: drop-shadow(0 4px 8px rgba(239,64,52,0.3)) !important;
        }
        .marriage-line {
            stroke: #9ca3af;
            stroke-width: 2;
        }
        .expand-collapse-btn {
            cursor: pointer;
        }
        .expand-collapse-btn circle {
            fill: #f3f4f6;
            stroke: #6b7280;
            stroke-width: 1.5;
            transition: fill 0.2s ease;
        }
        .expand-collapse-btn:hover circle {
            fill: #e5e7eb;
        }
        .expand-collapse-btn text {
            fill: #374151;
            font-weight: bold;
            user-select: none;
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white border-b sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('tree.index') }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <div class="flex items-center gap-3">
                            @if($person->photo_path)
                                <img src="{{ Storage::url($person->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                    <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold">{{ substr($person->first_name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <h1 class="text-lg font-bold text-gray-900">{{ $person->full_name }}</h1>
                                <p class="text-sm text-gray-500">
                                    @if($person->birth_date)
                                        {{ $person->birth_date->format('Y') }}
                                        @if(!$person->is_living && $person->death_date)
                                            - {{ $person->death_date->format('Y') }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 mr-4">
                            <label class="text-sm text-gray-500">{{ __('Generaciones') }}:</label>
                            <select id="generations" class="form-input py-1 px-2 text-sm w-16">
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>

                        <button id="btn-zoom-in" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded" title="{{ __('Acercar') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                            </svg>
                        </button>
                        <button id="btn-zoom-out" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded" title="{{ __('Alejar') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                            </svg>
                        </button>
                        <button id="btn-reset" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded" title="{{ __('Centrar') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        <a href="{{ route('gedcom.tree', ['person' => $person]) }}"
                           id="btn-export"
                           class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded"
                           title="{{ __('Exportar GEDCOM') }}"
                           data-base-url="{{ route('gedcom.tree', ['person' => $person]) }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tree Container -->
        <div id="tree-container" class="w-full overflow-hidden" style="height: calc(100vh - 120px);">
            <svg id="tree-svg" class="w-full h-full"></svg>
        </div>

        <!-- Info Panel (sidebar) -->
        <div id="info-panel" class="fixed right-0 top-20 w-80 bg-white shadow-xl border-l transform translate-x-full transition-transform duration-300 z-40" style="height: calc(100vh - 80px); overflow-y: auto;">
            <div class="p-4 border-b flex justify-between items-center sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">{{ __('Detalles') }}</h3>
                <button id="close-panel" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="panel-content" class="p-4">
                <!-- Dynamic content -->
            </div>
        </div>

        <!-- Legend -->
        <div class="fixed bottom-4 left-4 bg-white rounded-lg shadow-lg p-3 z-20 text-xs">
            <h4 class="font-semibold mb-2">{{ __('Leyenda') }}</h4>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                    <span>{{ __('Masculino') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-pink-400"></div>
                    <span>{{ __('Femenino') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded border-2" style="border-color: #EF4034; background-color: #f5f5f5;"></div>
                    <span>{{ __('Persona central') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded border-2" style="border-color: #2563eb;"></div>
                    <span>{{ __('Herencia cultural') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded bg-gray-300"></div>
                    <span>{{ __('Fallecido') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>{{ __('Menor protegido') }}</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.min.js"></script>
    <script>
        const rootPersonId = {{ $person->id }};
        const apiUrl = '{{ route("tree.api.data", $person) }}';
        const baseTreeUrl = '{{ url("/tree/view") }}';
        const addPersonUrl = '{{ route("persons.create") }}';

        // Tree configuration
        const config = {
            nodeWidth: 170,
            nodeHeight: 65,
            horizontalSpacing: 60,  // Space between siblings
            verticalSpacing: 110,   // Space between generations
            avatarSize: 40,
            coupleSpacing: 30,      // Space between spouses in a marriage (gap between nodes)
            familySpacing: 100      // Space between different families/marriages
        };

        let svg, g, zoom;
        let treeData = null;
        let selectedPersonId = null;
        let nodePositions = {};
        let collapsedFamilies = {}; // Track which families are collapsed (by family key)
        let personDescendantsMap = {}; // Track which persons have descendants
        let personToFamilyMap = {}; // Map person/spouse ID to their family key
        let orientation = 'vertical'; // 'vertical' or 'horizontal'

        // Helper function to get node spread size (perpendicular to tree growth)
        // In vertical mode: nodes spread horizontally, so use nodeWidth
        // In horizontal mode: nodes spread vertically, so use nodeHeight
        function getNodeSpreadSize() {
            return orientation === 'vertical' ? config.nodeWidth : config.nodeHeight;
        }

        // Helper function to get the main node dimension along growth direction
        function getNodeDepthSize() {
            return orientation === 'vertical' ? config.nodeHeight : config.nodeWidth;
        }

        // Check if a person has descendants (families with children)
        function hasDescendants(personId) {
            return personDescendantsMap[personId] || false;
        }

        // Toggle collapse state for a specific family and re-render
        function toggleCollapse(personId, event) {
            if (event) {
                event.stopPropagation();
            }
            // Get the family key for this person/spouse
            const familyKey = personToFamilyMap[personId];
            if (familyKey) {
                collapsedFamilies[familyKey] = !collapsedFamilies[familyKey];
            }
            renderTree(treeData);
        }

        // Check if a person's family is collapsed
        function isPersonCollapsed(personId) {
            const familyKey = personToFamilyMap[personId];
            return familyKey ? (collapsedFamilies[familyKey] || false) : false;
        }

        // Check if a specific family is collapsed
        function isFamilyCollapsed(familyKey) {
            return collapsedFamilies[familyKey] || false;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initTree();
            loadTreeData();

            document.getElementById('generations').addEventListener('change', function() {
                loadTreeData();
                updateExportUrl();
            });

            // Actualizar URL de exportación inicial
            updateExportUrl();
            document.getElementById('btn-zoom-in').addEventListener('click', () => zoomBy(1.3));
            document.getElementById('btn-zoom-out').addEventListener('click', () => zoomBy(0.7));
            document.getElementById('btn-reset').addEventListener('click', resetView);
            document.getElementById('close-panel').addEventListener('click', closePanel);
        });

        function initTree() {
            const container = document.getElementById('tree-container');
            const width = container.clientWidth;
            const height = container.clientHeight;

            svg = d3.select('#tree-svg')
                .attr('width', width)
                .attr('height', height);

            // Clear and setup
            svg.selectAll('*').remove();

            // Define arrow marker
            svg.append('defs').append('marker')
                .attr('id', 'arrow')
                .attr('viewBox', '0 -5 10 10')
                .attr('refX', 8)
                .attr('refY', 0)
                .attr('markerWidth', 6)
                .attr('markerHeight', 6)
                .attr('orient', 'auto')
                .append('path')
                .attr('d', 'M0,-5L10,0L0,5')
                .attr('fill', '#9ca3af');

            // Add zoom behavior
            zoom = d3.zoom()
                .scaleExtent([0.2, 2])
                .on('zoom', (event) => {
                    g.attr('transform', event.transform);
                });

            svg.call(zoom);

            g = svg.append('g');

            // Initial center
            svg.call(zoom.transform, d3.zoomIdentity.translate(width / 2, height / 2));
        }

        function updateExportUrl() {
            const generations = document.getElementById('generations').value;
            const exportBtn = document.getElementById('btn-export');
            const baseUrl = exportBtn.dataset.baseUrl;
            exportBtn.href = `${baseUrl}?generations=${generations}`;
        }

        function loadTreeData() {
            const generations = document.getElementById('generations').value;

            fetch(`${apiUrl}?generations=${generations}&direction=both`)
                .then(res => res.json())
                .then(data => {
                    treeData = data;
                    nodePositions = {};
                    renderTree(data);
                })
                .catch(err => console.error('Error loading tree:', err));
        }

        // Build map of which persons have children (descendants to collapse)
        // Also maps person/spouse to their family key
        function buildDescendantsMap(data) {
            personDescendantsMap = {};
            personToFamilyMap = {};

            // Root person - check if they have any children in any family
            if (data.descendants && data.descendants.length > 0) {
                data.descendants.forEach((family, familyIdx) => {
                    const familyKey = `family-${data.root.id}-${familyIdx}`;

                    if (family.children && family.children.length > 0) {
                        personDescendantsMap[data.root.id] = true;
                        // Map root to this family (use first family if multiple)
                        if (!personToFamilyMap[data.root.id]) {
                            personToFamilyMap[data.root.id] = familyKey;
                        }

                        // Also mark spouse as having children
                        if (family.spouse) {
                            personDescendantsMap[family.spouse.id] = true;
                            personToFamilyMap[family.spouse.id] = familyKey;
                        }
                    }
                });
            }

            // Process all descendants recursively
            function processDescendants(families, parentId = null) {
                if (!families) return;
                families.forEach((family, familyIdx) => {
                    const familyKey = `family-${parentId}-${familyIdx}`;

                    // Mark parent and spouse as having descendants if this family has children
                    if (family.children && family.children.length > 0) {
                        if (parentId) {
                            personDescendantsMap[parentId] = true;
                            // Map parent to this family (use first family if multiple)
                            if (!personToFamilyMap[parentId]) {
                                personToFamilyMap[parentId] = familyKey;
                            }
                        }
                        if (family.spouse) {
                            personDescendantsMap[family.spouse.id] = true;
                            personToFamilyMap[family.spouse.id] = familyKey;
                        }
                    }

                    // Process children
                    if (family.children) {
                        family.children.forEach(child => {
                            // Check if this child has any children of their own
                            if (child.descendants && child.descendants.length > 0) {
                                // Recurse into child's descendants
                                processDescendants(child.descendants, child.id);
                            }
                        });
                    }
                });
            }

            if (data.descendants) {
                processDescendants(data.descendants, data.root.id);
            }
        }

        function renderTree(data) {
            g.selectAll('*').remove();

            const centerX = 0;
            const centerY = 0;

            // Build map of persons with descendants
            buildDescendantsMap(data);

            // First pass: calculate positions
            calculatePositions(data, centerX, centerY);

            // Draw connectors first (behind nodes)
            const connectorsGroup = g.append('g').attr('class', 'connectors');

            // Draw ancestors connectors
            if (data.ancestors && data.ancestors.length > 0) {
                drawAncestorConnectors(connectorsGroup, data.root.id, data.ancestors, centerX, centerY);
            }

            // Draw descendants connectors
            if (data.descendants && data.descendants.length > 0) {
                drawDescendantConnectors(connectorsGroup, data.root.id, data.descendants, centerX, centerY);
            }

            // Draw nodes
            const nodesGroup = g.append('g').attr('class', 'nodes');

            // Draw root node
            drawNode(nodesGroup, data.root, centerX, centerY, true);
            nodePositions[data.root.id] = { x: centerX, y: centerY };

            // Draw ancestors
            if (data.ancestors && data.ancestors.length > 0) {
                drawAncestors(nodesGroup, data.ancestors, centerX, centerY - config.verticalSpacing, 1);
            }

            // Draw descendants
            if (data.descendants && data.descendants.length > 0) {
                drawDescendants(nodesGroup, data.descendants, centerX, centerY, 1, data.root.id);
            }

            // Add buttons removed - functionality available in detail panel
        }

        // ============================================
        // FLEXTREE-BASED LAYOUT ALGORITHM
        // ============================================

        function calculatePositions(data, centerX, centerY) {
            // Calculate ancestor positions using flextree
            if (data.ancestors && data.ancestors.length > 0) {
                calculateAncestorPositionsFlextree(data.ancestors, centerX, centerY);
            }

            // Calculate descendant positions using flextree
            if (data.descendants && data.descendants.length > 0) {
                calculateDescendantPositionsFlextree(data, centerX, centerY);
            }
        }

        // Build hierarchy for ancestors (tree goes UP)
        function buildAncestorHierarchy(person, ancestors) {
            const node = {
                id: person.id,
                data: person,
                children: []
            };

            if (ancestors && ancestors.length > 0) {
                ancestors.forEach(ancestor => {
                    const childNode = buildAncestorHierarchy(ancestor, ancestor.ancestors);
                    node.children.push(childNode);
                });
            }

            return node;
        }

        function calculateAncestorPositionsFlextree(ancestors, centerX, centerY) {
            // Create a virtual root that represents the person
            const virtualRoot = {
                id: 'virtual-root',
                data: null,
                children: ancestors.map(ancestor => buildAncestorHierarchy(ancestor, ancestor.ancestors))
            };

            // Create d3 hierarchy
            const root = d3.hierarchy(virtualRoot);

            // For flextree:
            // - First dimension = spread (perpendicular to tree growth)
            // - Second dimension = depth (along tree growth direction)
            const nodeSpread = getNodeSpreadSize();
            const depthSpacing = config.verticalSpacing; // Generation gap
            const siblingSpacing = config.horizontalSpacing;

            const treeLayout = d3.flextree()
                .nodeSize(node => {
                    if (node.data.id === 'virtual-root') return [0, 0];
                    return [nodeSpread + siblingSpacing, depthSpacing];
                })
                .spacing((a, b) => {
                    // Closer spacing for siblings (same parent = couple)
                    if (a.parent === b.parent) return config.coupleSpacing;
                    return siblingSpacing;
                });

            // Apply layout
            treeLayout(root);

            // Extract positions (ancestors go up/left depending on orientation)
            root.descendants().forEach(node => {
                if (node.data.id !== 'virtual-root' && node.data.data) {
                    if (orientation === 'vertical') {
                        // Vertical: tree.x = horizontal, tree.y = depth (goes up)
                        nodePositions[node.data.id] = {
                            x: centerX + node.x,
                            y: centerY - node.y - depthSpacing
                        };
                    } else {
                        // Horizontal: tree.x = vertical spread, tree.y = depth (goes left)
                        nodePositions[node.data.id] = {
                            x: centerX - node.y - depthSpacing,
                            y: centerY + node.x
                        };
                    }
                }
            });
        }

        // Build hierarchy for descendants with dummy root for multiple families
        function buildDescendantHierarchy(rootPerson, families, parentId) {
            if (!families || families.length === 0) {
                return null;
            }

            // If multiple families, create a dummy root
            const useDummyRoot = families.length > 1;

            if (useDummyRoot) {
                const dummyRoot = {
                    id: `dummy-${parentId}`,
                    isDummy: true,
                    data: null,
                    children: []
                };

                families.forEach((family, idx) => {
                    const familyKey = `family-${parentId}-${idx}`;
                    const isThisFamilyCollapsed = isFamilyCollapsed(familyKey);
                    const familyNode = buildFamilyNode(family, parentId, isThisFamilyCollapsed, idx);
                    dummyRoot.children.push(familyNode);
                });

                return dummyRoot;
            } else {
                // Single family - no dummy needed
                const familyKey = `family-${parentId}-0`;
                const isThisFamilyCollapsed = isFamilyCollapsed(familyKey);
                return buildFamilyNode(families[0], parentId, isThisFamilyCollapsed, 0);
            }
        }

        function buildFamilyNode(family, parentId, isCollapsed, familyIndex) {
            // Family node contains spouse info and children
            const familyKey = `family-${parentId}-${familyIndex}`;
            const familyNode = {
                id: familyKey,
                isFamily: true,
                spouse: family.spouse,
                parentId: parentId,
                familyKey: familyKey,
                data: family,
                children: []
            };

            // Add children if not collapsed
            if (!isCollapsed && family.children && family.children.length > 0) {
                family.children.forEach(child => {
                    const childNode = {
                        id: child.id,
                        data: child,
                        children: []
                    };

                    // Recursively add this child's descendants (check if child's first family is collapsed)
                    if (child.descendants && child.descendants.length > 0) {
                        const childDescendants = buildDescendantHierarchy(child, child.descendants, child.id);
                        if (childDescendants) {
                            if (childDescendants.isDummy) {
                                // Multiple families - add dummy's children
                                childNode.children = childDescendants.children;
                            } else {
                                // Single family
                                childNode.children = [childDescendants];
                            }
                        }
                    }

                    familyNode.children.push(childNode);
                });
            }

            return familyNode;
        }

        function calculateDescendantPositionsFlextree(data, centerX, centerY) {
            const families = data.descendants;
            if (!families || families.length === 0) return;

            const rootId = data.root.id;

            // Approach: Use a simpler recursive layout that properly handles couples
            // Each "family unit" = person + spouse (if any) + children

            const nodeSpread = getNodeSpreadSize();
            const depthSpacing = config.verticalSpacing;
            const siblingSpacing = config.horizontalSpacing;

            // Calculate the total width needed for a subtree rooted at a family
            function calculateSubtreeWidth(families, parentId) {
                if (!families || families.length === 0) return config.nodeWidth;

                let totalWidth = 0;
                families.forEach((family, idx) => {
                    const familyKey = `family-${parentId}-${idx}`;
                    const isCollapsed = isFamilyCollapsed(familyKey);

                    // Width of parent unit (person + spouse)
                    const parentUnitWidth = family.spouse
                        ? (config.nodeWidth * 2 + config.coupleSpacing)
                        : config.nodeWidth;

                    if (isCollapsed || !family.children || family.children.length === 0) {
                        totalWidth += parentUnitWidth;
                    } else {
                        // Calculate children's total width
                        let childrenWidth = 0;
                        family.children.forEach((child, childIdx) => {
                            if (childIdx > 0) childrenWidth += siblingSpacing;

                            // Calculate child's own width
                            let childWidth = config.nodeWidth;

                            // If child has their own families (descendants)
                            if (child.descendants && child.descendants.length > 0) {
                                // Calculate subtree width for child's descendants
                                const subtreeWidth = calculateSubtreeWidth(child.descendants, child.id);

                                // Check if child has a spouse (first family)
                                const childHasSpouse = child.descendants.some(f => f.spouse);
                                if (childHasSpouse) {
                                    // Child + spouse base width
                                    const childUnitWidth = config.nodeWidth * 2 + config.coupleSpacing;
                                    childWidth = Math.max(childUnitWidth, subtreeWidth);
                                } else {
                                    childWidth = Math.max(config.nodeWidth, subtreeWidth);
                                }
                            }

                            childrenWidth += childWidth;
                        });

                        // Family width is max of parent unit or children spread
                        totalWidth += Math.max(parentUnitWidth, childrenWidth);
                    }

                    if (idx > 0) totalWidth += config.familySpacing;
                });

                return totalWidth;
            }

            // Position a family unit (person at parentX, spouse to the right)
            function positionFamilyUnit(personId, spouseData, parentX, parentY, familyIdx) {
                // Person is already positioned or is root
                const personPos = nodePositions[personId] || { x: parentX, y: parentY };

                if (spouseData) {
                    // Position spouse to the right of person
                    nodePositions[spouseData.id] = {
                        x: personPos.x + config.nodeWidth + config.coupleSpacing,
                        y: personPos.y
                    };

                    // Family center point (for drawing connectors)
                    nodePositions[`family-${personId}-${familyIdx}`] = {
                        x: personPos.x + (config.nodeWidth + config.coupleSpacing) / 2,
                        y: personPos.y
                    };
                }
            }

            // Recursively position all descendants
            function positionDescendants(families, parentId, parentX, parentY, level) {
                if (!families || families.length === 0) return;

                // Calculate total width needed for all families of this parent
                let totalFamiliesWidth = 0;
                const familyWidths = [];

                families.forEach((family, idx) => {
                    const familyKey = `family-${parentId}-${idx}`;
                    const isCollapsed = isFamilyCollapsed(familyKey);

                    // Parent unit width
                    const parentUnitWidth = family.spouse
                        ? (config.nodeWidth * 2 + config.coupleSpacing)
                        : config.nodeWidth;

                    let familyWidth;
                    if (isCollapsed || !family.children || family.children.length === 0) {
                        familyWidth = parentUnitWidth;
                    } else {
                        // Calculate children width
                        let childrenWidth = 0;
                        family.children.forEach((child, childIdx) => {
                            if (childIdx > 0) childrenWidth += siblingSpacing;
                            const childSubtreeWidth = calculateSubtreeWidth(child.descendants, child.id);
                            childrenWidth += Math.max(config.nodeWidth, childSubtreeWidth || config.nodeWidth);
                        });
                        familyWidth = Math.max(parentUnitWidth, childrenWidth);
                    }

                    familyWidths.push(familyWidth);
                    if (idx > 0) totalFamiliesWidth += config.familySpacing;
                    totalFamiliesWidth += familyWidth;
                });

                // Start X position (centered under parent)
                let currentX = parentX - totalFamiliesWidth / 2;

                families.forEach((family, familyIdx) => {
                    const familyKey = `family-${parentId}-${familyIdx}`;
                    const isCollapsed = isFamilyCollapsed(familyKey);
                    const familyWidth = familyWidths[familyIdx];

                    // Center of this family
                    const familyCenterX = currentX + familyWidth / 2;

                    // Position spouse if exists (parent is already at parentX for root, or calculated for others)
                    if (family.spouse) {
                        // For root's families, spouse goes to the right of root
                        if (parentId === rootId && familyIdx === 0) {
                            nodePositions[family.spouse.id] = {
                                x: parentX + config.nodeWidth + config.coupleSpacing,
                                y: parentY
                            };
                        } else if (parentId === rootId) {
                            // Multiple spouses for root - offset each one
                            const spouseOffset = familyIdx * (config.nodeWidth + config.familySpacing + config.coupleSpacing);
                            nodePositions[family.spouse.id] = {
                                x: parentX + config.nodeWidth + config.coupleSpacing + spouseOffset,
                                y: parentY
                            };
                        } else {
                            // For non-root parents
                            const parentPos = nodePositions[parentId];
                            if (parentPos) {
                                nodePositions[family.spouse.id] = {
                                    x: parentPos.x + config.nodeWidth + config.coupleSpacing,
                                    y: parentPos.y
                                };
                            }
                        }
                    }

                    // Position children if not collapsed
                    if (!isCollapsed && family.children && family.children.length > 0) {
                        const childY = parentY + depthSpacing;

                        // Calculate children positions
                        let childrenTotalWidth = 0;
                        const childWidths = [];

                        family.children.forEach((child, idx) => {
                            // Calculate child's width including their spouse if they have one
                            let childWidth = config.nodeWidth;

                            if (child.descendants && child.descendants.length > 0) {
                                const subtreeWidth = calculateSubtreeWidth(child.descendants, child.id);
                                const childHasSpouse = child.descendants.some(f => f.spouse);

                                if (childHasSpouse) {
                                    // Child + spouse base width
                                    const childUnitWidth = config.nodeWidth * 2 + config.coupleSpacing;
                                    childWidth = Math.max(childUnitWidth, subtreeWidth);
                                } else {
                                    childWidth = Math.max(config.nodeWidth, subtreeWidth);
                                }
                            }

                            childWidths.push(childWidth);
                            if (idx > 0) childrenTotalWidth += siblingSpacing;
                            childrenTotalWidth += childWidth;
                        });

                        // Start children from center of this family
                        let childX = familyCenterX - childrenTotalWidth / 2;

                        family.children.forEach((child, childIdx) => {
                            const childWidth = childWidths[childIdx];

                            // Check if child has a spouse - if so, position child on the left side
                            const childHasSpouse = child.descendants && child.descendants.some(f => f.spouse);
                            let childCenterX;

                            if (childHasSpouse) {
                                // Position child so that child+spouse pair is centered in the width
                                const coupleWidth = config.nodeWidth * 2 + config.coupleSpacing;
                                const offset = (childWidth - coupleWidth) / 2;
                                childCenterX = childX + offset + config.nodeWidth / 2;
                            } else {
                                childCenterX = childX + childWidth / 2;
                            }

                            // Position the child
                            nodePositions[child.id] = {
                                x: childCenterX,
                                y: childY
                            };

                            // Recursively position child's descendants
                            if (child.descendants && child.descendants.length > 0) {
                                positionDescendants(child.descendants, child.id, childCenterX, childY, level + 1);
                            }

                            childX += childWidth + siblingSpacing;
                        });
                    }

                    currentX += familyWidth + config.familySpacing;
                });
            }

            // Start positioning from root
            positionDescendants(families, rootId, centerX, centerY, 1);
        }

        function drawAncestors(parent, ancestors, x, y, level) {
            ancestors.forEach((ancestor, index) => {
                // Use pre-calculated positions
                const pos = nodePositions[ancestor.id];
                if (!pos) return;

                drawNode(parent, ancestor, pos.x, pos.y);

                if (ancestor.ancestors && ancestor.ancestors.length > 0) {
                    drawAncestors(parent, ancestor.ancestors, pos.x, pos.y - config.verticalSpacing, level + 1);
                }
            });
        }

        function drawAncestorConnectors(parent, childId, ancestors, x, y) {
            ancestors.forEach((ancestor, index) => {
                const pos = nodePositions[ancestor.id];
                if (!pos) return;

                if (orientation === 'vertical') {
                    // Vertical orientation: line goes up
                    const midY = y - config.verticalSpacing / 2;

                    // Draw connector path
                    parent.append('path')
                        .attr('class', 'connector')
                        .attr('d', `M${x},${y - config.nodeHeight / 2} L${x},${midY} L${pos.x},${midY} L${pos.x},${pos.y + config.nodeHeight / 2}`);
                } else {
                    // Horizontal orientation: line goes left
                    const midX = x - config.verticalSpacing / 2;

                    // Draw connector path (swapped axes)
                    parent.append('path')
                        .attr('class', 'connector')
                        .attr('d', `M${x - config.nodeWidth / 2},${y} L${midX},${y} L${midX},${pos.y} L${pos.x + config.nodeWidth / 2},${pos.y}`);
                }

                // Recursively draw ancestor connectors
                if (ancestor.ancestors && ancestor.ancestors.length > 0) {
                    drawAncestorConnectors(parent, ancestor.id, ancestor.ancestors, pos.x, pos.y);
                }
            });
        }

        function drawDescendants(parent, families, rootX, rootY, level, rootId = null) {
            families.forEach((family, familyIndex) => {
                const familyKey = `family-${rootId}-${familyIndex}`;
                const isFamilyCollapsd = isFamilyCollapsed(familyKey);

                // Draw spouse using pre-calculated position
                if (family.spouse) {
                    const spousePos = nodePositions[family.spouse.id];
                    if (spousePos) {
                        drawNode(parent, family.spouse, spousePos.x, spousePos.y);
                    }
                }

                // Skip children if this family is collapsed
                if (isFamilyCollapsd) return;

                // Draw children using pre-calculated positions
                const children = family.children || [];
                if (children.length > 0) {
                    children.forEach((child, childIndex) => {
                        const childPos = nodePositions[child.id];
                        if (!childPos) return;

                        drawNode(parent, child, childPos.x, childPos.y);

                        // Recursively draw descendants
                        if (child.descendants && child.descendants.length > 0) {
                            drawDescendants(parent, child.descendants, childPos.x, childPos.y, level + 1, child.id);
                        }
                    });
                }
            });
        }

        function drawDescendantConnectors(parent, rootId, families, rootX, rootY) {
            // Get the actual position of the parent (might be different from rootX for non-root persons)
            const parentPos = (rootId === rootPersonId)
                ? { x: rootX, y: rootY }
                : nodePositions[rootId] || { x: rootX, y: rootY };

            families.forEach((family, familyIndex) => {
                const familyKey = `family-${rootId}-${familyIndex}`;
                const isFamilyCollapsd = isFamilyCollapsed(familyKey);

                const hasSpouse = family.spouse;
                const children = family.children || [];
                const spousePos = hasSpouse ? nodePositions[family.spouse.id] : null;

                if (orientation === 'vertical') {
                    // VERTICAL ORIENTATION

                    // Marriage line to spouse (horizontal)
                    if (spousePos) {
                        parent.append('line')
                            .attr('class', 'marriage-line')
                            .attr('x1', parentPos.x + config.nodeWidth / 2)
                            .attr('y1', parentPos.y)
                            .attr('x2', spousePos.x - config.nodeWidth / 2)
                            .attr('y2', spousePos.y);
                    }

                    // Skip children connectors if this family is collapsed
                    if (isFamilyCollapsd) return;

                    // Lines to children
                    if (children.length > 0) {
                        const childPositions = children.map(child => nodePositions[child.id]).filter(p => p);
                        if (childPositions.length === 0) return;

                        // Parent center point (between person and spouse if exists)
                        // If spouse exists: center is midpoint between the two node centers
                        const parentCenterX = spousePos
                            ? (parentPos.x + spousePos.x) / 2
                            : parentPos.x;

                        // The Y coordinate of the horizontal bar (midpoint between parents and children)
                        const firstChildY = Math.min(...childPositions.map(p => p.y));
                        const parentBottomY = parentPos.y + config.nodeHeight / 2;
                        const childTopY = firstChildY - config.nodeHeight / 2;
                        const midY = parentBottomY + (childTopY - parentBottomY) / 2;

                        // Vertical line from center of marriage line down to midY
                        parent.append('line')
                            .attr('class', 'connector')
                            .attr('x1', parentCenterX)
                            .attr('y1', parentPos.y)  // Start from the marriage line level
                            .attr('x2', parentCenterX)
                            .attr('y2', midY);

                        // Find leftmost and rightmost children
                        const childXPositions = childPositions.map(p => p.x);
                        const firstChildX = Math.min(...childXPositions);
                        const lastChildX = Math.max(...childXPositions);

                        // Horizontal line connecting all children (extends from parent center to all children)
                        const leftX = Math.min(parentCenterX, firstChildX);
                        const rightX = Math.max(parentCenterX, lastChildX);

                        parent.append('line')
                            .attr('class', 'connector')
                            .attr('x1', leftX)
                            .attr('y1', midY)
                            .attr('x2', rightX)
                            .attr('y2', midY);

                        // Vertical lines to each child
                        children.forEach((child) => {
                            const childPos = nodePositions[child.id];
                            if (!childPos) return;

                            // Connect from horizontal bar down to child
                            parent.append('line')
                                .attr('class', 'connector')
                                .attr('x1', childPos.x)
                                .attr('y1', midY)
                                .attr('x2', childPos.x)
                                .attr('y2', childPos.y - config.nodeHeight / 2);

                            // Recursively draw child's descendant connectors
                            if (child.descendants && child.descendants.length > 0) {
                                drawDescendantConnectors(parent, child.id, child.descendants, childPos.x, childPos.y);
                            }
                        });
                    }
                } else {
                    // HORIZONTAL ORIENTATION
                    // Marriage line to spouse (vertical in horizontal mode)
                    if (spousePos) {
                        parent.append('line')
                            .attr('class', 'marriage-line')
                            .attr('x1', parentPos.x)
                            .attr('y1', parentPos.y + config.nodeHeight / 2)
                            .attr('x2', spousePos.x)
                            .attr('y2', spousePos.y - config.nodeHeight / 2);
                    }

                    // Skip children connectors if this family is collapsed
                    if (isFamilyCollapsd) return;

                    // Lines to children
                    if (children.length > 0) {
                        const childPositions = children.map(child => nodePositions[child.id]).filter(p => p);
                        if (childPositions.length === 0) return;

                        // Parent center point (between person and spouse if exists)
                        const parentCenterY = spousePos
                            ? (parentPos.y + spousePos.y) / 2
                            : parentPos.y;

                        // Fixed midX at consistent distance from parent
                        const firstChildX = Math.min(...childPositions.map(p => p.x));
                        const parentRightX = parentPos.x + config.nodeWidth / 2;
                        const childLeftX = firstChildX - config.nodeWidth / 2;
                        const midX = parentRightX + (childLeftX - parentRightX) / 2;

                        // Horizontal line from parent to midX
                        parent.append('line')
                            .attr('class', 'connector')
                            .attr('x1', parentRightX)
                            .attr('y1', parentCenterY)
                            .attr('x2', midX)
                            .attr('y2', parentCenterY);

                        // Find topmost and bottommost children
                        const childYPositions = childPositions.map(p => p.y);
                        const firstChildY = Math.min(...childYPositions);
                        const lastChildY = Math.max(...childYPositions);

                        // Vertical line connecting all children
                        const topY = Math.min(parentCenterY, firstChildY);
                        const bottomY = Math.max(parentCenterY, lastChildY);

                        parent.append('line')
                            .attr('class', 'connector')
                            .attr('x1', midX)
                            .attr('y1', topY)
                            .attr('x2', midX)
                            .attr('y2', bottomY);

                        // Horizontal lines to each child
                        children.forEach((child) => {
                            const childPos = nodePositions[child.id];
                            if (!childPos) return;

                            // Connect from vertical bar to child
                            parent.append('line')
                                .attr('class', 'connector')
                                .attr('x1', midX)
                                .attr('y1', childPos.y)
                                .attr('x2', childPos.x - config.nodeWidth / 2)
                                .attr('y2', childPos.y);

                            // Recursively draw child's descendant connectors
                            if (child.descendants && child.descendants.length > 0) {
                                drawDescendantConnectors(parent, child.id, child.descendants, childPos.x, childPos.y);
                            }
                        });
                    }
                }
            });
        }

        function drawNode(parent, person, x, y, isRoot = false) {
            const nodeGroup = parent.append('g')
                .attr('class', `tree-node ${isRoot ? 'node-selected' : ''}`)
                .attr('data-id', person.id)
                .attr('transform', `translate(${x - config.nodeWidth / 2}, ${y - config.nodeHeight / 2})`)
                .style('cursor', 'pointer')
                .on('click', (event) => {
                    event.stopPropagation();
                    selectPerson(person);
                });

            // Background
            // Determinar color de borde: persona central = rojo, herencia cultural = azul, otros = gris
            let strokeColor = '#e2e8f0'; // Default gris
            let strokeWidth = 2;

            // Borde azul para personas con herencia cultural
            if (person.hasEthnicHeritage) {
                strokeColor = '#2563eb'; // Azul herencia
                strokeWidth = 3;
            }

            // Borde especial para persona central (raíz)
            if (isRoot) {
                strokeColor = '#dc2626'; // Rojo para persona central
                strokeWidth = 3;
            }

            nodeGroup.append('rect')
                .attr('width', config.nodeWidth)
                .attr('height', config.nodeHeight)
                .attr('rx', 8)
                .attr('fill', isRoot ? '#f5f5f5' : '#ffffff') // Gris muy claro para persona central
                .attr('stroke', strokeColor)
                .attr('stroke-width', strokeWidth)
                .attr('filter', 'drop-shadow(0 1px 2px rgba(0,0,0,0.1))');

            // Avatar
            const avatarX = 10;
            const avatarY = (config.nodeHeight - config.avatarSize) / 2;
            const avatarRadius = config.avatarSize / 2;

            if (person.photo) {
                // Clip path for circular image
                const clipId = `clip-${person.id}-${Date.now()}`;
                nodeGroup.append('clipPath')
                    .attr('id', clipId)
                    .append('circle')
                    .attr('cx', avatarX + avatarRadius)
                    .attr('cy', avatarY + avatarRadius)
                    .attr('r', avatarRadius);

                nodeGroup.append('image')
                    .attr('href', person.photo)
                    .attr('x', avatarX)
                    .attr('y', avatarY)
                    .attr('width', config.avatarSize)
                    .attr('height', config.avatarSize)
                    .attr('clip-path', `url(#${clipId})`)
                    .attr('preserveAspectRatio', 'xMidYMid slice');
            } else {
                // Avatar circle
                nodeGroup.append('circle')
                    .attr('cx', avatarX + avatarRadius)
                    .attr('cy', avatarY + avatarRadius)
                    .attr('r', avatarRadius)
                    .attr('fill', person.isLiving
                        ? (person.gender === 'M' ? '#93c5fd' : '#f9a8d4')
                        : '#d1d5db');

                // Initial
                nodeGroup.append('text')
                    .attr('x', avatarX + avatarRadius)
                    .attr('y', avatarY + avatarRadius + 5)
                    .attr('text-anchor', 'middle')
                    .attr('fill', person.isLiving
                        ? (person.gender === 'M' ? '#1e40af' : '#be185d')
                        : '#374151')
                    .attr('font-weight', 'bold')
                    .attr('font-size', '16px')
                    .text(person.firstName ? person.firstName.charAt(0).toUpperCase() : '?');
            }

            // Name
            const textX = avatarX + config.avatarSize + 8;
            nodeGroup.append('text')
                .attr('x', textX)
                .attr('y', config.nodeHeight / 2 - 8)
                .attr('font-size', '12px')
                .attr('font-weight', '600')
                .attr('fill', '#111827')
                .text(truncateText(person.firstName || '', 14));

            // Apellido (o indicador de menor protegido)
            if (person.isProtected) {
                nodeGroup.append('text')
                    .attr('x', textX)
                    .attr('y', config.nodeHeight / 2 + 6)
                    .attr('font-size', '10px')
                    .attr('fill', '#9ca3af')
                    .attr('font-style', 'italic')
                    .text('{{ __("Menor protegido") }}');
            } else {
                nodeGroup.append('text')
                    .attr('x', textX)
                    .attr('y', config.nodeHeight / 2 + 6)
                    .attr('font-size', '11px')
                    .attr('fill', '#4b5563')
                    .text(truncateText(person.lastName || '', 14));
            }

            // Dates (solo si no está protegido)
            if (person.birthDate && !person.isProtected) {
                let dates = person.birthDate;
                if (!person.isLiving && person.deathDate) {
                    dates += ' - ' + person.deathDate;
                }
                nodeGroup.append('text')
                    .attr('x', textX)
                    .attr('y', config.nodeHeight / 2 + 20)
                    .attr('font-size', '10px')
                    .attr('fill', '#9ca3af')
                    .text(dates);
            }

            // Add expand/collapse button if person has descendants
            if (hasDescendants(person.id)) {
                const btnRadius = 10;
                const isCollapsed = isPersonCollapsed(person.id);

                // Position button based on orientation
                let btnX, btnY;
                if (orientation === 'vertical') {
                    // Below the node
                    btnX = config.nodeWidth / 2;
                    btnY = config.nodeHeight + btnRadius + 3;
                } else {
                    // To the right of the node
                    btnX = config.nodeWidth + btnRadius + 3;
                    btnY = config.nodeHeight / 2;
                }

                const btnGroup = nodeGroup.append('g')
                    .attr('class', 'expand-collapse-btn')
                    .attr('transform', `translate(${btnX}, ${btnY})`)
                    .on('click', (event) => {
                        event.stopPropagation();
                        toggleCollapse(person.id, event);
                    });

                btnGroup.append('circle')
                    .attr('r', btnRadius);

                btnGroup.append('text')
                    .attr('text-anchor', 'middle')
                    .attr('dominant-baseline', 'central')
                    .attr('font-size', '14px')
                    .text(isCollapsed ? '+' : '−');
            }
        }

        function truncateText(text, maxLength) {
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }

        function selectPerson(person) {
            selectedPersonId = person.id;

            // Update visual selection
            g.selectAll('.tree-node').classed('node-selected', false);
            g.selectAll(`.tree-node[data-id="${person.id}"]`).classed('node-selected', true);

            // Show panel
            showPersonInfo(person);
        }

        function showPersonInfo(person) {
            const panel = document.getElementById('info-panel');
            const content = document.getElementById('panel-content');
            const photoUploadUrl = '{{ url("/persons") }}/' + person.id + '/photo';

            content.innerHTML = `
                <div class="text-center mb-6">
                    <div class="relative inline-block group">
                        ${person.photo
                            ? `<img src="${person.photo}" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-gray-100">`
                            : `<div class="w-24 h-24 rounded-full mx-auto flex items-center justify-center ring-4 ring-gray-100 ${person.gender === 'M' ? 'bg-blue-100' : 'bg-pink-100'}">
                                <span class="text-3xl font-bold ${person.gender === 'M' ? 'text-blue-600' : 'text-pink-600'}">${person.firstName?.charAt(0) || '?'}</span>
                               </div>`
                        }
                        <label class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="file" class="hidden" accept="image/*" onchange="uploadPhoto(${person.id}, this)">
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">{{ __('Clic para cambiar foto') }}</p>
                    <h3 class="font-bold text-xl text-gray-900 mt-2">${person.name}</h3>
                    ${person.birthDate ? `<p class="text-sm text-gray-500 mt-1">${person.birthDate}${!person.isLiving && person.deathDate ? ' - ' + person.deathDate : ''}</p>` : ''}
                </div>

                <div class="space-y-3 mb-6">
                    ${person.hasEthnicHeritage ? `
                        <div class="flex items-center gap-2 text-red-600 bg-red-50 p-2 rounded">
                            <span class="text-sm font-medium">{{ __('Herencia cultural') }}</span>
                        </div>
                    ` : ''}

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ __('Estado') }}:</span>
                        <span class="font-medium">${person.isLiving ? '{{ __("Vivo/a") }}' : '{{ __("Fallecido/a") }}'}</span>
                    </div>

                    ${person.siblingsCount > 0 ? `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-blue-800">
                                    ${person.siblingsCount === 1 ? '{{ __("Tiene 1 hermano/a") }}' : '{{ __("Tiene") }} ' + person.siblingsCount + ' {{ __("hermanos/as") }}'}
                                </span>
                            </div>
                            <div class="space-y-1">
                                ${person.siblings.map(sib => `
                                    <a href="${baseTreeUrl}/${sib.id}" class="flex items-center gap-2 text-sm text-blue-700 hover:text-blue-900 hover:underline">
                                        <span class="w-2 h-2 rounded-full ${sib.gender === 'M' ? 'bg-blue-400' : 'bg-pink-400'}"></span>
                                        ${sib.name}
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>

                <div class="space-y-2">
                    <a href="${person.url}" class="block w-full py-2.5 px-4 bg-[#3b82f6] text-white text-center font-medium rounded-lg hover:bg-[#1d4ed8] transition-colors">
                        {{ __('Ver perfil completo') }}
                    </a>

                    <button onclick="centerOnPerson(${person.id})" class="block w-full py-2.5 px-4 border-2 border-[#2563eb] text-[#2563eb] text-center font-medium rounded-lg hover:bg-[#2563eb] hover:text-white transition-colors">
                        {{ __('Centrar en arbol') }}
                    </button>

                    <a href="${baseTreeUrl}/${person.id}" class="block w-full py-2.5 px-4 border-2 border-[#2563eb] text-[#2563eb] text-center font-medium rounded-lg hover:bg-[#2563eb] hover:text-white transition-colors">
                        {{ __('Ver su arbol') }}
                    </a>
                </div>

                <div class="mt-6 pt-4 border-t">
                    <h4 class="font-semibold text-gray-900 mb-3">{{ __('Agregar familiar') }}</h4>
                    <div class="grid grid-cols-2 gap-2">
                        ${person.hasFather
                            ? `<span class="py-2 px-3 text-xs text-center border border-gray-200 text-gray-400 rounded cursor-not-allowed" title="{{ __('Ya tiene padre registrado') }}">
                                {{ __('Padre') }}
                               </span>`
                            : `<a href="${addPersonUrl}?relation=father&related_to=${person.id}" class="py-2 px-3 text-xs text-center border border-blue-500 text-blue-600 rounded hover:bg-blue-50">
                                + {{ __('Padre') }}
                               </a>`
                        }
                        ${person.hasMother
                            ? `<span class="py-2 px-3 text-xs text-center border border-gray-200 text-gray-400 rounded cursor-not-allowed" title="{{ __('Ya tiene madre registrada') }}">
                                {{ __('Madre') }}
                               </span>`
                            : `<a href="${addPersonUrl}?relation=mother&related_to=${person.id}" class="py-2 px-3 text-xs text-center border border-blue-500 text-blue-600 rounded hover:bg-blue-50">
                                + {{ __('Madre') }}
                               </a>`
                        }
                        <a href="${addPersonUrl}?relation=spouse&related_to=${person.id}" class="py-2 px-3 text-xs text-center border border-bñie-500 text-blue-600 rounded hover:bg-blue-50">
                            + {{ __('Conyuge') }}
                        </a>
                        <a href="${addPersonUrl}?relation=child&related_to=${person.id}" class="py-2 px-3 text-xs text-center border border-blue-500 text-blue-600 rounded hover:bg-blue-50">
                            + {{ __('Hijo/a') }}
                        </a>
                        <a href="${addPersonUrl}?relation=sibling&related_to=${person.id}" class="py-2 px-3 text-xs text-center border border-blue-500 text-blue-600 rounded hover:bg-blue-50">
                            + {{ __('Hermano/a') }}
                        </a>
                    </div>
                </div>
            `;

            panel.classList.remove('translate-x-full');
        }

        function closePanel() {
            document.getElementById('info-panel').classList.add('translate-x-full');
            selectedPersonId = null;
            g.selectAll('.tree-node').classed('node-selected', false);
        }

        function zoomBy(factor) {
            svg.transition().duration(300).call(zoom.scaleBy, factor);
        }

        function resetView() {
            const container = document.getElementById('tree-container');
            svg.transition().duration(500).call(
                zoom.transform,
                d3.zoomIdentity.translate(container.clientWidth / 2, container.clientHeight / 2)
            );
        }

        function centerOnPerson(personId) {
            const pos = nodePositions[personId];
            if (!pos) {
                // If position not found, reload with this person as root
                window.location.href = `${baseTreeUrl}/${personId}`;
                return;
            }

            const container = document.getElementById('tree-container');
            const width = container.clientWidth;
            const height = container.clientHeight;

            svg.transition().duration(500).call(
                zoom.transform,
                d3.zoomIdentity.translate(width / 2 - pos.x, height / 2 - pos.y)
            );
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            const container = document.getElementById('tree-container');
            svg.attr('width', container.clientWidth)
               .attr('height', container.clientHeight);
        });

        // Close panel when clicking outside
        svg.on('click', () => {
            closePanel();
        });

        // Upload photo function
        function uploadPhoto(personId, input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            const photoContainer = input.closest('.relative');
            const originalContent = photoContainer.innerHTML;
            photoContainer.innerHTML = `
                <div class="w-24 h-24 rounded-full mx-auto flex items-center justify-center bg-gray-100 ring-4 ring-gray-100">
                    <svg class="w-8 h-8 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            `;

            fetch(`{{ url('/persons') }}/${personId}/photo`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update photo in panel
                    photoContainer.innerHTML = `
                        <img src="${data.photo}" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-gray-100">
                        <label class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="file" class="hidden" accept="image/*" onchange="uploadPhoto(${personId}, this)">
                        </label>
                    `;
                    // Reload tree to show new photo in nodes
                    loadTreeData();
                } else {
                    alert(data.message || '{{ __("Error al subir la foto") }}');
                    photoContainer.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("Error al subir la foto") }}');
                photoContainer.innerHTML = originalContent;
            });
        }
    </script>
    @endpush
</x-app-layout>
