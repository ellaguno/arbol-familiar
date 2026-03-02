<x-app-layout>
    <x-slot name="title">{{ __('Marcar fallecidos') }} - {{ __('Herramientas') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <nav class="text-sm text-theme-muted mb-1">
                    <a href="{{ route('admin.index') }}" class="hover:text-mf-primary">{{ __('Admin') }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.tools') }}" class="hover:text-mf-primary">{{ __('Herramientas') }}</a>
                    <span class="mx-1">/</span>
                    <span>{{ __('Marcar fallecidos') }}</span>
                </nav>
                <h1 class="text-3xl font-bold text-theme">{{ __('Marcar fallecidos') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Deteccion de personas vivas con edad imposible (100+ anos)') }}</p>
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
                    <p class="text-yellow-800 dark:text-yellow-200 font-medium">{{ __('Revisa antes de aplicar') }}</p>
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1">
                        {{ __('Esta herramienta marca personas como fallecidas pero NO establece fecha de defuncion. Confianza alta: fecha exacta de nacimiento indica 100+ anos, o es ancestro directo. Media: solo tiene ano de nacimiento. Baja: es ancestro de alguien con datos aproximados.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mensaje de exito -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Resumen -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-theme">{{ number_format($totalLiving) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Personas vivas') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalFound) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Deben ser fallecidos') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalAlta) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Confianza alta') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($totalMedia) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Confianza media') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-500 dark:text-red-400">{{ number_format($totalBaja) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Confianza baja') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($totalFound > 0)
            <form method="POST" action="{{ route('admin.tools.mark-deceased.apply') }}"
                  x-data="markDeceased()"
                  @submit="return confirm('{{ __('Marcar las personas seleccionadas como fallecidas? Esta accion no establece fecha de defuncion.') }}')">
                @csrf

                <!-- Barra de acciones (sticky) -->
                <div class="sticky top-0 z-10 bg-theme-card border border-theme rounded-lg p-3 mb-6 flex flex-wrap items-center gap-3 shadow-sm">
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
                    <button type="submit" class="btn-primary text-sm py-1.5 px-4 ml-auto" :disabled="selected === 0">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Marcar como fallecidos') }}
                    </button>
                </div>

                <!-- Tablas por confianza -->
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
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Nacimiento') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase w-20">{{ __('Edad') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase">{{ __('Razon') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-theme-light">
                                        @foreach($byConfidence[$conf] as $p)
                                            <tr class="hover:bg-theme-secondary transition-colors">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox"
                                                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-4 h-4 deceased-check"
                                                           name="mark[]"
                                                           value="{{ $p['person']->id }}"
                                                           data-conf="{{ $conf }}"
                                                           {{ $conf === 'alta' ? 'checked' : '' }}
                                                           @change="updateCount()">
                                                </td>
                                                <td class="px-4 py-3 text-sm text-theme-muted">{{ $p['person']->id }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-theme">
                                                    <a href="{{ route('persons.show', $p['person']) }}" target="_blank" class="hover:text-mf-primary">
                                                        {{ $p['person']->first_name }} {{ $p['person']->patronymic }} {{ $p['person']->matronymic }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-theme-muted">
                                                    {{ $p['person']->birth_date_formatted ?? __('Desconocido') }}
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    @if($p['age'])
                                                        <span class="font-semibold {{ $p['age'] >= 100 ? 'text-red-600 dark:text-red-400' : 'text-theme' }}">
                                                            {{ $p['age'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-theme-muted italic">{{ __('N/D') }}</span>
                                                    @endif
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
        @else
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg text-theme font-medium">{{ __('No se encontraron personas por marcar') }}</p>
                    <p class="text-theme-muted mt-1">{{ __('Todas las personas con 100+ anos ya estan marcadas como fallecidas.') }}</p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
    function markDeceased() {
        return {
            selected: 0,
            init() {
                this.updateCount();
            },
            updateCount() {
                this.selected = document.querySelectorAll('.deceased-check:checked').length;
            },
            toggleAll(state) {
                document.querySelectorAll('.deceased-check').forEach(cb => cb.checked = state);
                this.updateCount();
            },
            toggleConf(conf) {
                document.querySelectorAll('.deceased-check').forEach(cb => {
                    cb.checked = (cb.dataset.conf === conf);
                });
                this.updateCount();
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
