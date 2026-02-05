<x-app-layout>
    <x-slot name="title">{{ __('Sesion expirada') }} - {{ config('app.name') }}</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <span class="text-8xl font-bold text-gray-200 dark:text-gray-700">419</span>
            </div>
            <h1 class="text-3xl font-bold text-theme mb-4">{{ __('Sesion expirada') }}</h1>
            <p class="text-theme-secondary mb-8">
                {{ __('Tu sesion ha expirado. Por favor, vuelve a cargar la pagina e intenta de nuevo.') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="btn-outline">
                    {{ __('Regresar') }}
                </a>
                <a href="{{ route('home') }}" class="btn-primary">
                    {{ __('Ir al inicio') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
