<x-app-layout>
    <x-slot name="title">{{ __('Arbol Genealogico') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-12">
            <div class="w-20 h-20 mx-auto mb-6 bg-mf-light rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Arbol Genealogico') }}</h1>
            <p class="text-gray-600 max-w-xl mx-auto">
                {{ __('Selecciona una persona para ver su arbol genealogico con ancestros y descendientes.') }}
            </p>
        </div>

        @if($persons->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <p class="text-gray-500 mb-4">{{ __('No hay personas registradas.') }}</p>
                    <a href="{{ route('persons.create') }}" class="btn-primary">{{ __('Crear primera persona') }}</a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold">{{ __('Seleccionar persona raiz') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tree.index') }}" method="GET" class="mb-6">
                        <div class="flex gap-4">
                            <input type="text" id="search-person" placeholder="{{ __('Buscar por nombre...') }}"
                                   class="form-input flex-1">
                        </div>
                    </form>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto" id="persons-list">
                        @foreach($persons as $person)
                            <a href="{{ route('tree.view', $person) }}"
                               class="person-card flex items-center gap-3 p-3 rounded-lg border hover:border-mf-primary hover:bg-mf-light transition-colors"
                               data-name="{{ strtolower($person->full_name) }}">
                                @if($person->photo_path)
                                    <img src="{{ Storage::url($person->photo_path) }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                        <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold">{{ substr($person->first_name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $person->full_name }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($person->birth_date)
                                            {{ $person->birth_date->format('Y') }}
                                        @endif
                                        @if($person->has_ethnic_heritage)
                                            <span class="text-blue-600 ml-1">*</span>
                                        @endif
                                    </p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.getElementById('search-person')?.addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.person-card').forEach(card => {
                const name = card.dataset.name;
                card.style.display = name.includes(search) ? 'flex' : 'none';
            });
        });
    </script>
    @endpush
</x-app-layout>
