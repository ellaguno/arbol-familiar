<x-guest-layout>
    <x-slot name="title">{{ __('Recuperar contraseña') }} - {{ config('app.name') }}</x-slot>

    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-mf-light rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-mf-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-theme">{{ __('Recuperar contraseña') }}</h1>
                <p class="text-red-600 mt-1">{{ __('Te enviaremos un enlace para restablecer tu contraseña') }}</p>
            </div>

            @if(session('status'))
                <x-alert type="success" class="mb-6">
                    {{ session('status') }}
                </x-alert>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <x-input
                    type="email"
                    name="email"
                    :label="__('Correo electronico')"
                    :value="old('email')"
                    required
                    autofocus
                />

                <x-button type="submit" class="w-full">
                    {{ __('Enviar enlace de recuperacion') }}
                </x-button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-mf-primary hover:underline">
                    {{ __('Volver al inicio de sesion') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
