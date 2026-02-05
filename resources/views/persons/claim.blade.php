<x-app-layout>
    <x-slot name="title">{{ __('Reclamar perfil') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h1 class="text-xl font-bold text-theme">{{ __('Reclamar este perfil') }}</h1>
            </div>
            <div class="card-body">
                <!-- Informacion de la persona -->
                <div class="flex items-start gap-4 mb-6 p-4 bg-theme-secondary rounded-lg">
                    <div class="flex-shrink-0">
                        @if($person->photo_path)
                            <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->full_name }}"
                                 class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-mf-primary flex items-center justify-center text-white text-2xl font-bold">
                                {{ substr($person->first_name, 0, 1) }}{{ substr($person->patronymic, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-theme">{{ $person->full_name }}</h2>
                        @if($person->birth_date)
                            <p class="text-sm text-theme-secondary">
                                {{ __('Nacimiento') }}: {{ $person->birth_date->format('d/m/Y') }}
                                @if($person->birth_place)
                                    - {{ $person->birth_place }}
                                @endif
                            </p>
                        @endif
                        @if($person->residence_place)
                            <p class="text-sm text-theme-secondary">{{ __('Residencia') }}: {{ $person->residence_place }}</p>
                        @endif
                    </div>
                </div>

                <!-- Explicacion -->
                <div class="mb-6">
                    <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-medium text-blue-800 dark:text-blue-300">{{ __('Como funciona') }}</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                {{ __('Al reclamar este perfil, enviaras una solicitud a') }}
                                <strong>{{ $creator?->email ?? __('el creador') }}</strong>
                                {{ __('quien debera aprobar que tu cuenta se vincule con esta persona.') }}
                            </p>
                            <p class="text-sm text-blue-700 dark:text-blue-400 mt-2">
                                {{ __('Una vez aprobado, podras editar la informacion de este perfil y sera tu perfil principal en el sistema.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('persons.claim.send', $person) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="message" class="form-label">{{ __('Mensaje (opcional)') }}</label>
                        <textarea name="message" id="message" rows="4" class="form-input"
                                  placeholder="{{ __('Explica por que eres esta persona o agrega informacion que ayude a verificar tu identidad...') }}">{{ old('message') }}</textarea>
                        <p class="form-help">{{ __('Puedes agregar detalles que ayuden a confirmar tu identidad.') }}</p>
                        @error('message')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Enviar solicitud') }}
                        </button>
                        <a href="{{ route('persons.show', $person) }}" class="btn-outline">
                            {{ __('Cancelar') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
