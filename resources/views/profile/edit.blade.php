<x-app-layout>
    <x-slot name="title">{{ __('Editar Perfil') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Mi Perfil') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('Actualiza tu informacion personal') }}</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Columna izquierda: Foto -->
            <div class="lg:col-span-1">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            @if($person && $person->photo_path)
                                <img src="{{ Storage::url($person->photo_path) }}" alt="{{ $person->full_name }}"
                                     class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-mf-light">
                            @else
                                <div class="w-32 h-32 rounded-full bg-mf-primary text-white flex items-center justify-center mx-auto text-4xl font-bold">
                                    {{ $person ? strtoupper(substr($person->first_name, 0, 1)) : strtoupper(substr($user->email, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <h3 class="font-semibold text-gray-900">
                            {{ $person ? $person->full_name : $user->email }}
                        </h3>


                        <!-- Subir foto -->
                        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mt-6">
                            @csrf
                            <label class="btn-outline btn-sm cursor-pointer inline-block">
                                <input type="file" name="photo" accept="image/*" class="hidden" onchange="this.form.submit()">
                                {{ __('Cambiar foto') }}
                            </label>
                        </form>

                        @if($person && $person->photo_path)
                            <form action="{{ route('profile.photo.delete') }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">
                                    {{ __('Eliminar foto') }}
                                </button>
                            </form>
                        @endif

                        @error('photo')
                            <p class="form-error mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Menu lateral -->
                <div class="card mt-6">
                    <div class="card-body p-0">
                        <nav class="space-y-1">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-mf-primary bg-mf-light border-l-4 border-mf-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Datos personales') }}
                            </a>
                            <a href="{{ route('profile.settings') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('Configuracion') }}
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Formulario -->
            <div class="lg:col-span-2">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Datos Personales -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Datos Personales') }}</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="form-label">{{ __('Nombre(s)') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" id="first_name"
                                           value="{{ old('first_name', $person?->first_name) }}"
                                           class="form-input @error('first_name') border-red-500 @enderror" required>
                                    @error('first_name')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="nickname" class="form-label">{{ __('Apodo') }}</label>
                                    <input type="text" name="nickname" id="nickname"
                                           value="{{ old('nickname', $person?->nickname) }}"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="patronymic" class="form-label">{{ __('Apellido paterno') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="patronymic" id="patronymic"
                                           value="{{ old('patronymic', $person?->patronymic) }}"
                                           class="form-input @error('patronymic') border-red-500 @enderror" required>
                                    @error('patronymic')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="matronymic" class="form-label">{{ __('Apellido materno') }}</label>
                                    <input type="text" name="matronymic" id="matronymic"
                                           value="{{ old('matronymic', $person?->matronymic) }}"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="gender" class="form-label">{{ __('Genero') }} <span class="text-red-500">*</span></label>
                                    <select name="gender" id="gender" class="form-input @error('gender') border-red-500 @enderror" required>
                                        <option value="">{{ __('Seleccionar...') }}</option>
                                        <option value="M" {{ old('gender', $person?->gender) == 'M' ? 'selected' : '' }}>{{ __('Masculino') }}</option>
                                        <option value="F" {{ old('gender', $person?->gender) == 'F' ? 'selected' : '' }}>{{ __('Femenino') }}</option>
                                        <option value="O" {{ old('gender', $person?->gender) == 'O' ? 'selected' : '' }}>{{ __('Otro') }}</option>
                                    </select>
                                    @error('gender')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="marital_status" class="form-label">{{ __('Estado civil') }}</label>
                                    <select name="marital_status" id="marital_status" class="form-input">
                                        <option value="">{{ __('Seleccionar...') }}</option>
                                        @foreach(config('mi-familia.marital_statuses') as $value => $label)
                                            <option value="{{ $value }}" {{ old('marital_status', $person?->marital_status) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="birth_date" class="form-label">{{ __('Fecha de nacimiento') }}</label>
                                    <input type="date" name="birth_date" id="birth_date"
                                           value="{{ old('birth_date', $person?->birth_date?->format('Y-m-d')) }}"
                                           min="1000-01-01" max="9999-12-31"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="birth_place" class="form-label">{{ __('Lugar de nacimiento') }}</label>
                                    <input type="text" name="birth_place" id="birth_place"
                                           value="{{ old('birth_place', $person?->birth_place) }}"
                                           placeholder="Ciudad, Estado"
                                           class="form-input">
                                </div>

                                <div>
                                    <label for="birth_country" class="form-label">{{ __('Pais de nacimiento') }}</label>
                                    <input type="text" name="birth_country" id="birth_country"
                                           value="{{ old('birth_country', $person?->birth_country ?? 'Mexico') }}"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="residence_place" class="form-label">{{ __('Lugar de residencia') }}</label>
                                    <input type="text" name="residence_place" id="residence_place"
                                           value="{{ old('residence_place', $person?->residence_place) }}"
                                           placeholder="Ciudad, Estado"
                                           class="form-input">
                                </div>

                                <div>
                                    <label for="residence_country" class="form-label">{{ __('Pais de residencia') }}</label>
                                    <input type="text" name="residence_country" id="residence_country"
                                           value="{{ old('residence_country', $person?->residence_country ?? 'Mexico') }}"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="occupation" class="form-label">{{ __('Ocupacion') }}</label>
                                    <input type="text" name="occupation" id="occupation"
                                           value="{{ old('occupation', $person?->occupation) }}"
                                           class="form-input">
                                </div>

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
                    <div class="card mb-6" x-data="{ hasHeritage: {{ old('has_ethnic_heritage', $person?->has_ethnic_heritage) ? 'true' : 'false' }} }">
                        <div class="card-header flex items-center gap-2">
                             <div class="flex gap-1">

                            </div> 
                            <h2 class="text-lg font-semibold">{{ __('Herencia cultural') }}</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="has_ethnic_heritage" value="1"
                                       class="form-checkbox"
                                       x-model="hasHeritage"
                                       {{ old('has_ethnic_heritage', $person?->has_ethnic_heritage) ? 'checked' : '' }}>
                                <span class="font-medium">{{ __('Tengo herencia cultural') }}</span>
                            </label>

                            <div x-show="hasHeritage" x-transition class="space-y-4 pt-4 border-t">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="heritage_region" class="form-label">{{ __('Region de origen') }}</label>
                                        <select name="heritage_region" id="heritage_region" class="form-input">
                                            <option value="">{{ __('Seleccionar...') }}</option>
                                            @foreach($heritageRegions as $key => $name)
                                                <option value="{{ $key }}" {{ old('heritage_region', $person?->heritage_region) == $key ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="origin_town" class="form-label">{{ __('Ciudad/Pueblo de origen') }}</label>
                                        <input type="text" name="origin_town" id="origin_town"
                                               value="{{ old('origin_town', $person?->origin_town) }}"
                                               placeholder="Ej: Split, Dubrovnik, Zagreb..."
                                               class="form-input">
                                    </div>
                                </div>

                                <div>
                                    <label for="migration_decade" class="form-label">{{ __('Decada de migracion familiar') }}</label>
                                    <select name="migration_decade" id="migration_decade" class="form-input">
                                        <option value="">{{ __('No lo se / No aplica') }}</option>
                                        @foreach(config('mi-familia.migration_decades') as $value => $label)
                                            <option value="{{ $value }}" {{ old('migration_decade', $person?->migration_decade) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuracion -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Preferencias') }}</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="language" class="form-label">{{ __('Idioma') }}</label>
                                    <select name="language" id="language" class="form-input">
                                        <option value="es" {{ old('language', $user->language) == 'es' ? 'selected' : '' }}>Español</option>
                                        <option value="en" {{ old('language', $user->language) == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="privacy_level" class="form-label">{{ __('Nivel de privacidad') }}</label>
                                    <select name="privacy_level" id="privacy_level" class="form-input">
                                        <option value="direct_family" {{ old('privacy_level', $user->privacy_level) == 'direct_family' ? 'selected' : '' }}>
                                            {{ __('Solo familia directa') }}
                                        </option>
                                        <option value="extended_family" {{ old('privacy_level', $user->privacy_level) == 'extended_family' ? 'selected' : '' }}>
                                            {{ __('Familia extendida') }}
                                        </option>
                                        <option value="selected_users" {{ old('privacy_level', $user->privacy_level) == 'selected_users' ? 'selected' : '' }}>
                                            {{ __('Familia + usuarios seleccionados') }}
                                        </option>
                                        <option value="community" {{ old('privacy_level', $user->privacy_level) == 'community' ? 'selected' : '' }}>
                                            {{ __('Toda la comunidad') }}
                                        </option>
                                    </select>
                                    <p class="form-help">{{ __('Define quien puede ver tu perfil y tu arbol.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('dashboard') }}" class="btn-outline">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="btn-primary">
                            {{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
