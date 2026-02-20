<x-app-layout>
    <x-slot name="title">{{ $media->title }} - {{ config('app.name') }}</x-slot>

    @if($media->isImage())
    <style>
        .media-viewer-fullscreen {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            flex-direction: column;
        }
        .media-viewer-fullscreen .viewer-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            flex-shrink: 0;
        }
        .media-viewer-fullscreen .viewer-toolbar button {
            padding: 0.5rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .media-viewer-fullscreen .viewer-toolbar button:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        .media-viewer-canvas {
            flex: 1;
            overflow: hidden;
            cursor: grab;
            position: relative;
        }
        .media-viewer-canvas.dragging {
            cursor: grabbing;
        }
        .media-viewer-canvas img {
            position: absolute;
            transform-origin: 0 0;
            max-width: none;
            max-height: none;
            user-select: none;
            -webkit-user-drag: none;
        }
    </style>
    @endif

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('media.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Galeria') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium truncate max-w-xs ml-1 md:ml-2">{{ $media->title }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Preview -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-body p-0">
                        @if($media->isImage())
                            <div x-data="imageViewer('{{ $media->url }}', {{ json_encode($media->title) }})" class="relative">
                                {{-- Inline preview --}}
                                <img src="{{ $media->url }}" alt="{{ $media->title }}"
                                     class="w-full h-auto rounded-lg cursor-zoom-in"
                                     @click="openFullscreen()">

                                {{-- Zoom hint --}}
                                <div class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                    {{ __('Clic para ampliar') }}
                                </div>

                                {{-- Fullscreen viewer --}}
                                <template x-teleport="body">
                                    <div x-show="isOpen" x-cloak
                                         class="media-viewer-fullscreen"
                                         @keydown.escape.window="closeFullscreen()"
                                         @keydown.plus.window="zoomIn()"
                                         @keydown.equal.window="zoomIn()"
                                         @keydown.minus.window="zoomOut()"
                                         @keydown.0.window="resetView()">

                                        {{-- Toolbar --}}
                                        <div class="viewer-toolbar">
                                            <span class="text-sm truncate max-w-xs" x-text="title"></span>
                                            <div class="flex items-center gap-2">
                                                <button @click="zoomOut()" title="{{ __('Alejar') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                                                    </svg>
                                                </button>
                                                <span class="text-sm min-w-[4rem] text-center" x-text="Math.round(scale * 100) + '%'"></span>
                                                <button @click="zoomIn()" title="{{ __('Acercar') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                    </svg>
                                                </button>
                                                <button @click="resetView()" title="{{ __('Ajustar a pantalla') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                                    </svg>
                                                </button>
                                                <button @click="closeFullscreen()" title="{{ __('Cerrar') }} (Esc)">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Canvas --}}
                                        <div class="media-viewer-canvas"
                                             x-ref="canvas"
                                             :class="{ 'dragging': isDragging }"
                                             @mousedown="startDrag($event)"
                                             @mousemove="drag($event)"
                                             @mouseup="endDrag()"
                                             @mouseleave="endDrag()"
                                             @wheel.prevent="handleWheel($event)"
                                             @touchstart.passive="startTouch($event)"
                                             @touchmove.prevent="handleTouch($event)"
                                             @touchend="endTouch($event)"
                                             @dblclick="toggleFitActual($event)">
                                            <img :src="src" :alt="title"
                                                 x-ref="image"
                                                 @load="onImageLoad()"
                                                 :style="'transform: translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ')'">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @elseif($media->isDocument())
                            @if($media->isPdf())
                                <div class="aspect-[4/3]">
                                    <iframe src="{{ $media->url }}" class="w-full h-full rounded-lg"></iframe>
                                </div>
                            @else
                                <div class="p-12 text-center">
                                    <svg class="w-24 h-24 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-600 mb-4">{{ $media->file_name }}</p>
                                    <a href="{{ route('media.download', $media) }}" class="btn-primary">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        {{ __('Descargar') }}
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="p-12 text-center">
                                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <p class="text-gray-600 mb-4">{{ __('Enlace externo') }}</p>
                                <a href="{{ $media->external_url }}" target="_blank" class="btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ __('Abrir enlace') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Informacion') }}</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $media->title }}</h3>
                            @if($media->is_primary)
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ __('Principal') }}
                                </span>
                            @endif
                        </div>

                        @if($media->description)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Descripcion') }}</dt>
                                <dd class="text-gray-700">{{ $media->description }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm text-gray-500">{{ __('Tipo') }}</dt>
                            <dd class="text-gray-700">
                                @if($media->isImage()) {{ __('Imagen') }}
                                @elseif($media->isDocument()) {{ __('Documento') }}
                                @else {{ __('Enlace') }}
                                @endif
                            </dd>
                        </div>

                        @if($media->file_size)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Tamano') }}</dt>
                                <dd class="text-gray-700">{{ $media->formatted_size }}</dd>
                            </div>
                        @endif

                        @if($media->mediable)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Persona asociada') }}</dt>
                                <dd>
                                    <a href="{{ route('persons.show', $media->mediable_id) }}" class="text-mf-primary hover:underline">
                                        {{ $media->mediable->full_name }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        @if($media->created_at)
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Subido') }}</dt>
                                <dd class="text-gray-700">{{ $media->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body space-y-2">
                        @if($media->file_path)
                            <a href="{{ route('media.download', $media) }}" class="btn-outline w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                {{ __('Descargar') }}
                            </a>
                        @endif

                        @if($media->mediable_id)
                            <form action="{{ route('media.primary', $media) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-outline w-full">
                                    @if($media->is_primary)
                                        {{ __('Quitar como principal') }}
                                    @else
                                        {{ __('Marcar como principal') }}
                                    @endif
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('media.edit', $media) }}" class="btn-outline w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('Editar') }}
                        </a>

                        <form action="{{ route('media.destroy', $media) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accent w-full" onclick="return confirm('{{ __('Eliminar este archivo?') }}')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Eliminar') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($media->isImage())
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageViewer', (src, title) => ({
            src: src,
            title: title,
            isOpen: false,
            scale: 1,
            panX: 0,
            panY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            panStartX: 0,
            panStartY: 0,
            imgWidth: 0,
            imgHeight: 0,
            fitScale: 1,
            minScale: 0.1,
            maxScale: 10,
            // Touch
            lastTouchDist: 0,
            lastTouchX: 0,
            lastTouchY: 0,

            openFullscreen() {
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
                this.$nextTick(() => {
                    if (this.$refs.image && this.$refs.image.complete) {
                        this.onImageLoad();
                    }
                });
            },

            closeFullscreen() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },

            onImageLoad() {
                const img = this.$refs.image;
                if (!img) return;
                this.imgWidth = img.naturalWidth;
                this.imgHeight = img.naturalHeight;
                this.fitToScreen();
            },

            fitToScreen() {
                const canvas = this.$refs.canvas;
                if (!canvas || !this.imgWidth) return;
                const cw = canvas.clientWidth;
                const ch = canvas.clientHeight;
                this.fitScale = Math.min(cw / this.imgWidth, ch / this.imgHeight, 1);
                this.scale = this.fitScale;
                this.panX = (cw - this.imgWidth * this.scale) / 2;
                this.panY = (ch - this.imgHeight * this.scale) / 2;
            },

            resetView() {
                this.fitToScreen();
            },

            zoomIn() {
                this.zoomTo(this.scale * 1.3);
            },

            zoomOut() {
                this.zoomTo(this.scale / 1.3);
            },

            zoomTo(newScale, cx, cy) {
                const canvas = this.$refs.canvas;
                if (!canvas) return;
                newScale = Math.max(this.minScale, Math.min(this.maxScale, newScale));
                if (cx === undefined) {
                    cx = canvas.clientWidth / 2;
                    cy = canvas.clientHeight / 2;
                }
                // Zoom towards the point (cx, cy)
                const ratio = newScale / this.scale;
                this.panX = cx - (cx - this.panX) * ratio;
                this.panY = cy - (cy - this.panY) * ratio;
                this.scale = newScale;
            },

            toggleFitActual(e) {
                const rect = this.$refs.canvas.getBoundingClientRect();
                const cx = e.clientX - rect.left;
                const cy = e.clientY - rect.top;
                if (Math.abs(this.scale - this.fitScale) < 0.01) {
                    this.zoomTo(1, cx, cy);
                } else {
                    this.fitToScreen();
                }
            },

            handleWheel(e) {
                if (!this.isOpen) return;
                const rect = this.$refs.canvas.getBoundingClientRect();
                const cx = e.clientX - rect.left;
                const cy = e.clientY - rect.top;
                const factor = e.deltaY < 0 ? 1.15 : 1 / 1.15;
                this.zoomTo(this.scale * factor, cx, cy);
            },

            startDrag(e) {
                if (e.button !== 0) return;
                this.isDragging = true;
                this.dragStartX = e.clientX;
                this.dragStartY = e.clientY;
                this.panStartX = this.panX;
                this.panStartY = this.panY;
            },

            drag(e) {
                if (!this.isDragging) return;
                this.panX = this.panStartX + (e.clientX - this.dragStartX);
                this.panY = this.panStartY + (e.clientY - this.dragStartY);
            },

            endDrag() {
                this.isDragging = false;
            },

            // Touch support
            getTouchDist(e) {
                const t = e.touches;
                return Math.hypot(t[0].clientX - t[1].clientX, t[0].clientY - t[1].clientY);
            },

            getTouchCenter(e) {
                const t = e.touches;
                return {
                    x: (t[0].clientX + t[1].clientX) / 2,
                    y: (t[0].clientY + t[1].clientY) / 2
                };
            },

            startTouch(e) {
                if (e.touches.length === 1) {
                    this.isDragging = true;
                    this.dragStartX = e.touches[0].clientX;
                    this.dragStartY = e.touches[0].clientY;
                    this.panStartX = this.panX;
                    this.panStartY = this.panY;
                } else if (e.touches.length === 2) {
                    this.isDragging = false;
                    this.lastTouchDist = this.getTouchDist(e);
                    const c = this.getTouchCenter(e);
                    this.lastTouchX = c.x;
                    this.lastTouchY = c.y;
                }
            },

            handleTouch(e) {
                if (e.touches.length === 1 && this.isDragging) {
                    this.panX = this.panStartX + (e.touches[0].clientX - this.dragStartX);
                    this.panY = this.panStartY + (e.touches[0].clientY - this.dragStartY);
                } else if (e.touches.length === 2) {
                    const dist = this.getTouchDist(e);
                    const c = this.getTouchCenter(e);
                    const rect = this.$refs.canvas.getBoundingClientRect();
                    const cx = c.x - rect.left;
                    const cy = c.y - rect.top;
                    const factor = dist / this.lastTouchDist;
                    this.zoomTo(this.scale * factor, cx, cy);
                    this.lastTouchDist = dist;
                    this.lastTouchX = c.x;
                    this.lastTouchY = c.y;
                }
            },

            endTouch(e) {
                if (e.touches.length < 2) {
                    this.isDragging = false;
                }
            }
        }));
    });
    </script>
    @endif
</x-app-layout>
