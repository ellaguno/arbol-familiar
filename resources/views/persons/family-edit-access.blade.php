<x-app-layout>
    <x-slot name="title">{{ __('Solicitar acceso para editar familia') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h1 class="text-xl font-bold text-theme">{{ __('Solicitar acceso para editar familia directa') }}</h1>
                <p class="text-sm text-theme-secondary mt-1">{{ __('Solicita permiso para editar la informacion de tus familiares cercanos') }}</p>
            </div>
            <div class="card-body">
                <!-- Tu perfil -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        @if($person->photo_path)
                            <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->full_name }}"
                                 class="w-14 h-14 rounded-full object-cover">
                        @else
                            <div class="w-14 h-14 rounded-full bg-blue-200 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg font-bold">
                                {{ substr($person->first_name, 0, 1) }}{{ substr($person->patronymic, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-theme">{{ $person->full_name }}</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400">{{ __('Tu perfil') }}</p>
                        </div>
                    </div>
                </div>

                @if($familyToRequest->isEmpty())
                    <!-- No hay familiares para solicitar -->
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-theme mb-2">{{ __('Ya tienes acceso completo') }}</h3>
                        <p class="text-theme-secondary mb-4">{{ __('Ya puedes editar toda tu familia directa o no tienes familiares registrados aun.') }}</p>
                        <a href="{{ route('tree.index') }}" class="btn-primary inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                            {{ __('Ver arbol familiar') }}
                        </a>
                    </div>
                @else
                    <!-- Explicacion -->
                    <div class="mb-6">
                        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="font-medium text-amber-800 dark:text-amber-300">{{ __('Como funciona?') }}</h3>
                                <ul class="text-sm text-amber-700 dark:text-amber-400 mt-2 space-y-1 list-disc list-inside">
                                    <li>{{ __('Selecciona los familiares que deseas poder editar') }}</li>
                                    <li>{{ __('Se enviara una solicitud al creador de cada perfil') }}</li>
                                    <li>{{ __('Una vez aprobada, podras editar la informacion de esa persona') }}</li>
                                    <li>{{ __('El creador puede revocar el acceso en cualquier momento') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <form action="{{ route('persons.family-edit-access.send') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Lista de familiares -->
                        <div>
                            <label class="form-label mb-3">{{ __('Selecciona los familiares') }}</label>

                            <div class="space-y-3">
                                @foreach($familyToRequest as $member)
                                    @php
                                        $memberPerson = $member['person'];
                                        $isPending = in_array($memberPerson->id, $pendingRequests);
                                    @endphp
                                    <label class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer transition-colors
                                        {{ $isPending ? 'bg-theme-secondary border-theme cursor-not-allowed' : 'hover:bg-theme-hover border-theme' }}">
                                        <input type="checkbox"
                                               name="person_ids[]"
                                               value="{{ $memberPerson->id }}"
                                               {{ $isPending ? 'disabled' : '' }}
                                               class="h-5 w-5 text-[#3b82f6] border-theme rounded focus:ring-[#3b82f6]">

                                        <div class="flex items-center gap-3 flex-1">
                                            @if($memberPerson->photo_path)
                                                <img src="{{ asset('storage/' . $memberPerson->photo_path) }}" alt="{{ $memberPerson->full_name }}"
                                                     class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-theme-secondary flex items-center justify-center text-theme-secondary font-bold">
                                                    {{ substr($memberPerson->first_name, 0, 1) }}{{ substr($memberPerson->patronymic, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $memberPerson->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ $member['label'] }}</p>
                                            </div>
                                        </div>

                                        @if($isPending)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('Pendiente') }}
                                            </span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            @error('person_ids')
                                <p class="form-error mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mensaje opcional -->
                        <div>
                            <label for="message" class="form-label">{{ __('Mensaje (opcional)') }}</label>
                            <textarea name="message" id="message" rows="3" class="form-input"
                                      placeholder="{{ __('Explica por que necesitas editar estos perfiles...') }}">{{ old('message') }}</textarea>
                            <p class="form-help">{{ __('Este mensaje se enviara a los creadores de los perfiles.') }}</p>
                            @error('message')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center gap-4 pt-4 border-t border-theme">
                            <button type="submit" class="btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                {{ __('Enviar solicitudes') }}
                            </button>
                            <a href="{{ route('tree.index') }}" class="btn-outline">
                                {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Permisos actuales -->
        @if(count($editableIds) > 0)
            <div class="card mt-6">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-theme">{{ __('Personas que puedes editar') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-theme-secondary mb-4">{{ __('Ya tienes permiso para editar estas personas:') }}</p>
                    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach(\App\Models\Person::whereIn('id', $editableIds)->get() as $editablePerson)
                            <a href="{{ route('persons.show', $editablePerson) }}"
                               class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                @if($editablePerson->photo_path)
                                    <img src="{{ asset('storage/' . $editablePerson->photo_path) }}" alt="{{ $editablePerson->full_name }}"
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-200 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs font-bold">
                                        {{ substr($editablePerson->first_name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-theme truncate">{{ $editablePerson->full_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
