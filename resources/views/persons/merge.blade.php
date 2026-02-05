<x-app-layout>
    <x-slot name="title">{{ __('Fusionar perfiles') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h1 class="text-xl font-bold text-theme">{{ __('Fusionar este perfil con el tuyo') }}</h1>
            </div>
            <div class="card-body">
                @if($cannotMerge)
                    <!-- No se puede fusionar - Bloqueo -->
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-red-800 dark:text-red-300">{{ __('No es posible realizar esta fusión') }}</h3>
                                <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ $cannotMergeReason }}</p>
                                <p class="text-sm text-red-600 dark:text-red-400 mt-3">
                                    {{ __('Si crees que esto es un error, contacta al administrador o al creador del perfil.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Solo mostrar los perfiles para comparación -->
                    <div class="grid md:grid-cols-2 gap-6 mb-6 opacity-60">
                        <!-- Perfil a fusionar (source) -->
                        <div class="p-4 bg-theme-secondary border border-theme rounded-lg">
                            <h3 class="text-sm font-medium text-theme-secondary mb-3">{{ __('Perfil seleccionado') }}</h3>
                            <div class="flex items-center gap-3">
                                @if($person->photo_path)
                                    <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->full_name }}"
                                         class="w-16 h-16 rounded-full object-cover grayscale">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-theme-secondary flex items-center justify-center text-theme-muted text-xl font-bold">
                                        {{ substr($person->first_name, 0, 1) }}{{ substr($person->patronymic, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-theme-secondary">{{ $person->full_name }}</p>
                                    @if($person->birth_date)
                                        <p class="text-sm text-theme-muted">{{ $person->birth_date->format('d/m/Y') }}</p>
                                    @endif
                                    @if(!$person->is_living)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-theme-secondary text-theme-secondary mt-1">
                                            {{ __('Fallecido') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tu perfil -->
                        <div class="p-4 bg-theme-secondary border border-theme rounded-lg">
                            <h3 class="text-sm font-medium text-theme-secondary mb-3">{{ __('Tu perfil') }}</h3>
                            <div class="flex items-center gap-3">
                                @if($userPerson->photo_path)
                                    <img src="{{ asset('storage/' . $userPerson->photo_path) }}" alt="{{ $userPerson->full_name }}"
                                         class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-blue-200 flex items-center justify-center text-blue-600 text-xl font-bold">
                                        {{ substr($userPerson->first_name, 0, 1) }}{{ substr($userPerson->patronymic, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-theme">{{ $userPerson->full_name }}</p>
                                    @if($userPerson->birth_date)
                                        <p class="text-sm text-theme-secondary">{{ $userPerson->birth_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center pt-4">
                        <a href="{{ route('persons.show', $person) }}" class="btn-outline">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            {{ __('Volver al perfil') }}
                        </a>
                    </div>
                @else
                    <!-- Comparacion visual de ambos perfiles -->
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <!-- Perfil a fusionar (source) -->
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Se eliminará') }}
                            </h3>
                            <div class="flex items-center gap-3">
                                @if($person->photo_path)
                                    <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->full_name }}"
                                         class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center text-red-600 text-xl font-bold">
                                        {{ substr($person->first_name, 0, 1) }}{{ substr($person->patronymic, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-theme">{{ $person->full_name }}</p>
                                    @if($person->birth_date)
                                        <p class="text-sm text-theme-secondary">{{ $person->birth_date->format('d/m/Y') }}</p>
                                    @endif
                                    <p class="text-xs text-theme-muted">ID: {{ $person->id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tu perfil (target) -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Se conservará') }}
                            </h3>
                            <div class="flex items-center gap-3">
                                @if($userPerson->photo_path)
                                    <img src="{{ asset('storage/' . $userPerson->photo_path) }}" alt="{{ $userPerson->full_name }}"
                                         class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-blue-200 flex items-center justify-center text-blue-600 text-xl font-bold">
                                        {{ substr($userPerson->first_name, 0, 1) }}{{ substr($userPerson->patronymic, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-theme">{{ $userPerson->full_name }}</p>
                                    @if($userPerson->birth_date)
                                        <p class="text-sm text-theme-secondary">{{ $userPerson->birth_date->format('d/m/Y') }}</p>
                                    @endif
                                    <p class="text-xs text-theme-muted">ID: {{ $userPerson->id }} ({{ __('tu perfil') }})</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advertencias -->
                    @if(!empty($warnings))
                        <div class="space-y-4 mb-6">
                            @foreach($warnings as $warning)
                                @if($warning['type'] === 'name_mismatch')
                                    <!-- Advertencia de nombres no coinciden -->
                                    <div class="flex items-start gap-3 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-300 dark:border-orange-800 rounded-lg">
                                        <svg class="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <div>
                                            <h3 class="font-semibold text-orange-800 dark:text-orange-300">{{ __('Advertencia: Nombres diferentes') }}</h3>
                                            <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">{{ $warning['message'] }}</p>
                                        </div>
                                    </div>
                                @elseif($warning['type'] === 'data_loss')
                                    <!-- Advertencia de pérdida de datos -->
                                    <div class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-800 rounded-lg">
                                        <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <div>
                                            <h3 class="font-semibold text-yellow-800 dark:text-yellow-300">{{ __('Advertencia: Posible pérdida de datos') }}</h3>
                                            <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">{{ $warning['message'] }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Explicacion -->
                    <div class="mb-6">
                        <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="font-medium text-blue-800 dark:text-blue-300">{{ __('¿Qué sucede al fusionar?') }}</h3>
                                <ul class="text-sm text-blue-700 dark:text-blue-400 mt-2 space-y-1 list-disc list-inside">
                                    <li>{{ __('Las relaciones familiares se transferirán a tu perfil') }}</li>
                                    <li>{{ __('Las fotos y documentos se moverán a tu perfil') }}</li>
                                    <li>{{ __('Los datos que no tengas se completarán con los de este perfil') }}</li>
                                    <li>{{ __('El perfil duplicado será eliminado permanentemente') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Autorizacion -->
                    <div class="mb-6">
                        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <div>
                                <h3 class="font-medium text-amber-800 dark:text-amber-300">{{ __('Requiere autorización') }}</h3>
                                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                                    {{ __('Se enviará una solicitud a') }}
                                    <strong>{{ $creator?->email ?? __('el creador del perfil') }}</strong>
                                    {{ __('quien debe aprobar la fusión.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <form action="{{ route('persons.merge.send', $person) }}" method="POST" class="space-y-4"
                          x-data="{ confirmed: false, showConfirmModal: false }"
                          @submit.prevent="showConfirmModal = true">
                        @csrf

                        <div>
                            <label for="message" class="form-label">{{ __('Mensaje (opcional)') }}</label>
                            <textarea name="message" id="message" rows="3" class="form-input"
                                      placeholder="{{ __('Explica por qué crees que ambos perfiles son la misma persona...') }}">{{ old('message') }}</textarea>
                            <p class="form-help">{{ __('Puedes agregar detalles que ayuden a confirmar que son la misma persona.') }}</p>
                            @error('message')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!$namesSimilar)
                            <!-- Confirmación adicional si los nombres no coinciden -->
                            <div class="flex items-start gap-2 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                                <input type="checkbox" id="confirm_names" x-model="confirmed" required
                                       class="mt-1 h-4 w-4 text-orange-600 border-theme rounded focus:ring-orange-500">
                                <label for="confirm_names" class="text-sm text-orange-800 dark:text-orange-300">
                                    {{ __('Confirmo que entiendo que los nombres son diferentes y aún así deseo continuar con la solicitud de fusión.') }}
                                </label>
                            </div>
                        @endif

                        <div class="flex items-center gap-4 pt-4">
                            <button type="submit" class="btn-primary"
                                    @if(!$namesSimilar) :disabled="!confirmed" :class="{'opacity-50 cursor-not-allowed': !confirmed}" @endif>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                {{ __('Solicitar fusión') }}
                            </button>
                            <a href="{{ route('persons.show', $person) }}" class="btn-outline">
                                {{ __('Cancelar') }}
                            </a>
                        </div>

                        <!-- Modal de confirmación final -->
                        <div x-show="showConfirmModal" x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">
                            <div class="bg-theme-card rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                                 @click.away="showConfirmModal = false">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-theme">{{ __('¿Confirmar solicitud de fusión?') }}</h3>
                                        <p class="text-sm text-theme-secondary mt-2">
                                            {{ __('Estás a punto de solicitar la fusión del perfil de') }}
                                            <strong>{{ $person->full_name }}</strong>
                                            {{ __('con tu perfil. Esta acción no se puede deshacer una vez aprobada.') }}
                                        </p>
                                        <ul class="text-sm text-theme-muted mt-3 space-y-1 list-disc list-inside">
                                            <li>{{ __('El perfil seleccionado será eliminado') }}</li>
                                            <li>{{ __('Algunos datos podrían perderse') }}</li>
                                            <li>{{ __('Las relaciones serán transferidas') }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" @click="showConfirmModal = false" class="btn-outline">
                                        {{ __('Cancelar') }}
                                    </button>
                                    <button type="button"
                                            @click="$el.closest('form').submit()"
                                            class="btn-primary bg-yellow-600 hover:bg-yellow-700">
                                        {{ __('Sí, solicitar fusión') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
