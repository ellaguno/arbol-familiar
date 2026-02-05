<x-guest-layout>
    <x-slot name="title">{{ __('Nueva contrasena') }} - {{ config('app.name') }}</x-slot>

    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-mf-light rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-theme">{{ __('Crear nueva contrasena') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Ingresa tu nueva contrasena') }}</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <x-input
                    type="email"
                    name="email"
                    :label="__('Correo electronico')"
                    :value="$email ?? old('email')"
                    required
                />

                <div>
                    <label for="password" class="form-label">{{ __('Nueva contrasena') }} <span class="text-red-500">*</span></label>
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

                <x-button type="submit" class="w-full">
                    {{ __('Restablecer contrasena') }}
                </x-button>
            </form>
        </div>
    </div>
</x-guest-layout>
