<x-guest-layout>
    <x-slot name="title">{{ __('Verificar correo') }} - {{ config('app.name') }}</x-slot>

    <div class="card">
        <div class="card-body text-center">
            <div class="w-16 h-16 bg-mf-light rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-theme mb-2">{{ __('Verifica tu correo electronico') }}</h1>
            <p class="text-theme-secondary mb-6">
                {{ __('Te hemos enviado un enlace de verificacion a') }}
                <strong>{{ auth()->user()->email }}</strong>
            </p>

            @if(session('status'))
                <x-alert type="success" class="mb-6">
                    {{ session('status') }}
                </x-alert>
            @endif

            <div class="bg-theme-secondary rounded-lg p-6 mb-6">
                <p class="text-sm text-theme-secondary mb-4">
                    {{ __('Tambien puedes verificar tu cuenta ingresando el codigo de 6 digitos que enviamos a tu correo:') }}
                </p>

                <form method="POST" action="{{ route('verification.verify.code') }}" class="max-w-xs mx-auto">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="code" maxlength="6"
                               class="form-input text-center text-lg tracking-widest"
                               placeholder="______">
                        <x-button type="submit">{{ __('Verificar') }}</x-button>
                    </div>
                    @error('code')
                        <p class="form-error mt-2">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            <p class="text-sm text-theme-muted mb-4">
                {{ __('No recibiste el correo? Revisa tu carpeta de spam o') }}
            </p>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <x-button type="submit" variant="outline">
                    {{ __('Reenviar correo de verificacion') }}
                </x-button>
            </form>

            <div class="mt-6 pt-6 border-t border-theme">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-theme-muted hover:text-theme-secondary">
                        {{ __('Cerrar sesion') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
