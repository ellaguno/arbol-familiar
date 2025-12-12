<x-app-layout>
    <x-slot name="title">{{ __('Acceso denegado') }} - {{ config('app.name') }}</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <span class="text-8xl font-bold text-gray-200">403</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Acceso denegado') }}</h1>
            <p class="text-gray-600 mb-8">
                {{ __('No tienes permiso para acceder a esta pagina.') }}
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
