<x-app-layout>
    <x-slot name="title">{{ __('Resultados de investigacion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="researchSession({{ $session->id }}, '{{ $session->status }}')"
         x-init="startPolling()">

        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('research.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Investigacion') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Resultados') }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-theme">{{ Str::limit($session->query, 100) }}</h1>
                @if($session->person)
                    <p class="text-theme-muted mt-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <a href="{{ route('persons.show', $session->person) }}" class="text-mf-primary hover:underline">
                            {{ $session->person->full_name }}
                        </a>
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <span x-text="statusLabel"
                      class="px-3 py-1 rounded-full text-sm"
                      :class="statusClass"></span>
                <span class="text-sm text-theme-muted">{{ $session->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Loading indicator -->
        <div x-show="isLoading" x-transition class="card mb-6 border-mf-primary/50">
            <div class="card-body">
                <div class="flex items-center gap-4 mb-4">
                    <div class="relative">
                        <svg class="animate-spin w-8 h-8 text-mf-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-theme" x-text="loadingMessage"></p>
                        <p class="text-sm text-theme-muted" x-text="loadingSubmessage"></p>
                    </div>
                </div>

                <!-- Progress steps -->
                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center gap-1" :class="stepCompleted('pending') ? 'text-green-600 dark:text-green-400' : (status === 'pending' ? 'text-mf-primary' : 'text-theme-muted')">
                        <svg x-show="stepCompleted('pending')" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="status === 'pending'" class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="5"/>
                        </svg>
                        <span>{{ __('Iniciando') }}</span>
                    </div>
                    <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="flex items-center gap-1" :class="stepCompleted('searching') ? 'text-green-600 dark:text-green-400' : (status === 'searching' ? 'text-mf-primary' : 'text-theme-muted')">
                        <svg x-show="stepCompleted('searching')" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="status === 'searching'" class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="5"/>
                        </svg>
                        <span>{{ __('Buscando') }}</span>
                    </div>
                    <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="flex items-center gap-1" :class="stepCompleted('analyzing') ? 'text-green-600 dark:text-green-400' : (status === 'analyzing' ? 'text-mf-primary' : 'text-theme-muted')">
                        <svg x-show="stepCompleted('analyzing')" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="status === 'analyzing'" class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="5"/>
                        </svg>
                        <span>{{ __('Analizando') }}</span>
                    </div>
                    <svg class="w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="flex items-center gap-1 text-theme-muted">
                        <span>{{ __('Listo') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error message -->
        <div x-show="hasError" x-transition class="card mb-6 border-red-300 dark:border-red-700">
            <div class="card-body bg-red-50 dark:bg-red-900/30">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="font-medium text-red-800 dark:text-red-200">{{ __('Error en la investigacion') }}</h3>
                        <p class="text-red-700 dark:text-red-300 mt-1" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search results -->
        <div x-show="searchResults && Object.keys(searchResults).length > 0" x-transition class="card mb-6">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-theme">{{ __('Resultados de busqueda') }}</h2>
            </div>
            <div class="card-body">
                <template x-for="(results, source) in searchResults" :key="source">
                    <div class="mb-6 last:mb-0">
                        <h3 class="font-medium text-theme mb-3 flex items-center gap-2">
                            <span x-text="source"></span>
                            <span class="text-sm text-theme-muted" x-text="'(' + results.length + ')'"></span>
                        </h3>
                        <div class="space-y-2">
                            <template x-for="(result, index) in results" :key="index">
                                <div class="p-3 border border-theme rounded-lg hover:bg-theme-secondary transition-colors">
                                    <template x-if="result.type === 'search_url'">
                                        <div>
                                            <a :href="result.url" target="_blank" rel="noopener"
                                               class="font-medium text-mf-primary hover:underline flex items-center gap-2">
                                                <span x-text="result.title"></span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                            <p class="text-sm text-theme-muted mt-1" x-text="result.description"></p>
                                            <template x-if="result.requires_login">
                                                <span class="text-xs text-yellow-600 dark:text-yellow-400">{{ __('Requiere iniciar sesion') }}</span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="result.type === 'article' || result.type === 'person_search'">
                                        <div>
                                            <a :href="result.url" target="_blank" rel="noopener"
                                               class="font-medium text-mf-primary hover:underline" x-text="result.title"></a>
                                            <p class="text-sm text-theme-muted mt-1" x-text="result.snippet"></p>
                                        </div>
                                    </template>
                                    <template x-if="result.type === 'error'">
                                        <div class="text-red-600 dark:text-red-400">
                                            <span class="font-medium" x-text="result.title"></span>
                                            <p class="text-sm" x-text="result.snippet"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- AI Analysis -->
        <div x-show="aiAnalysis && aiAnalysis.content" x-transition class="card mb-6">
            <div class="card-header flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <h2 class="text-lg font-semibold text-theme">{{ __('Analisis de IA') }}</h2>
                </div>
                <span class="text-sm text-theme-muted">
                    {{ $session->ai_provider }} / {{ $session->ai_model }}
                    <template x-if="tokensUsed > 0">
                        <span>(<span x-text="tokensUsed"></span> tokens)</span>
                    </template>
                </span>
            </div>
            <div class="card-body">
                <div class="ai-analysis-content text-gray-800 dark:text-gray-100" x-html="formatAnalysis(aiAnalysis.content)"></div>
            </div>
        </div>

        <!-- Suggestions -->
        <div x-show="suggestions && suggestions.length > 0" x-transition class="card mb-6">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-theme">{{ __('Sugerencias para continuar') }}</h2>
            </div>
            <div class="card-body">
                <ul class="space-y-3">
                    <template x-for="(suggestion, index) in suggestions" :key="index">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-theme" x-text="suggestion"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('research.index') }}" class="btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
            @if($session->person)
                <a href="{{ route('research.person', $session->person) }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    {{ __('Nueva investigacion') }}
                </a>
            @endif
            <form action="{{ route('research.destroy', $session) }}" method="POST" class="inline"
                  onsubmit="return confirm('{{ __('Eliminar esta sesion de investigacion?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 dark:text-red-400 border-red-300 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/30">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{ __('Eliminar') }}
                </button>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        .ai-analysis-content {
            line-height: 1.75;
            color: #374151;
        }
        .dark .ai-analysis-content {
            color: #e5e7eb;
        }
        .ai-analysis-content h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        .ai-analysis-content h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .ai-analysis-content h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .ai-analysis-content h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
        }
        .dark .ai-analysis-content h1,
        .dark .ai-analysis-content h2,
        .dark .ai-analysis-content h3,
        .dark .ai-analysis-content h4 {
            color: #f9fafb;
        }
        .ai-analysis-content p {
            margin: 0.75rem 0;
        }
        .ai-analysis-content ul, .ai-analysis-content ol {
            margin: 0.75rem 0;
            padding-left: 1.5rem;
        }
        .ai-analysis-content ul {
            list-style-type: disc;
        }
        .ai-analysis-content ol {
            list-style-type: decimal;
        }
        .ai-analysis-content li {
            margin: 0.375rem 0;
        }
        .ai-analysis-content li > ul, .ai-analysis-content li > ol {
            margin: 0.25rem 0;
        }
        .ai-analysis-content strong {
            font-weight: 600;
            color: #111827;
        }
        .dark .ai-analysis-content strong {
            color: #f3f4f6;
        }
        .ai-analysis-content em {
            font-style: italic;
        }
        .ai-analysis-content a {
            color: var(--mf-primary);
            text-decoration: underline;
        }
        .ai-analysis-content a:hover {
            text-decoration: none;
        }
        .ai-analysis-content blockquote {
            border-left: 4px solid var(--mf-primary);
            padding-left: 1rem;
            margin: 1rem 0;
            color: #6b7280;
            font-style: italic;
        }
        .dark .ai-analysis-content blockquote {
            color: #9ca3af;
        }
        .ai-analysis-content code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-family: ui-monospace, monospace;
        }
        .dark .ai-analysis-content code {
            background-color: #374151;
            color: #e5e7eb;
        }
        .ai-analysis-content pre {
            background-color: #1f2937;
            color: #e5e7eb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .ai-analysis-content pre code {
            background-color: transparent;
            padding: 0;
        }
        .ai-analysis-content hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 1.5rem 0;
        }
        .dark .ai-analysis-content hr {
            border-top-color: #4b5563;
        }
        .ai-analysis-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        .ai-analysis-content th, .ai-analysis-content td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem 0.75rem;
            text-align: left;
        }
        .dark .ai-analysis-content th, .dark .ai-analysis-content td {
            border-color: #4b5563;
        }
        .ai-analysis-content th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .dark .ai-analysis-content th {
            background-color: #374151;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Marked.js for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        // Configure marked for safe rendering
        marked.setOptions({
            breaks: true,
            gfm: true,
        });

        function researchSession(sessionId, initialStatus) {
            return {
                status: initialStatus,
                searchResults: @json($session->search_results ?? []),
                aiAnalysis: @json($session->ai_analysis ?? []),
                suggestions: @json($session->suggestions ?? []),
                tokensUsed: {{ $session->tokens_used ?? 0 }},
                isLoading: true,
                hasError: false,
                errorMessage: '',
                pollInterval: null,
                elapsedSeconds: 0,
                timerInterval: null,

                get statusLabel() {
                    const labels = {
                        'pending': '{{ __("Pendiente") }}',
                        'searching': '{{ __("Buscando...") }}',
                        'analyzing': '{{ __("Analizando...") }}',
                        'completed': '{{ __("Completado") }}',
                        'failed': '{{ __("Error") }}',
                    };
                    return labels[this.status] || this.status;
                },

                get statusClass() {
                    const classes = {
                        'completed': 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
                        'failed': 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
                    };
                    return classes[this.status] || 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300';
                },

                get loadingMessage() {
                    const messages = {
                        'pending': '{{ __("Preparando investigacion...") }}',
                        'searching': '{{ __("Buscando en fuentes genealogicas...") }}',
                        'analyzing': '{{ __("La IA esta analizando los resultados...") }}',
                    };
                    return messages[this.status] || '{{ __("Procesando...") }}';
                },

                get loadingSubmessage() {
                    const messages = {
                        'pending': '{{ __("Iniciando conexion con las fuentes de datos") }}',
                        'searching': '{{ __("Consultando FamilySearch, Wikipedia y otras fuentes...") }}',
                        'analyzing': '{{ __("Esto puede tomar unos segundos dependiendo del modelo") }}',
                    };
                    let msg = messages[this.status] || '';
                    if (this.elapsedSeconds > 0) {
                        msg += ` (${this.elapsedSeconds}s)`;
                    }
                    return msg;
                },

                stepCompleted(step) {
                    const order = ['pending', 'searching', 'analyzing', 'completed'];
                    const currentIndex = order.indexOf(this.status);
                    const stepIndex = order.indexOf(step);
                    return stepIndex < currentIndex;
                },

                startPolling() {
                    this.checkInitialState();

                    if (!['completed', 'failed'].includes(this.status)) {
                        this.pollInterval = setInterval(() => this.checkStatus(), 2000);
                        this.timerInterval = setInterval(() => this.elapsedSeconds++, 1000);
                    }
                },

                checkInitialState() {
                    if (['completed', 'failed'].includes(this.status)) {
                        this.isLoading = false;
                        if (this.status === 'failed') {
                            this.hasError = true;
                            this.errorMessage = this.aiAnalysis?.message || '{{ __("Error desconocido") }}';
                        }
                    }
                },

                async checkStatus() {
                    try {
                        const response = await fetch(`/research/${sessionId}/status`);
                        const data = await response.json();

                        this.status = data.status;
                        this.searchResults = data.search_results || {};
                        this.aiAnalysis = data.ai_analysis || {};
                        this.suggestions = data.suggestions || [];
                        this.tokensUsed = data.tokens_used || 0;

                        if (data.status === 'failed') {
                            this.hasError = true;
                            this.errorMessage = data.ai_analysis?.message || '{{ __("Error desconocido") }}';
                        }

                        if (['completed', 'failed'].includes(data.status)) {
                            this.isLoading = false;
                            clearInterval(this.pollInterval);
                            clearInterval(this.timerInterval);
                        }
                    } catch (error) {
                        console.error('Error checking status:', error);
                    }
                },

                formatAnalysis(content) {
                    if (!content) return '';

                    // Use marked.js to parse markdown
                    try {
                        return marked.parse(content);
                    } catch (e) {
                        console.error('Error parsing markdown:', e);
                        // Fallback: basic HTML escape and line breaks
                        return content
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/\n/g, '<br>');
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
