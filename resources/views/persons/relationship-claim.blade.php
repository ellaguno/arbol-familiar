<x-app-layout>
    <x-slot name="title">{{ __('Declarar parentesco') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h1 class="text-xl font-bold text-theme">{{ __('Declarar relacion familiar') }}</h1>
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
                                {{ __('Nacimiento') }}: {{ $person->birth_date->format('Y') }}
                                @if($person->birth_place)
                                    - {{ $person->birth_place }}
                                @endif
                            </p>
                        @endif
                        @if($person->gender)
                            <p class="text-sm text-theme-secondary">
                                {{ $person->gender === 'M' ? __('Masculino') : ($person->gender === 'F' ? __('Femenino') : __('No especificado')) }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Explicacion -->
                <div class="mb-6">
                    <div class="flex items-start gap-3 p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-medium text-indigo-800 dark:text-indigo-300">{{ __('Como funciona') }}</h3>
                            <p class="text-sm text-indigo-700 dark:text-indigo-400 mt-1">
                                {{ __('Al declarar una relacion, enviaras una solicitud a') }}
                                <strong>{{ $reviewerLabel ?? __('el responsable del perfil') }}</strong>
                                {{ __('quien debera aprobar la relacion. Una vez aprobada, se creara la conexion familiar entre ambos perfiles.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('persons.relationship-claim.send', $person) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Tipo de relacion -->
                    <div>
                        <label class="form-label">{{ __('¿Cual es tu relacion con esta persona?') }}</label>
                        <p class="form-help mb-3">{{ __('Selecciona como se relaciona :person contigo.', ['person' => $person->first_name]) }}</p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @php
                                $relationships = [
                                    'father' => ['label' => __('Es mi padre'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                    'mother' => ['label' => __('Es mi madre'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                    'child' => ['label' => __('Es mi hijo/a'), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                                    'sibling' => ['label' => __('Es mi hermano/a'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                                    'spouse' => ['label' => __('Es mi conyuge'), 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                ];
                            @endphp

                            @foreach($relationships as $value => $rel)
                                <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition-all
                                    {{ old('relationship_type') === $value ? 'border-mf-primary bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-mf-primary' }}">
                                    <input type="radio" name="relationship_type" value="{{ $value }}" class="sr-only peer"
                                           {{ old('relationship_type') === $value ? 'checked' : '' }}>
                                    <svg class="w-8 h-8 mb-2 text-theme-muted peer-checked:text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rel['icon'] }}"/>
                                    </svg>
                                    <span class="text-sm font-medium text-center text-theme peer-checked:text-mf-primary">{{ $rel['label'] }}</span>
                                    <div class="absolute top-2 right-2 w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600 peer-checked:border-mf-primary peer-checked:bg-mf-primary flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white hidden peer-checked:block"></div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('relationship_type')
                            <p class="form-error mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mensaje opcional -->
                    <div>
                        <label for="message" class="form-label">{{ __('Mensaje (opcional)') }}</label>
                        <textarea name="message" id="message" rows="3" class="form-input"
                                  placeholder="{{ __('Agrega informacion que ayude a verificar tu relacion familiar...') }}">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            {{ __('Enviar solicitud de parentesco') }}
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
