<x-app-layout>
    <x-slot name="title">{{ $person->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner de informacion limitada -->
        <div class="mb-6 flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div>
                <h3 class="font-medium text-amber-800 dark:text-amber-300">{{ __('Informacion limitada') }}</h3>
                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                    {{ __('Vincula tu cuenta o declara una relacion para ver el perfil completo.') }}
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <!-- Foto / Avatar -->
                    <div class="flex-shrink-0">
                        @if($person->photo_path)
                            <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->full_name }}"
                                 class="w-32 h-32 rounded-full object-cover">
                        @else
                            <div class="w-32 h-32 rounded-full bg-mf-primary flex items-center justify-center text-white text-4xl font-bold">
                                {{ substr($person->first_name, 0, 1) }}{{ substr($person->patronymic, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <!-- Datos basicos -->
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-theme">{{ $person->full_name }}</h1>

                        <div class="mt-3 space-y-2 text-theme-secondary">
                            @if($person->gender)
                                <p>
                                    <svg class="w-4 h-4 inline mr-2 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $person->gender === 'M' ? __('Masculino') : ($person->gender === 'F' ? __('Femenino') : __('No especificado')) }}
                                </p>
                            @endif

                            @if($person->birth_date)
                                <p>
                                    <svg class="w-4 h-4 inline mr-2 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Nacimiento') }}: {{ $person->birth_date->format('Y') }}
                                </p>
                            @endif

                            @if($person->birth_place)
                                <p>
                                    <svg class="w-4 h-4 inline mr-2 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $person->birth_place }}
                                </p>
                            @endif

                            @if(!$person->is_living)
                                <p class="text-theme-muted">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Fallecido/a') }}
                                </p>
                            @endif
                        </div>

                        <!-- Acciones -->
                        <div class="mt-6 flex flex-col gap-2">
                            @php
                                $user = auth()->user();
                                $isOwnPerson = $user->person_id === $person->id;

                                $currentPersonIsDummy = false;
                                if ($user->person_id) {
                                    $cp = $user->person;
                                    if ($cp) {
                                        $hasFamily = $cp->familiesAsChild()->exists()
                                            || $cp->familiesAsSpouse()->exists();
                                        $currentPersonIsDummy = !$hasFamily;
                                    }
                                }

                                $canClaim = ($currentPersonIsDummy || !$user->person_id) && !$person->user_id;
                                $canDeclareRelationship = $user->person_id && $user->person_id !== $person->id;

                                $hasPendingClaim = \App\Models\Message::where('sender_id', $user->id)
                                    ->where('related_person_id', $person->id)
                                    ->whereIn('type', ['person_claim', 'relationship_claim'])
                                    ->where('action_status', 'pending')
                                    ->exists();
                            @endphp

                            @if($hasPendingClaim)
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-center">
                                    <span class="text-yellow-700 dark:text-yellow-400 text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Solicitud pendiente de aprobacion') }}
                                    </span>
                                </div>
                            @else
                                @if($canClaim)
                                    <a href="{{ route('persons.claim', $person) }}" class="btn-primary w-full">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ __('Este soy yo') }}
                                    </a>
                                @endif

                                @if($canDeclareRelationship && !$isOwnPerson)
                                    <a href="{{ route('persons.relationship-claim', $person) }}" class="btn-outline w-full">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        {{ __('Estoy relacionado directamente') }}
                                    </a>
                                @endif
                            @endif

                            <a href="{{ route('persons.index') }}" class="btn-outline w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                {{ __('Volver') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
