<x-app-layout>
    <x-slot name="title">{{ __('Reporte de Ancestros') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <li><span class="text-theme-secondary font-medium">{{ __('Reporte de Ancestros') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Reporte de Ancestros') }}</h1>
                <p class="text-theme-muted mt-1">{{ __('Formato Ahnentafel - :count ancestros encontrados', ['count' => $totalFound - 1]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.ancestors.pdf', ['person' => $person, 'generations' => $generations]) }}"
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
                <form method="GET" action="{{ route('reports.ancestors', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Tabla Ahnentafel -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-16">#</th>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Nacimiento') }}</th>
                                <th>{{ __('Defuncion') }}</th>
                                <th>{{ __('Generacion') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentGen = -1; @endphp
                            @foreach($ahnentafel as $num => $entry)
                                @if($entry['generation'] !== $currentGen)
                                    @php $currentGen = $entry['generation']; @endphp
                                    <tr>
                                        <td colspan="5" class="bg-theme-secondary">
                                            <span class="font-semibold text-theme-secondary text-sm">
                                                {{ $entry['generation_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                                <tr class="{{ $entry['person']->has_ethnic_heritage ? 'border-l-4 border-l-mf-secondary' : '' }}">
                                    <td class="text-theme-muted font-mono text-sm">{{ $num }}</td>
                                    <td>
                                        <a href="{{ route('persons.show', $entry['person']) }}" class="text-mf-primary hover:underline font-medium">
                                            {{ $entry['person']->shouldProtectMinorData() ? $entry['person']->first_name : $entry['person']->full_name }}
                                        </a>
                                        @if($entry['person']->gender)
                                            <span class="text-theme-muted text-xs ml-1">
                                                {{ $entry['person']->gender === 'M' ? '♂' : ($entry['person']->gender === 'F' ? '♀' : '') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        @if(!$entry['person']->shouldProtectMinorData())
                                            {{ $entry['person']->birth_date_formatted ?? $entry['person']->birth_year ?? '' }}
                                            @if($entry['person']->birth_place)
                                                <span class="text-theme-muted block text-xs">{{ $entry['person']->birth_place }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        @if(!$entry['person']->is_living && !$entry['person']->shouldProtectMinorData())
                                            {{ $entry['person']->death_date_formatted ?? $entry['person']->death_year ?? '' }}
                                            @if($entry['person']->death_place)
                                                <span class="text-theme-muted block text-xs">{{ $entry['person']->death_place }}</span>
                                            @endif
                                        @elseif($entry['person']->is_living)
                                            <span class="text-green-600 text-xs">{{ __('Vivo/a') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-theme-muted">{{ $entry['generation'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
