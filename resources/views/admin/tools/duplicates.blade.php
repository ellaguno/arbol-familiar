<x-app-layout>
    <x-slot name="title">{{ __('Buscar duplicados') }} - {{ __('Herramientas') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <nav class="text-sm text-theme-muted mb-1">
                    <a href="{{ route('admin.index') }}" class="hover:text-mf-primary">{{ __('Admin') }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.tools') }}" class="hover:text-mf-primary">{{ __('Herramientas') }}</a>
                    <span class="mx-1">/</span>
                    <span>{{ __('Buscar duplicados') }}</span>
                </nav>
                <h1 class="text-3xl font-bold text-theme">{{ __('Buscar duplicados') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Detecta personas con nombre y apellido identicos') }}</p>
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
                    <p class="text-yellow-800 dark:text-yellow-200 font-medium">{{ __('Revisa con cuidado antes de fusionar') }}</p>
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1">
                        {{ __('Personas con el mismo nombre no siempre son duplicados (ej. padre e hijo). Usa "Comparar" para revisar los datos antes de actuar. La fusion es irreversible.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300 font-medium">{{ session('success') }}</p>
                @if(session('details'))
                    <ul class="mt-2 text-sm text-green-600 dark:text-green-400 space-y-1">
                        @foreach(session('details') as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-700 dark:text-red-300 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Resumen -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-theme">{{ number_format($totalPersons) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Total personas') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($totalGroups) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Grupos con coincidencias') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalDuplicates) }}</p>
                        <p class="text-sm text-theme-muted">{{ __('Posibles duplicados') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @foreach(['alta' => __('Alta'), 'media' => __('Media'), 'baja' => __('Baja')] as $conf => $label)
            @if(count($byConfidence[$conf]) > 0)
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $conf === 'alta' ? 'bg-green-500' : ($conf === 'media' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                            {{ __('Confianza') }} {{ $label }}
                            <span class="text-sm font-normal text-theme-muted">({{ count($byConfidence[$conf]) }} {{ __('grupos') }})</span>
                        </h2>
                    </div>
                    <div class="divide-y divide-theme-light">
                        @foreach($byConfidence[$conf] as $group)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-theme">{{ $group['key'] }}</h3>
                                    <span class="text-sm text-theme-muted">{{ count($group['persons']) }} {{ __('personas') }}</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-xs text-theme-muted uppercase">
                                                <th class="pb-2 pr-4">#</th>
                                                <th class="pb-2 pr-4">{{ __('Nombre completo') }}</th>
                                                <th class="pb-2 pr-4">{{ __('Nacimiento') }}</th>
                                                <th class="pb-2 pr-4">{{ __('Genero') }}</th>
                                                <th class="pb-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['persons'] as $person)
                                                <tr>
                                                    <td class="py-1 pr-4 text-theme-muted">{{ $person->id }}</td>
                                                    <td class="py-1 pr-4">
                                                        <a href="{{ route('persons.show', $person) }}" target="_blank" class="text-theme hover:text-mf-primary">
                                                            {{ $person->first_name }} {{ $person->patronymic }} {{ $person->matronymic }}
                                                        </a>
                                                    </td>
                                                    <td class="py-1 pr-4 text-theme-muted">{{ $person->birth_date ?? '-' }}</td>
                                                    <td class="py-1 pr-4 text-theme-muted">{{ $person->gender }}</td>
                                                    <td class="py-1 text-right">
                                                        @if(!$loop->first)
                                                            <a href="{{ route('admin.tools.duplicates.compare', [$group['persons']->first(), $person]) }}"
                                                               class="text-sm text-mf-primary hover:underline">
                                                                {{ __('Comparar con #:id', ['id' => $group['persons']->first()->id]) }}
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @if($totalGroups === 0)
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg text-theme font-medium">{{ __('No se encontraron duplicados') }}</p>
                    <p class="text-theme-muted mt-1">{{ __('Todas las personas tienen nombres unicos.') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
