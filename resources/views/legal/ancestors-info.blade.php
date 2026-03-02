<x-app-layout>
    <x-slot name="title">{{ __('Donde encontrar más información de mis antepasados') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-theme mb-8">{{ __('Donde encontrar más información de mis antepasados') }}</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none text-theme-secondary">
            <!-- Contenido editable aquí -->
            <p>{{ __('Próximamente encontrarás aquí recursos útiles para investigar tu historia familiar.') }}</p>
        </div>

        <div class="mt-8 pt-8 border-t border-theme">
            <a href="{{ url()->previous() }}" class="text-mf-primary hover:underline">
                &larr; {{ __('Volver') }}
            </a>
        </div>
    </div>
</x-app-layout>
