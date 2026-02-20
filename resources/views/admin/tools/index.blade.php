<x-app-layout>
    <x-slot name="title">{{ __('Herramientas') }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Herramientas') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Utilidades de mantenimiento y correccion de datos') }}</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Corregir apellidos -->
            <a href="{{ route('admin.tools.fix-surnames') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme text-lg">{{ __('Corregir apellidos') }}</h3>
                        </div>
                    </div>
                    <p class="text-sm text-theme-muted">
                        {{ __('Detecta y separa apellidos compuestos (patronymic/matronymic) usando datos de padres, hermanos e hijos.') }}
                    </p>
                </div>
            </a>
            <!-- Buscar duplicados -->
            <a href="{{ route('admin.tools.duplicates') }}" class="card hover:shadow-md transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-theme text-lg">{{ __('Buscar duplicados') }}</h3>
                        </div>
                    </div>
                    <p class="text-sm text-theme-muted">
                        {{ __('Detecta personas con nombre y apellido identicos. Permite comparar, fusionar o eliminar duplicados.') }}
                    </p>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
