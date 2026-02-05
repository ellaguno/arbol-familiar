<x-app-layout>
    <x-slot name="title">{{ __('Nueva familia') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('families.index') }}" class="text-theme-muted hover:text-theme-secondary">{{ __('Familias') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-theme-muted" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-theme-secondary font-medium ml-1 md:ml-2">{{ __('Nueva familia') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-theme">{{ __('Nueva familia') }}</h1>
            <p class="text-theme-secondary mt-1">{{ __('Crea una nueva unidad familiar') }}</p>
        </div>

        <form action="{{ route('families.store') }}" method="POST" class="space-y-6">
            @csrf

            @include('families._form', ['family' => null])

            <div class="flex justify-end gap-4">
                <a href="{{ route('families.index') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">{{ __('Crear familia') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
