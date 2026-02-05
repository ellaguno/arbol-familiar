<x-app-layout>
    <x-slot name="title">{{ __('Cuadro de Pedigri') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <li><span class="text-theme-secondary font-medium">{{ __('Cuadro de Pedigri') }}</span></li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-theme">{{ __('Cuadro de Pedigri') }}</h1>
                <p class="text-theme-muted mt-1">{{ __(':count ancestros encontrados en :gen generaciones', ['count' => $totalFound - 1, 'gen' => $generations]) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.pedigree.svg', ['person' => $person, 'generations' => $generations]) }}"
                   class="btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Descargar SVG') }}
                </a>
                <a href="{{ route('reports.pedigree.pdf', ['person' => $person, 'generations' => $generations]) }}"
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
                <form method="GET" action="{{ route('reports.pedigree', $person) }}" class="flex items-center gap-4">
                    <label for="generations" class="form-label mb-0">{{ __('Generaciones') }}:</label>
                    <select name="generations" id="generations" class="form-select w-auto" onchange="this.form.submit()">
                        @for($i = 2; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ $generations == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Cuadro de Pedigri -->
        <div class="card">
            <div class="card-body p-0">
                <div class="overflow-auto" style="max-height: 80vh;">
                    @include('reports-pedigree::svg')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
