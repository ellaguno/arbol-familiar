<x-guest-layout>
    <x-slot name="title">{{ __('Registro') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-amber-500 text-center">{{ __('¡Bienvenido a Mi Familia!') }}</h1>
                    <p class="text-amber-500 mt-1 text-center">{{ __('Por favor ingresa la siguiente información para configurar tu perfil.') }}</p>
                    <p class="text-theme-secondary mt-3 text-sm leading-relaxed text-left"><strong class="text-amber-500">{{ config('app.name') }}</strong> {{ __('es una plataforma genealógica desarrollada para ayudarte a explorar el alcance de tus raíces familiares y conectar con tu comunidad.') }}</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-6" id="register-form"
                      x-data="{
                          hasHeritage: '{{ old('has_ethnic_heritage', '') }}'
                      }">
                    @csrf

                    <!-- Datos de la cuenta -->
                    <div class="border-b border-theme pb-6">
                        <h2 class="text-lg font-semibold text-theme mb-4">{{ __('Datos de la cuenta') }}</h2>

                        <div class="grid md:grid-cols-2 gap-4">
                            <x-input
                                type="email"
                                name="email"
                                :label="__('Correo electronico')"
                                :value="old('email')"
                                required
                            />

                            <x-input
                                type="email"
                                name="email_confirmation"
                                :label="__('Confirmar correo')"
                                required
                            />

                            <div>
                                <label for="password" class="form-label">{{ __('Contrasena') }} <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password" class="form-input @error('password') border-red-500 @enderror" required>
                                @error('password')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-help">{{ __('Minimo 8 caracteres, mayusculas, minusculas, numeros y simbolos') }}</p>
                            </div>

                            <x-input
                                type="password"
                                name="password_confirmation"
                                :label="__('Confirmar contrasena')"
                                required
                            />
                        </div>
                    </div>

                    <!-- Datos personales -->
                    <div class="border-b border-theme pb-6">
                        <h2 class="text-lg font-semibold text-theme mb-4">{{ __('Datos personales') }}</h2>

                        <div class="grid md:grid-cols-2 gap-4">
                            <x-input
                                name="first_name"
                                :label="__('Nombre(s)')"
                                :value="old('first_name')"
                                required
                            />

                            <x-input
                                name="patronymic"
                                :label="__('Apellido paterno')"
                                :value="old('patronymic')"
                                required
                            />

                            <x-input
                                name="matronymic"
                                :label="__('Apellido materno')"
                                :value="old('matronymic')"
                            />

                            <x-select
                                name="gender"
                                :label="__('Genero')"
                                :options="['M' => __('Masculino'), 'F' => __('Femenino'), 'U' => __('Prefiero no decir')]"
                                :selected="old('gender')"
                                required
                                :placeholder="__('Selecciona...')"
                            />

                            <x-input
                                type="date"
                                name="birth_date"
                                :label="__('Fecha de nacimiento')"
                                :value="old('birth_date')"
                            />

                            <x-input
                                name="birth_country"
                                :label="__('Pais de nacimiento')"
                                :value="old('birth_country')"
                            />

                            <x-input
                                name="residence_country"
                                :label="__('Pais de residencia')"
                                :value="old('residence_country')"
                            />
                        </div>
                    </div>

                    <!-- Herencia cultural -->
                    @if($heritageEnabled ?? false)
                    <div class="border-b border-theme pb-6">
                        <h2 class="text-lg font-semibold text-theme mb-4">{{ $heritageLabel ?? __('Herencia cultural') }}</h2>

                        <div class="mb-4">
                            <label class="form-label">{{ __('¿Tienes una herencia cultural que deseas registrar?') }}</label>
                            <div class="flex gap-6 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="has_ethnic_heritage" value="1" class="form-radio"
                                           x-model="hasHeritage"
                                           {{ old('has_ethnic_heritage') == '1' ? 'checked' : '' }}>
                                    <span>{{ __('Si') }}</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="has_ethnic_heritage" value="0" class="form-radio"
                                           x-model="hasHeritage"
                                           {{ old('has_ethnic_heritage') == '0' ? 'checked' : '' }}>
                                    <span>{{ __('No') }}</span>
                                </label>
                            </div>
                            @error('has_ethnic_heritage')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="hasHeritage === '1'" x-transition class="space-y-4">
                            <div class="bg-mf-light p-4 rounded-lg">
                                <h3 class="font-medium text-theme mb-3">{{ __('Datos de herencia') }}</h3>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <x-select
                                        name="heritage_region"
                                        :label="__('Region de origen')"
                                        :options="$heritageRegions ?? []"
                                        :selected="old('heritage_region')"
                                        :placeholder="__('Selecciona la region...')"
                                    />

                                    <x-select
                                        name="migration_decade"
                                        :label="__('Decada de migracion')"
                                        :options="$heritageDecades ?? []"
                                        :selected="old('migration_decade')"
                                        :placeholder="__('Selecciona...')"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Privacidad -->
                    <div>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="privacy_accepted" value="1" class="form-checkbox mt-1" required
                                   {{ old('privacy_accepted') ? 'checked' : '' }}
                                   oninvalid="this.setCustomValidity('{{ __('Debes aceptar los terminos y condiciones para continuar') }}')"
                                   onchange="this.setCustomValidity('')">
                            <div>
                                <span class="text-theme-secondary">
                                    {{ __('He leido y acepto la') }}
                                    <a href="{{ route('privacy') }}" target="_blank" class="text-mf-primary hover:underline">{{ __('politica de privacidad') }}</a>
                                    {{ __('y los') }}
                                    <a href="{{ route('terms') }}" target="_blank" class="text-mf-primary hover:underline">{{ __('terminos de uso') }}</a>.
                                </span>
                            </div>
                        </label>
                        @error('privacy_accepted')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- reCAPTCHA v3 (invisible) -->
                    @if(config('mi-familia.recaptcha.enabled'))
                        <input type="hidden" name="g-recaptcha-response" id="recaptcha-response-register">
                        @error('g-recaptcha-response')
                            <p class="form-error text-center">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-theme-muted text-center">
                            {{ __('Protegido por reCAPTCHA') }} -
                            <a href="https://policies.google.com/privacy" target="_blank" class="underline">{{ __('Privacidad') }}</a> y
                            <a href="https://policies.google.com/terms" target="_blank" class="underline">{{ __('Terminos') }}</a>
                        </p>
                    @endif

                    <x-button type="submit" class="w-full" size="lg">
                        {{ __('Crear mi cuenta') }}
                    </x-button>
                </form>

                <div class="mt-6 pt-6 border-t border-theme text-center">
                    <p class="text-theme-secondary">
                        {{ __('Ya tienes cuenta?') }}
                        <a href="{{ route('login') }}" class="text-mf-primary hover:underline font-medium">
                            {{ __('Inicia sesion') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(config('mi-familia.recaptcha.enabled'))
    @push('scripts')
    <script>
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('mi-familia.recaptcha.site_key') }}', {action: 'register'}).then(function(token) {
                    document.getElementById('recaptcha-response-register').value = token;
                    form.submit();
                });
            });
        });
    </script>
    @endpush
    @endif
</x-guest-layout>
