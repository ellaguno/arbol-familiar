<x-app-layout>
    @php
        $isProtectedMinor = $person->shouldProtectMinorData();
        $displayName = $isProtectedMinor ? $person->first_name : $person->full_name;
    @endphp
    <x-slot name="title">{{ $displayName }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Aviso de menor protegido -->
        @if($isProtectedMinor)
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-amber-800 dark:text-amber-300">{{ __('Informacion protegida') }}</p>
                        <p class="text-sm text-amber-700 dark:text-amber-400">{{ __('Esta persona es menor de edad. Su informacion personal esta protegida y solo es visible para quien la registro.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('persons.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Personas') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1">{{ $displayName }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Columna izquierda: Perfil -->
            <div class="lg:col-span-1">
                <div class="card">
                    <div class="card-body text-center">
                        <!-- Foto (oculta para menores protegidos) -->
                        @if($isProtectedMinor)
                            <div class="w-32 h-32 rounded-full bg-theme-secondary flex items-center justify-center mx-auto mb-4">
                                <svg class="w-12 h-12 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                        @elseif($person->photo_path)
                            <img src="{{ Storage::url($person->photo_path) }}"
                                 alt="{{ $displayName }}"
                                 class="w-32 h-32 rounded-full object-cover mx-auto mb-4">
                        @else
                            <div class="w-32 h-32 rounded-full bg-theme-secondary flex items-center justify-center mx-auto mb-4">
                                <svg class="w-16 h-16 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        @endif

                        <h1 class="text-2xl font-bold text-theme">{{ $displayName }}</h1>
                        @if($person->nickname)
                            <p class="text-theme-muted">"{{ $person->nickname }}"</p>
                        @endif

                        <div class="flex flex-wrap justify-center gap-2 mt-4">
                            @if($person->gender)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $person->gender === 'M' ? 'bg-blue-200 text-blue-900 border-blue-400 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700' : ($person->gender === 'F' ? 'bg-pink-200 text-pink-900 border-pink-400 dark:bg-pink-900/40 dark:text-pink-200 dark:border-pink-700' : 'bg-theme-secondary text-theme border-gray-300 dark:border-gray-600') }}">
                                    {{ $person->gender === 'M' ? __('Masculino') : ($person->gender === 'F' ? __('Femenino') : __('Otro')) }}
                                </span>
                            @endif
                            @if($person->marital_status)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border bg-purple-200 text-purple-900 border-purple-400 dark:bg-purple-900/40 dark:text-purple-200 dark:border-purple-700">
                                    {{ config('mi-familia.marital_statuses')[$person->marital_status] ?? $person->marital_status }}
                                </span>
                            @endif
                            @if(($heritageEnabled ?? false) && $person->has_ethnic_heritage)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border bg-red-200 text-red-900 border-red-400 dark:bg-red-900/40 dark:text-red-200 dark:border-red-700">
                                    HR
                                </span>
                            @endif
                        </div>

                        @if(!$person->is_living)
                            <p class="text-theme-muted mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Fallecido/a') }}
                            </p>
                        @endif

                        <!-- Acciones -->
                        <div class="mt-6 flex flex-col gap-2">
                            {{-- Botones "Este soy yo" / "Fusionar con mi perfil" --}}
                            @php
                                $user = auth()->user();
                                $isOwnPerson = $user->person_id === $person->id;
                                $canClaim = !$user->person_id && !$person->user_id;

                                // Verificar si la persona ya está en el árbol del usuario
                                $isAlreadyInTree = false;
                                if ($user->person_id && $user->person_id !== $person->id) {
                                    $userPerson = $user->person;
                                    if ($userPerson) {
                                        // Ya está en el árbol si: fue creada por el mismo usuario,
                                        // o es familiar directo del usuario
                                        $isAlreadyInTree = $person->created_by === $user->id
                                            || in_array($person->id, $userPerson->directFamilyIds);
                                    }
                                }
                                $canAddToTree = $user->person_id && $user->person_id !== $person->id && !$isAlreadyInTree;

                                $hasPendingClaim = \App\Models\Message::where('sender_id', $user->id)
                                    ->where('related_person_id', $person->id)
                                    ->where('type', 'person_claim')
                                    ->where('action_status', 'pending')
                                    ->exists();
                            @endphp

                            @if($isOwnPerson)
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 border-2 border-emerald-400 dark:border-emerald-700 rounded-lg text-center mb-2">
                                    <span class="text-emerald-800 dark:text-emerald-300 font-medium">
                                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Este es tu perfil') }}
                                    </span>
                                </div>
                            @elseif($hasPendingClaim)
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-center mb-2">
                                    <span class="text-yellow-700 dark:text-yellow-400 text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Solicitud pendiente de aprobacion') }}
                                    </span>
                                </div>
                            @elseif($canClaim)
                                {{-- Usuario sin perfil puede reclamar --}}
                                <a href="{{ route('persons.claim', $person) }}" style="line-height : 21.06px; color :#EF4034; color : rgb(239, 64, 52);" class="btn-primary w-full ">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ __('Este soy yo') }}
                                </a>
                            @elseif($isAlreadyInTree)
                                {{-- La persona ya está en el árbol del usuario --}}
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-center mb-2">
                                    <span class="text-green-700 dark:text-green-400 text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('En tu árbol') }}
                                    </span>
                                </div>
                            @elseif($canAddToTree)
                                {{-- Usuario CON perfil puede agregar a su árbol --}}
                                <a href="{{ route('persons.add-to-tree', $person) }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background-color: #DC2626; hover:background-color: #B91C1C;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Agregar a mi árbol') }}
                                </a>
                            @elseif($person->user_id && $person->user_id !== $user->id)
                                <div class="p-3 bg-theme-secondary border border-theme rounded-lg text-center mb-2">
                                    <span class="text-theme-secondary text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ __('Perfil vinculado a otro usuario') }}
                                    </span>
                                </div>
                            @endif

                            <a href="{{ route('persons.edit', $person) }}" class="btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __('Editar') }}
                            </a>
                            <a href="{{ route('persons.relationships', $person) }}" class="btn-outline w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                {{ __('Relaciones') }}
                            </a>
                            <a href="{{ route('tree.view', $person) }}" class="btn-outline w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ __('Ver en arbol') }}
                            </a>
                            @php $sidebarHooks = trim($hooks->render('person.show.sidebar', ['person' => $person])); @endphp
                            @if($sidebarHooks)
                            <div class="w-full" x-data="{ open: false }">
                                <button @click="open = !open" class="btn-outline w-full justify-between">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ __('Reportes') }}
                                    </span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse>
                                    <div class="mt-1 border border-theme rounded-lg bg-theme-secondary py-1">
                                        {!! $sidebarHooks !!}
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($isOwnPerson)
                                <a href="{{ route('profile.settings') }}" class="btn-ghost w-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Configuracion') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Informacion -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos personales -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Datos personales') }}</h2>
                    </div>
                    <div class="card-body">
                        @if($isProtectedMinor)
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-theme-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <p class="text-theme-muted">{{ __('Informacion protegida por ser menor de edad.') }}</p>
                            </div>
                        @else
                            <dl class="grid md:grid-cols-2 gap-4">
                                @if($person->birth_year || $person->birth_date)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Nacimiento') }}</dt>
                                        <dd class="text-theme">
                                            {{ $person->birth_date_formatted ?? ($person->birth_date ? $person->birth_date->format('d/m/Y') : '') }}
                                            @if($person->birth_date_approx)
                                                <span class="text-theme-muted">({{ __('aprox.') }})</span>
                                            @endif
                                            @if($person->age && $person->is_living)
                                                <span class="text-theme-muted">({{ $person->age }} {{ __('años') }})</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                @if($person->birth_place || $person->birth_country)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Lugar de nacimiento') }}</dt>
                                        <dd class="text-theme">
                                            {{ collect([$person->birth_place, $person->birth_country])->filter()->join(', ') }}
                                        </dd>
                                    </div>
                                @endif

                                @if(!$person->is_living && ($person->death_year || $person->death_date))
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Defuncion') }}</dt>
                                        <dd class="text-theme">
                                            {{ $person->death_date_formatted ?? ($person->death_date ? $person->death_date->format('d/m/Y') : '') }}
                                            @if($person->death_date_approx)
                                                <span class="text-theme-muted">({{ __('aprox.') }})</span>
                                            @endif
                                            @if($person->age)
                                                <span class="text-theme-muted">({{ $person->age }} {{ __('años') }})</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                @if(!$person->is_living && ($person->death_place || $person->death_country))
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Lugar de defuncion') }}</dt>
                                        <dd class="text-theme">
                                            {{ collect([$person->death_place, $person->death_country])->filter()->join(', ') }}
                                        </dd>
                                    </div>
                                @endif

                                @if($person->residence_place || $person->residence_country)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Residencia') }}</dt>
                                        <dd class="text-theme">
                                            {{ collect([$person->residence_place, $person->residence_country])->filter()->join(', ') }}
                                        </dd>
                                    </div>
                                @endif

                                @if($person->occupation)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Ocupacion') }}</dt>
                                        <dd class="text-theme">{{ $person->occupation }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif
                    </div>
                </div>

                <!-- Herencia cultural -->
                @if(($heritageEnabled ?? false) && $person->has_ethnic_heritage)
                    <div class="card">
                        <div class="card-header flex items-center gap-2">
                            <h2 class="text-lg font-semibold">{{ $heritageLabel ?? __('Herencia cultural') }}</h2>
                        </div>
                        <div class="card-body">
                            <dl class="grid md:grid-cols-2 gap-4">
                                @if($person->heritage_region)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Region de origen') }}</dt>
                                        <dd class="text-theme">{{ ($heritageRegions ?? [])[$person->heritage_region] ?? $person->heritage_region }}</dd>
                                    </div>
                                @endif

                                @if($person->origin_town)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Pueblo/Ciudad') }}</dt>
                                        <dd class="text-theme">{{ $person->origin_town }}</dd>
                                    </div>
                                @endif

                                @if($person->migration_decade)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Decada de migracion') }}</dt>
                                        <dd class="text-theme">{{ ($heritageDecades ?? [])[$person->migration_decade] ?? $person->migration_decade }}</dd>
                                    </div>
                                @endif

                                @if($person->migration_destination)
                                    <div>
                                        <dt class="text-sm text-theme-muted">{{ __('Destino') }}</dt>
                                        <dd class="text-theme">{{ $person->migration_destination }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                <!-- Familia -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Familia') }}</h2>
                    </div>
                    <div class="card-body space-y-6">
                        <!-- Padres -->
                        @if($person->father || $person->mother)
                            <div>
                                <h3 class="font-medium text-theme-secondary mb-2">{{ __('Padres') }}</h3>
                                <div class="flex flex-wrap gap-4">
                                    @if($person->father)
                                        <a href="{{ route('persons.show', $person->father) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-theme-hover">
                                            @if($person->father->photo_path)
                                                <img src="{{ Storage::url($person->father->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ substr($person->father->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $person->father->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ __('Padre') }}</p>
                                            </div>
                                        </a>
                                    @endif
                                    @if($person->mother)
                                        <a href="{{ route('persons.show', $person->mother) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-theme-hover">
                                            @if($person->mother->photo_path)
                                                <img src="{{ Storage::url($person->mother->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                                                    <span class="text-pink-600 dark:text-pink-400 font-medium">{{ substr($person->mother->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $person->mother->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ __('Madre') }}</p>
                                            </div>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Cónyuges (todos, actuales y anteriores) -->
                        @if($person->allSpouses->isNotEmpty())
                            <div>
                                <h3 class="font-medium text-theme-secondary mb-2">
                                    {{ $person->allSpouses->count() > 1 ? __('Cónyuges') : __('Cónyuge') }}
                                </h3>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($person->allSpouses as $spouseInfo)
                                        @php $spouse = $spouseInfo['person']; @endphp
                                        <a href="{{ route('persons.show', $spouse) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-theme-hover">
                                            @if($spouse->photo_path)
                                                <img src="{{ Storage::url($spouse->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-{{ $spouse->gender === 'M' ? 'blue' : 'pink' }}-100 {{ $spouse->gender === 'M' ? 'dark:bg-blue-900/30' : 'dark:bg-pink-900/30' }} flex items-center justify-center">
                                                    <span class="text-{{ $spouse->gender === 'M' ? 'blue' : 'pink' }}-600 {{ $spouse->gender === 'M' ? 'dark:text-blue-400' : 'dark:text-pink-400' }} font-medium">{{ substr($spouse->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $spouse->full_name }}</p>
                                                <p class="text-sm text-theme-muted">
                                                    {{ $spouse->gender === 'M' ? __('Esposo') : __('Esposa') }}
                                                    @if($spouseInfo['status'] && $spouseInfo['status'] !== 'married')
                                                        <span class="text-xs text-theme-muted">
                                                            ({{ __($spouseInfo['status']) }})
                                                        </span>
                                                    @endif
                                                    @if($spouseInfo['marriage_date'])
                                                        <span class="text-xs text-theme-muted">
                                                            - {{ $spouseInfo['marriage_date']->format('Y') }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            @if($spouseInfo['is_current'])
                                                <span class="ml-1 w-2 h-2 bg-green-500 rounded-full" title="{{ __('Actual') }}"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Hermanos -->
                        @if($person->siblings->isNotEmpty())
                            <div>
                                <h3 class="font-medium text-theme-secondary mb-2">{{ __('Hermanos') }}</h3>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($person->siblings as $sibling)
                                        <a href="{{ route('persons.show', $sibling) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-theme-hover">
                                            @if($sibling->photo_path)
                                                <img src="{{ Storage::url($sibling->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                                    <span class="text-theme-secondary font-medium">{{ substr($sibling->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $sibling->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ $sibling->gender === 'M' ? __('Hermano') : __('Hermana') }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Hijos -->
                        @if($person->children->isNotEmpty())
                            <div>
                                <h3 class="font-medium text-theme-secondary mb-2">{{ __('Hijos') }}</h3>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($person->children as $child)
                                        <a href="{{ route('persons.show', $child) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-theme-hover">
                                            @if($child->photo_path)
                                                <img src="{{ Storage::url($child->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-theme-secondary flex items-center justify-center">
                                                    <span class="text-theme-secondary font-medium">{{ substr($child->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-theme">{{ $child->full_name }}</p>
                                                <p class="text-sm text-theme-muted">{{ $child->gender === 'M' ? __('Hijo') : __('Hija') }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!$person->father && !$person->mother && !$person->current_spouse && $person->siblings->isEmpty() && $person->children->isEmpty())
                            <p class="text-theme-muted text-center py-4">
                                {{ __('No hay relaciones familiares registradas.') }}
                                <a href="{{ route('persons.relationships', $person) }}" class="text-mf-primary hover:underline">
                                    {{ __('Agregar relaciones') }}
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Eventos -->
                @if($person->events && $person->events->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Eventos') }}</h2>
                        </div>
                        <div class="card-body">
                            <ul class="space-y-3">
                                @foreach($person->events as $event)
                                    <li class="flex items-start gap-3">
                                        <div class="w-2 h-2 rounded-full bg-mf-primary mt-2"></div>
                                        <div>
                                            <p class="font-medium">{{ $event->type }}</p>
                                            @if($event->date)
                                                <p class="text-sm text-theme-muted">{{ $event->date->format('d/m/Y') }}</p>
                                            @endif
                                            @if($event->place)
                                                <p class="text-sm text-theme-muted">{{ $event->place }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Galeria y Documentos -->
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="text-lg font-semibold">
                            <a href="{{ route('media.index', ['person_id' => $person->id]) }}" class="hover:text-mf-primary">
                                {{ __('Galeria y Documentos') }}
                            </a>
                        </h2>
                        <a href="{{ route('media.create', ['person_id' => $person->id]) }}" class="text-sm text-mf-primary hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Agregar') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if($person->media && $person->media->isNotEmpty())
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($person->media->take(8) as $media)
                                    <a href="{{ route('media.show', $media) }}" class="group relative aspect-square rounded-lg overflow-hidden bg-theme-secondary">
                                        @if(in_array($media->type, ['image', 'photo']))
                                            <img src="{{ Storage::url($media->file_path) }}"
                                                 alt="{{ $media->title }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center p-2">
                                                <svg class="w-12 h-12 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-xs text-theme-muted mt-1 text-center truncate w-full">{{ $media->title ?? $media->original_filename }}</span>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                                            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @if($person->media->count() > 8)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('media.person', $person) }}" class="text-mf-primary hover:underline">
                                        {{ __('Ver todos') }} ({{ $person->media->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-theme-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-theme-muted mb-3">{{ __('No hay fotos ni documentos.') }}</p>
                                <a href="{{ route('media.create', ['person_id' => $person->id]) }}" class="btn-outline inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Subir foto o documento') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                {!! $hooks->render('person.show.content', ['person' => $person]) !!}
            </div>
        </div>
    </div>
</x-app-layout>
