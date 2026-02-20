<x-app-layout>
    <x-slot name="title">{{ __('Comparar personas') }} - {{ __('Herramientas') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <nav class="text-sm text-theme-muted mb-1">
                    <a href="{{ route('admin.index') }}" class="hover:text-mf-primary">{{ __('Admin') }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.tools') }}" class="hover:text-mf-primary">{{ __('Herramientas') }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.tools.duplicates') }}" class="hover:text-mf-primary">{{ __('Duplicados') }}</a>
                    <span class="mx-1">/</span>
                    <span>{{ __('Comparar') }}</span>
                </nav>
                <h1 class="text-3xl font-bold text-theme">{{ __('Comparar personas') }}</h1>
            </div>
            <a href="{{ route('admin.tools.duplicates') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <!-- Encabezados de personas -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="card border-blue-300 dark:border-blue-700">
                <div class="card-body flex items-center gap-4">
                    @if($personA->photo_path)
                        <img src="{{ asset('storage/' . $personA->photo_path) }}" class="w-14 h-14 rounded-full object-cover" alt="">
                    @else
                        <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg">
                            {{ mb_substr($personA->first_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-bold text-theme text-lg">
                            <span class="text-blue-600 dark:text-blue-400">#{{ $personA->id }}</span>
                            {{ $personA->first_name }} {{ $personA->patronymic }} {{ $personA->matronymic }}
                        </p>
                        <a href="{{ route('persons.show', $personA) }}" target="_blank" class="text-sm text-mf-primary hover:underline">{{ __('Ver perfil') }}</a>
                    </div>
                </div>
            </div>
            <div class="card border-purple-300 dark:border-purple-700">
                <div class="card-body flex items-center gap-4">
                    @if($personB->photo_path)
                        <img src="{{ asset('storage/' . $personB->photo_path) }}" class="w-14 h-14 rounded-full object-cover" alt="">
                    @else
                        <div class="w-14 h-14 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold text-lg">
                            {{ mb_substr($personB->first_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-bold text-theme text-lg">
                            <span class="text-purple-600 dark:text-purple-400">#{{ $personB->id }}</span>
                            {{ $personB->first_name }} {{ $personB->patronymic }} {{ $personB->matronymic }}
                        </p>
                        <a href="{{ route('persons.show', $personB) }}" target="_blank" class="text-sm text-mf-primary hover:underline">{{ __('Ver perfil') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla comparativa -->
        <div class="card mb-6">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ __('Comparacion de datos') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-theme-light">
                            <th class="px-4 py-3 text-left text-xs font-medium text-theme-muted uppercase w-1/4">{{ __('Campo') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-blue-600 dark:text-blue-400 uppercase w-5/12">#{{ $personA->id }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-purple-600 dark:text-purple-400 uppercase w-5/12">#{{ $personB->id }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme-light">
                        @foreach($fields as $field)
                            <tr class="{{ $field['different'] ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                <td class="px-4 py-2 text-sm font-medium text-theme-muted">
                                    {{ $field['label'] }}
                                    @if($field['different'])
                                        <span class="text-yellow-600 dark:text-yellow-400 ml-1" title="{{ __('Valores diferentes') }}">*</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-theme">{{ $field['value_a'] ?: '-' }}</td>
                                <td class="px-4 py-2 text-sm text-theme">{{ $field['value_b'] ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Relaciones vinculadas -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-blue-600 dark:text-blue-400">{{ __('Relaciones de') }} #{{ $personA->id }}</h3>
                </div>
                <div class="card-body text-sm space-y-2">
                    <p><span class="text-theme-muted">{{ __('Familias como hijo:') }}</span> {{ $personA->familiesAsChild->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Familias como padre/madre:') }}</span> {{ $personA->familiesAsHusband->count() + $personA->familiesAsWife->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Eventos:') }}</span> {{ $personA->events->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Archivos multimedia:') }}</span> {{ $personA->media->count() }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-purple-600 dark:text-purple-400">{{ __('Relaciones de') }} #{{ $personB->id }}</h3>
                </div>
                <div class="card-body text-sm space-y-2">
                    <p><span class="text-theme-muted">{{ __('Familias como hijo:') }}</span> {{ $personB->familiesAsChild->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Familias como padre/madre:') }}</span> {{ $personB->familiesAsHusband->count() + $personB->familiesAsWife->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Eventos:') }}</span> {{ $personB->events->count() }}</p>
                    <p><span class="text-theme-muted">{{ __('Archivos multimedia:') }}</span> {{ $personB->media->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ __('Acciones') }}</h2>
            </div>
            <div class="card-body space-y-6">
                <!-- Fusionar -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="font-semibold text-theme mb-3">{{ __('Fusionar personas') }}</h3>
                    <p class="text-sm text-theme-muted mb-4">
                        {{ __('Transfiere todas las relaciones, eventos y archivos del duplicado a la persona principal. Los campos vacios del principal se completan con datos del duplicado. El duplicado se elimina.') }}
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <form method="POST" action="{{ route('admin.tools.duplicates.merge') }}"
                              onsubmit="return confirm('{{ __('¿Fusionar #:dup en #:pri? Esta accion es irreversible.', ['dup' => $personB->id, 'pri' => $personA->id]) }}')">
                            @csrf
                            <input type="hidden" name="primary_id" value="{{ $personA->id }}">
                            <input type="hidden" name="duplicate_id" value="{{ $personB->id }}">
                            <button type="submit" class="btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                {{ __('Conservar #:id, eliminar #:dup', ['id' => $personA->id, 'dup' => $personB->id]) }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.tools.duplicates.merge') }}"
                              onsubmit="return confirm('{{ __('¿Fusionar #:dup en #:pri? Esta accion es irreversible.', ['dup' => $personA->id, 'pri' => $personB->id]) }}')">
                            @csrf
                            <input type="hidden" name="primary_id" value="{{ $personB->id }}">
                            <input type="hidden" name="duplicate_id" value="{{ $personA->id }}">
                            <button type="submit" class="btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                </svg>
                                {{ __('Conservar #:id, eliminar #:dup', ['id' => $personB->id, 'dup' => $personA->id]) }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Eliminar -->
                <div class="p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
                    <h3 class="font-semibold text-theme mb-3">{{ __('Eliminar sin fusionar') }}</h3>
                    <p class="text-sm text-theme-muted mb-4">
                        {{ __('Elimina la persona seleccionada sin transferir sus datos. Las relaciones vinculadas se perderan.') }}
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <form method="POST" action="{{ route('admin.tools.duplicates.delete') }}"
                              onsubmit="return confirm('{{ __('¿Eliminar #:id definitivamente? Se perderan sus relaciones.', ['id' => $personA->id]) }}')">
                            @csrf
                            <input type="hidden" name="person_id" value="{{ $personA->id }}">
                            <button type="submit" class="btn-outline w-full text-red-600 border-red-300 hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20">
                                {{ __('Eliminar #:id', ['id' => $personA->id]) }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.tools.duplicates.delete') }}"
                              onsubmit="return confirm('{{ __('¿Eliminar #:id definitivamente? Se perderan sus relaciones.', ['id' => $personB->id]) }}')">
                            @csrf
                            <input type="hidden" name="person_id" value="{{ $personB->id }}">
                            <button type="submit" class="btn-outline w-full text-red-600 border-red-300 hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/20">
                                {{ __('Eliminar #:id', ['id' => $personB->id]) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
