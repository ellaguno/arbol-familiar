<x-guest-layout :hideLogo="true">
    <x-slot name="title">{{ __('Iniciar Sesion') }} - {{ config('app.name') }}</x-slot>

    @php
        $sc = isset($siteSettings) ? $siteSettings : null;
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <x-application-logo class="h-16 w-auto mx-auto mb-4" />
                <h1 class="text-2xl font-bold text-theme">{{ $sc ? $sc->content('login', 'title', __('Iniciar Sesion')) : __('Iniciar Sesion') }}</h1>
                <p class="text-theme-secondary mt-1">{{ $sc ? $sc->content('login', 'subtitle', __('Accede a tu arbol genealogico')) : __('Accede a tu arbol genealogico') }}</p>
            </div>

            @if(session('status'))
                <x-alert type="success" class="mb-4">
                    {{ session('status') }}
                </x-alert>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" id="login-form">
                @csrf

                <x-input
                    type="email"
                    name="email"
                    :label="__('Correo electronico')"
                    :value="old('email')"
                    required
                    autofocus
                />

                <div>
                    <label for="password" class="form-label">{{ __('Contrasena') }}</label>
                    <div class="relative" x-data="{ show: false }">
                        <input
                            :type="show ? 'text' : 'password'"
                            name="password"
                            id="password"
                            class="form-input pr-10 @error('password') border-red-500 @enderror"
                            required
                        >
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-theme-muted hover:text-theme-secondary"
                        >
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="form-checkbox" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-sm text-theme-secondary">{{ __('Recordarme') }}</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-mf-primary hover:underline">
                        {{ __('Olvidaste tu contrasena?') }}
                    </a>
                </div>

                <!-- reCAPTCHA v3 (invisible) -->
                @if(config('mi-familia.recaptcha.enabled'))
                    <input type="hidden" name="g-recaptcha-response" id="recaptcha-response-login">
                    @error('g-recaptcha-response')
                        <p class="form-error text-center">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-theme-muted text-center">
                        {{ __('Protegido por reCAPTCHA') }} -
                        <a href="https://policies.google.com/privacy" target="_blank" class="underline">{{ __('Privacidad') }}</a> y
                        <a href="https://policies.google.com/terms" target="_blank" class="underline">{{ __('Terminos') }}</a>
                    </p>
                @endif

                <x-button type="submit" class="w-full" id="login-btn">
                    {{ __('Ingresar') }}
                </x-button>
            </form>

            <div class="mt-6 pt-6 border-t border-theme text-center">
                <p class="text-theme-secondary">{{ $sc ? $sc->content('login', 'register_text', __('No tienes cuenta?')) : __('No tienes cuenta?') }}</p>
                <a href="{{ route('register') }}" class="btn-outline w-full mt-3">
                    {{ $sc ? $sc->content('login', 'register_button', __('Registrate gratis')) : __('Registrate gratis') }}
                </a>
            </div>
        </div>
    </div>

    @if(config('mi-familia.recaptcha.enabled'))
    @push('scripts')
    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('mi-familia.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                    document.getElementById('recaptcha-response-login').value = token;
                    form.submit();
                });
            });
        });
    </script>
    @endpush
    @endif
</x-guest-layout>
