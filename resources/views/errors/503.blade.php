<x-app-layout>
    <x-slot name="title">{{ __('Sitio en mantenimiento') }} - {{ config('app.name') }}</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <span class="text-8xl font-bold text-gray-200 dark:text-gray-700">503</span>
            </div>
            <h1 class="text-3xl font-bold text-theme mb-4">{{ __('Sitio en mantenimiento') }}</h1>
            <p class="text-theme-secondary mb-8">
                {{ __('Estamos realizando mejoras. Por favor, vuelve en unos minutos.') }}
            </p>
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="btn-primary">
                    {{ __('Ir al inicio') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
