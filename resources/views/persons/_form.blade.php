@php
    $person = $person ?? null;
    $prefill = $prefill ?? [];
@endphp

<div x-data="{
    isLiving: {{ old('is_living', $person?->is_living ?? true) ? 'true' : 'false' }},
    hasEthnicHeritage: {{ old('has_ethnic_heritage', $person?->has_ethnic_heritage ?? ($prefill['has_ethnic_heritage'] ?? false)) ? 'true' : 'false' }}
}">
    <!-- Datos basicos -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-lg font-semibold">{{ __('Datos basicos') }}</h2>
        </div>
        <div class="card-body">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="first_name" class="form-label">{{ __('Nombre(s)') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" id="first_name"
                           value="{{ old('first_name', $person?->first_name) }}"
                           class="form-input @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido paterno -->
                <div>
                    <label for="patronymic" class="form-label">{{ __('Apellido paterno') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="patronymic" id="patronymic"
                           value="{{ old('patronymic', $person?->patronymic ?? ($prefill['patronymic'] ?? '')) }}"
                           class="form-input @error('patronymic') border-red-500 @enderror" required>
                    @error('patronymic')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido materno -->
                <div>
                    <label for="matronymic" class="form-label">{{ __('Apellido materno') }}</label>
                    <input type="text" name="matronymic" id="matronymic"
                           value="{{ old('matronymic', $person?->matronymic ?? ($prefill['matronymic'] ?? '')) }}"
                           class="form-input">
                </div>

                <!-- Apodo -->
                <div>
                    <label for="nickname" class="form-label">{{ __('Apodo') }}</label>
                    <input type="text" name="nickname" id="nickname"
                           value="{{ old('nickname', $person?->nickname) }}"
                           class="form-input">
                </div>

                <!-- Genero -->
                <div>
                    <label for="gender" class="form-label">{{ __('Genero') }} <span class="text-red-500">*</span></label>
                    @php $selectedGender = old('gender', $person?->gender ?? ($prefill['gender'] ?? '')); @endphp
                    <select name="gender" id="gender" class="form-input @error('gender') border-red-500 @enderror" required>
                        <option value="">{{ __('Seleccionar') }}</option>
                        <option value="M" {{ $selectedGender === 'M' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                        <option value="F" {{ $selectedGender === 'F' ? 'selected' : '' }}>{{ __('Femenino') }}</option>
                        <option value="O" {{ $selectedGender === 'O' ? 'selected' : '' }}>{{ __('Otro') }}</option>
                    </select>
                    @error('gender')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado civil -->
                <div>
                    <label for="marital_status" class="form-label">{{ __('Estado civil') }}</label>
                    <select name="marital_status" id="marital_status" class="form-input">
                        <option value="">{{ __('Seleccionar') }}</option>
                        @foreach(config('mi-familia.marital_statuses') as $value => $label)
                            <option value="{{ $value }}" {{ old('marital_status', $person?->marital_status) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estado (vivo/fallecido) -->
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_living" value="0">
                        <input type="checkbox" name="is_living" value="1"
                               class="form-checkbox"
                               x-model="isLiving"
                               {{ old('is_living', $person?->is_living ?? true) ? 'checked' : '' }}>
                        <span>{{ __('Persona viva') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_minor" value="0">
                        <input type="checkbox" name="is_minor" value="1"
                               class="form-checkbox"
                               {{ old('is_minor', $person?->is_minor) ? 'checked' : '' }}>
                        <span>{{ __('Menor de edad') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Nacimiento -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-lg font-semibold">{{ __('Nacimiento') }}</h2>
        </div>
        <div class="card-body">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Fecha de nacimiento (campos separados) -->
                <div>
                    <label class="form-label">{{ __('Fecha de nacimiento') }}</label>
                    <div class="flex gap-2 items-center">
                        <!-- Día (opcional) -->
                        <select name="birth_day" id="birth_day" class="form-input w-20">
                            <option value="">{{ __('Día') }}</option>
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ old('birth_day', $person?->birth_day) == $d ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endfor
                        </select>

                        <!-- Mes (opcional) -->
                        <select name="birth_month" id="birth_month" class="form-input w-28">
                            <option value="">{{ __('Mes') }}</option>
                            @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $idx => $mes)
                                <option value="{{ $idx + 1 }}" {{ old('birth_month', $person?->birth_month) == ($idx + 1) ? 'selected' : '' }}>
                                    {{ $mes }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Año -->
                        <input type="number" name="birth_year" id="birth_year"
                               value="{{ old('birth_year', $person?->birth_year) }}"
                               placeholder="{{ __('Año') }}"
                               min="1000" max="{{ date('Y') }}"
                               maxlength="4"
                               oninput="this.value = this.value.slice(0, 4)"
                               class="form-input w-24">

                        <label class="flex items-center gap-2 cursor-pointer text-sm ml-2">
                            <input type="hidden" name="birth_date_approx" value="0">
                            <input type="checkbox" name="birth_date_approx" value="1"
                                   class="form-checkbox"
                                   {{ old('birth_date_approx', $person?->birth_date_approx) ? 'checked' : '' }}>
                            <span>{{ __('Aprox.') }}</span>
                        </label>
                    </div>
                    <!-- <p class="form-help mt-1">{{ __('Solo el año es obligatorio si conoces la fecha') }}</p> -->
                </div>

                <!-- Lugar de nacimiento -->
                <div>
                    <label for="birth_place" class="form-label">{{ __('Lugar de nacimiento') }}</label>
                    <input type="text" name="birth_place" id="birth_place"
                           value="{{ old('birth_place', $person?->birth_place) }}"
                           placeholder="{{ __('Ciudad, Estado') }}"
                           class="form-input">
                </div>

                <!-- Pais de nacimiento -->
                <div>
                    <label for="birth_country" class="form-label">{{ __('Pais de nacimiento') }}</label>
                    <input type="text" name="birth_country" id="birth_country"
                           value="{{ old('birth_country', $person?->birth_country) }}"
                           placeholder="{{ __('Mexico, Croacia, etc.') }}"
                           class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Defuncion (solo si no esta vivo) -->
    <div class="card" x-show="!isLiving" x-cloak>
        <div class="card-header">
            <h2 class="text-lg font-semibold">{{ __('Defuncion') }}</h2>
        </div>
        <div class="card-body">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Fecha de defuncion (campos separados) -->
                <div>
                    <label class="form-label">{{ __('Fecha de defuncion') }}</label>
                    <div class="flex gap-2 items-center">
                        <!-- Día (opcional) -->
                        <select name="death_day" id="death_day" class="form-input w-20">
                            <option value="">{{ __('Día') }}</option>
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ old('death_day', $person?->death_day) == $d ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endfor
                        </select>

                        <!-- Mes (opcional) -->
                        <select name="death_month" id="death_month" class="form-input w-28">
                            <option value="">{{ __('Mes') }}</option>
                            @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $idx => $mes)
                                <option value="{{ $idx + 1 }}" {{ old('death_month', $person?->death_month) == ($idx + 1) ? 'selected' : '' }}>
                                    {{ $mes }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Año -->
                        <input type="number" name="death_year" id="death_year"
                               value="{{ old('death_year', $person?->death_year) }}"
                               placeholder="{{ __('Año') }}"
                               min="1000" max="{{ date('Y') }}"
                               maxlength="4"
                               oninput="this.value = this.value.slice(0, 4)"
                               class="form-input w-24">

                        <label class="flex items-center gap-2 cursor-pointer text-sm ml-2">
                            <input type="hidden" name="death_date_approx" value="0">
                            <input type="checkbox" name="death_date_approx" value="1"
                                   class="form-checkbox"
                                   {{ old('death_date_approx', $person?->death_date_approx) ? 'checked' : '' }}>
                            <span>{{ __('Aprox.') }}</span>
                        </label>
                    </div>
                    <!-- <p class="form-help mt-1">{{ __('Solo el año es obligatorio si conoces la fecha') }}</p> -->
                </div>

                <!-- Lugar de defuncion -->
                <div>
                    <label for="death_place" class="form-label">{{ __('Lugar de defuncion') }}</label>
                    <input type="text" name="death_place" id="death_place"
                           value="{{ old('death_place', $person?->death_place) }}"
                           class="form-input">
                </div>

                <!-- Pais de defuncion -->
                <div>
                    <label for="death_country" class="form-label">{{ __('Pais de defuncion') }}</label>
                    <input type="text" name="death_country" id="death_country"
                           value="{{ old('death_country', $person?->death_country) }}"
                           class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Residencia y ocupacion -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-lg font-semibold">{{ __('Residencia y contacto') }}</h2>
        </div>
        <div class="card-body">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Lugar de residencia -->
                <div>
                    <label for="residence_place" class="form-label">{{ __('Lugar de residencia') }}</label>
                    <input type="text" name="residence_place" id="residence_place"
                           value="{{ old('residence_place', $person?->residence_place) }}"
                           class="form-input">
                </div>

                <!-- Pais de residencia -->
                <div>
                    <label for="residence_country" class="form-label">{{ __('Pais de residencia') }}</label>
                    <input type="text" name="residence_country" id="residence_country"
                           value="{{ old('residence_country', $person?->residence_country) }}"
                           class="form-input">
                </div>

                <!-- Ocupacion -->
                <div>
                    <label for="occupation" class="form-label">{{ __('Ocupacion') }}</label>
                    <input type="text" name="occupation" id="occupation"
                           value="{{ old('occupation', $person?->occupation) }}"
                           class="form-input">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="form-label">{{ __('Correo electronico') }}</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $person?->email) }}"
                           class="form-input">
                    <p class="form-help">{{ __('Se enviara una invitacion automaticamente para solicitar consentimiento de datos') }}</p>

                    @if($person && $person->email && !$person->user_id)
                        @php
                            $pendingInvitation = \App\Models\Invitation::where('person_id', $person->id)
                                ->where('email', $person->email)
                                ->pending()
                                ->first();
                        @endphp
                        @if($pendingInvitation)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-amber-600">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Invitacion pendiente') }} - {{ __('Enviada') }} {{ $pendingInvitation->sent_at?->diffForHumans() ?? __('hace poco') }}
                                </span>
                                <form action="{{ route('persons.send-invitation', $person) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">
                                        {{ __('Reenviar invitacion') }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-2">
                                <form action="{{ route('persons.send-invitation', $person) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">
                                        {{ __('Enviar invitacion') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Telefono -->
                <div>
                    <label for="phone" class="form-label">{{ __('Telefono') }}</label>
                    <input type="tel" name="phone" id="phone"
                           value="{{ old('phone', $person?->phone) }}"
                           class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Herencia cultural -->
    <div class="card">
        <div class="card-header flex items-center gap-2">

            <h2 class="text-lg font-semibold">{{ __('Herencia cultural') }}</h2>
        </div>
        <div class="card-body">
            <label class="flex items-center gap-3 cursor-pointer mb-6">
                <input type="hidden" name="has_ethnic_heritage" value="0">
                <input type="checkbox" name="has_ethnic_heritage" value="1"
                       class="form-checkbox"
                       x-model="hasEthnicHeritage"
                       {{ old('has_ethnic_heritage', $person?->has_ethnic_heritage ?? ($prefill['has_ethnic_heritage'] ?? false)) ? 'checked' : '' }}>
                <span class="font-medium">{{ __('Esta persona tiene herencia cultural') }}</span>
            </label>

            <div x-show="hasEthnicHeritage" x-cloak class="grid md:grid-cols-2 gap-6">
                <!-- Region de origen -->
                <div>
                    <label for="heritage_region" class="form-label">{{ __('Region de origen') }}</label>
                    @php $selectedRegion = old('heritage_region', $person?->heritage_region ?? ($prefill['heritage_region'] ?? '')); @endphp
                    <select name="heritage_region" id="heritage_region" class="form-input">
                        <option value="">{{ __('Seleccionar') }}</option>
                        @foreach($heritageRegions as $code => $name)
                            <option value="{{ $code }}" {{ $selectedRegion === $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pueblo de origen -->
                <div>
                    <label for="origin_town" class="form-label">{{ __('Pueblo/Ciudad de origen') }}</label>
                    <input type="text" name="origin_town" id="origin_town"
                           value="{{ old('origin_town', $person?->origin_town ?? ($prefill['origin_town'] ?? '')) }}"
                           placeholder="{{ __('Nombre del pueblo o ciudad') }}"
                           class="form-input">
                </div>

                <!-- Decada de migracion -->
                <div>
                    <label for="migration_decade" class="form-label">{{ __('Decada de migracion') }}</label>
                    <select name="migration_decade" id="migration_decade" class="form-input">
                        <option value="">{{ __('Seleccionar') }}</option>
                        @foreach(config('mi-familia.migration_decades') as $value => $label)
                            <option value="{{ $value }}" {{ old('migration_decade', $person?->migration_decade) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-help">{{ __('Cuando migro la familia') }}</p>
                </div>

                <!-- Destino de migracion -->
                <div>
                    <label for="migration_destination" class="form-label">{{ __('Destino de migracion') }}</label>
                    <input type="text" name="migration_destination" id="migration_destination"
                           value="{{ old('migration_destination', $person?->migration_destination) }}"
                           placeholder="{{ __('Pais o region de destino') }}"
                           class="form-input">
                </div>
            </div>
        </div>
    </div>

    <!-- Privacidad -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-lg font-semibold">{{ __('Privacidad') }}</h2>
        </div>
        <div class="card-body">
            <label for="privacy_level" class="form-label">{{ __('Quien puede ver esta persona') }} <span class="text-red-500">*</span></label>
            <select name="privacy_level" id="privacy_level" class="form-input max-w-md" required>
                <option value="private" {{ old('privacy_level', $person?->privacy_level ?? 'family') === 'private' ? 'selected' : '' }}>
                    {{ __('Solo yo (privado)') }}
                </option>
                <option value="family" {{ old('privacy_level', $person?->privacy_level ?? 'family') === 'family' ? 'selected' : '' }}>
                    {{ __('Mi familia') }}
                </option>
                <option value="community" {{ old('privacy_level', $person?->privacy_level) === 'community' ? 'selected' : '' }}>
                    {{ __('Toda la comunidad') }}
                </option>
                <option value="public" {{ old('privacy_level', $person?->privacy_level) === 'public' ? 'selected' : '' }}>
                    {{ __('Publico') }}
                </option>
            </select>
            <p class="form-help mt-2">
                {{ __('Controla quien puede ver los datos de esta persona en la plataforma.') }}
            </p>
        </div>
    </div>
</div>
