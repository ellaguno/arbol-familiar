<x-app-layout>
    <x-slot name="title">{{ __('Investigacion') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Investigacion Genealogica') }}</h1>
                <p class="text-theme-muted mt-1">{{ __('Busca informacion genealogica con ayuda de IA') }}</p>
            </div>
        </div>

        @if(!$aiConfigured)
            <div class="card mb-8">
                <div class="card-body">
                    <div class="flex items-center gap-4 text-yellow-600 dark:text-yellow-400">
                        <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold">{{ __('IA no configurada') }}</h3>
                            <p class="text-sm">{{ __('El administrador debe configurar un proveedor de IA para usar esta funcionalidad.') }}</p>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.research.settings') }}" class="text-sm underline hover:no-underline">{{ __('Ir a configuracion') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Quick search -->
            <div class="card mb-8">
                <div class="card-body">
                    <form action="{{ route('research.search') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-theme mb-2">{{ __('Que deseas investigar?') }}</label>
                            <textarea name="query" rows="2" class="form-input w-full" required
                                      placeholder="{{ __('Ej: Buscar registros de Juan Perez nacido en 1890 en Guadalajara...') }}">{{ old('query') }}</textarea>
                            @error('query')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Sources -->
                            <div>
                                <label class="block text-sm font-medium text-theme mb-2">{{ __('Fuentes') }}</label>
                                <div class="space-y-2">
                                    @foreach($sources as $source)
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="sources[]" value="{{ $source->getId() }}"
                                                   class="form-checkbox" {{ $source->isEnabled() ? 'checked' : '' }}>
                                            <span class="text-theme">{{ $source->getName() }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('sources')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- AI Provider Info (read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-theme mb-2">{{ __('Asistente de IA') }}</label>
                                <div class="p-3 bg-theme-secondary rounded-lg border border-theme">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-theme">{{ $providerName }}</p>
                                            <p class="text-sm text-theme-muted">{{ $defaultModel }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                {{ __('Investigar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Recent sessions -->
        @if($recentSessions->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Investigaciones recientes') }}</h2>
                </div>
                <div class="divide-y divide-theme">
                    @foreach($recentSessions as $session)
                        <a href="{{ route('research.session', $session) }}"
                           class="block p-4 hover:bg-theme-secondary transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-theme truncate">{{ Str::limit($session->query, 80) }}</h3>
                                    @if($session->person)
                                        <p class="text-sm text-theme-muted mt-1">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $session->person->full_name }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap
                                        {{ $session->status === 'completed' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : '' }}
                                        {{ $session->status === 'failed' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}
                                        {{ in_array($session->status, ['pending', 'searching', 'analyzing']) ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300' : '' }}">
                                        {{ $session->status_label }}
                                    </span>
                                    <span class="text-sm text-theme-muted whitespace-nowrap">
                                        {{ $session->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-theme-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-theme mb-2">{{ __('Sin investigaciones') }}</h3>
                    <p class="text-theme-muted">{{ __('Aun no has realizado ninguna investigacion. Usa el formulario arriba para comenzar.') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
