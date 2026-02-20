<x-app-layout>
    <x-slot name="title">{{ __('Corregir apellidos') }} - {{ __('Herramientas') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb y titulo -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <nav class="text-sm text-theme-muted mb-1">
                    <a href="{{ route('admin.index') }}" class="hover:text-mf-primary">{{ __('Admin') }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.tools') }}" class="hover:text-mf-primary">{{ __('Herramientas') }}</a>
                    <span class="mx-1">/</span>
                    <span>{{ __('Corregir apellidos') }}</span>
                </nav>
                <h1 class="text-3xl font-bold text-theme">{{ __('Corregir apellidos') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Revision y separacion de apellidos patronymic/matronymic') }}</p>
            </div>
            <a href="{{ route('admin.tools') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <!-- Advertencia -->
        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-yellow-800 dark:text-yellow-200 font-medium">{{ __('Usa esta herramienta con cuidado') }}</p>
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1">
                        {{ __('Revisa cada propuesta antes de aplicarla. Los cambios de confianza "alta" se basan en datos de ambos padres. Los de confianza "media" usan un solo padre, hermano o hijo. Los de confianza "baja" asumen separacion sin datos de referencia.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mensaje de exito -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300 font-medium">{{ session('success') }}</p>
                @if(session('details'))
                    <ul class="mt-2 text-sm text-green-600 dark:text-green-400 space-y-1">
                        @foreach(session('details') as $detail)
                            <li class="{{ $detail['error'] ? 'text-red-600 dark:text-red-400' : '' }}">
                                {{ $detail['message'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <!-- Resumen -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-theme">{{ number_format($total) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Total personas') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($fixable) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Con propuestas') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($uncertain) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('No separables') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-theme-secondary">{{ number_format($skipped) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Sin cambios') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($fixable > 0)
            <form method="POST" action="{{ route('admin.tools.fix-surnames.apply') }}" x-data="fixSurnames()">
                @csrf

                <!-- Barra de acciones -->
                <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border border-theme rounded-lg p-3 mb-6 flex flex-wrap items-center gap-3 shadow-sm">
                    <button type="button" @click="toggleAll(true)" class="btn-outline text-sm py-1.5 px-3">
                        {{ __('Seleccionar todos') }}
                    </button>
                    <button type="button" @click="toggleAll(false)" class="btn-outline text-sm py-1.5 px-3">
                        {{ __('Deseleccionar todos') }}
                    </button>
                    <button type="button" @click="toggleConf('alta')" class="btn-outline text-sm py-1.5 px-3">
                        {{ __('Solo confianza alta') }}
                    </button>
                    <span class="text-sm text-theme-muted" x-text="selected + ' {{ __('seleccionados') }}'"></span>
                    <button type="submit" class="btn-primary text-sm py-1.5 px-4 ml-auto" x-bind:disabled="selected === 0">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Aplicar seleccionados') }}
                    </button>
                </div>

                <!-- Tabla por confianza -->
                @foreach(['alta' => __('Alta'), 'media' => __('Media'), 'baja' => __('Baja')] as $conf => $label)
                    @if(count($byConfidence[$conf]) > 0)
                        <div class="card mb-6">
                            <div class="card-header">
                                <h2 class="text-lg font-semibold flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ $conf === 'alta' ? 'bg-green-500' : ($conf === 'media' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                    {{ __('Confianza') }} {{ $label }}
                                    <span class="text-sm font-normal text-theme-muted">({{ count($byConfidence[$conf]) }})</span>
                                </h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-theme-light">
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase w-10"></th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase w-16">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Nombre') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Actual') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Propuesto') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Razon') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-theme-light">
                                        @foreach($byConfidence[$conf] as $p)
                                            <tr class="hover:bg-theme-secondary transition-colors">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox"
                                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 surname-check"
                                                           name="apply[{{ $p['person']->id }}][pat]"
                                                           value="{{ $p['new_pat'] }}"
                                                           data-conf="{{ $conf }}"
                                                           data-person-id="{{ $p['person']->id }}"
                                                           {{ $conf === 'alta' ? 'checked' : '' }}
                                                           @change="updateCount()">
                                                    <input type="hidden"
                                                           name="apply[{{ $p['person']->id }}][mat]"
                                                           value="{{ $p['new_mat'] }}"
                                                           {{ $conf !== 'alta' ? 'disabled' : '' }}>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-theme-muted">{{ $p['person']->id }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-theme">
                                                    <a href="{{ route('persons.show', $p['person']) }}" target="_blank" class="hover:text-mf-primary">
                                                        {{ $p['person']->first_name }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="text-red-500 line-through">{{ $p['old_pat'] }}</span>
                                                    @if($p['old_mat'])
                                                        <span class="text-red-500 line-through ml-1">{{ $p['old_mat'] }}</span>
                                                    @else
                                                        <span class="text-theme-muted italic ml-1">({{ __('vacio') }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $p['new_pat'] }}</span>
                                                    <span class="text-green-600 dark:text-green-400 font-medium ml-1">{{ $p['new_mat'] }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-xs text-theme-muted">{{ $p['reason'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach
            </form>
        @endif

        <!-- No separables -->
        @if(count($warnings) > 0)
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-gray-500"></span>
                        {{ __('No se pudieron separar') }}
                        <span class="text-sm font-normal text-theme-muted">({{ count($warnings) }})</span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-theme-light">
                                <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase w-16">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Nombre') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Patronymic') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Nota') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-theme-light">
                            @foreach($warnings as $w)
                                <tr class="hover:bg-theme-secondary transition-colors">
                                    <td class="px-4 py-3 text-sm text-theme-muted">{{ $w['person']->id }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-theme">
                                        <a href="{{ route('persons.show', $w['person']) }}" target="_blank" class="hover:text-mf-primary">
                                            {{ $w['person']->first_name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-theme">"{{ $w['old_pat'] }}"</td>
                                    <td class="px-4 py-3 text-xs text-theme-muted">{{ $w['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($fixable === 0 && $uncertain === 0)
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg text-theme font-medium">{{ __('Todos los apellidos estan correctos') }}</p>
                    <p class="text-theme-muted mt-1">{{ __('No hay cambios necesarios.') }}</p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
    function fixSurnames() {
        return {
            selected: document.querySelectorAll('.surname-check:checked').length || {{ count($byConfidence['alta']) }},
            updateCount() {
                this.selected = document.querySelectorAll('.surname-check:checked').length;
                // Sync hidden inputs
                document.querySelectorAll('.surname-check').forEach(cb => {
                    const hidden = cb.closest('tr').querySelector('input[type=hidden]');
                    if (hidden) hidden.disabled = !cb.checked;
                });
            },
            toggleAll(state) {
                document.querySelectorAll('.surname-check').forEach(cb => {
                    cb.checked = state;
                    const hidden = cb.closest('tr').querySelector('input[type=hidden]');
                    if (hidden) hidden.disabled = !state;
                });
                this.updateCount();
            },
            toggleConf(conf) {
                document.querySelectorAll('.surname-check').forEach(cb => {
                    const isConf = cb.dataset.conf === conf;
                    cb.checked = isConf;
                    const hidden = cb.closest('tr').querySelector('input[type=hidden]');
                    if (hidden) hidden.disabled = !isConf;
                });
                this.updateCount();
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
