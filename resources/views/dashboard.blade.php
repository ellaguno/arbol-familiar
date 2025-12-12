<x-app-layout>
    <x-slot name="title">{{ __('Dashboard') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Saludo -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-red-600">
                {{ __('Hola') }}, {{ $person ? $person->first_name : $user->email }}!
            </h1>
            <p class="text-gray-600 mt-1">{{ __('Bienvenido a tu arbol genealogico') }}</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Columna principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta de perfil -->
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start gap-6">
                            @if($person && $person->photo_path)
                                <img src="{{ Storage::url($person->photo_path) }}" alt="{{ $person->full_name }}"
                                     class="avatar avatar-xl">
                            @else
                                <div class="avatar avatar-xl bg-mf-primary text-white flex items-center justify-center text-2xl font-bold">
                                    {{ $person ? strtoupper(substr($person->first_name, 0, 1)) : strtoupper(substr($user->email, 0, 1)) }}
                                </div>
                            @endif

                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    {{ $person ? $person->full_name : $user->email }}
                                </h2>
                                @if($person)
                                    <p class="text-gray-600">
                                        @if($person->birth_date)
                                            {{ $person->age }} {{ __('anos') }} &bull;
                                        @endif
                                        @if($person->residence_place)
                                            {{ $person->residence_place }}
                                        @endif
                                    </p>
                                    @if($person->has_ethnic_heritage)
                                        <span class="badge badge-primary mt-2">
                                            {{ __('Herencia cultural') }}
                                            @if($person->heritage_region)
                                                - {{ config('mi-familia.heritage_regions')[$person->heritage_region] ?? $person->heritage_region }}
                                            @endif
                                        </span>
                                    @endif
                                @endif

                                <div class="flex gap-3 mt-4">
                                    <a href="{{ route('profile.edit') }}" class="btn-outline btn-sm">
                                        {{ __('Editar perfil') }}
                                    </a>
                                    <a href="{{ route('tree.index') }}" class="btn-primary btn-sm">
                                        {{ __('Ver mi arbol') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadisticas -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-3xl text-red-600 font-bold text-mf-primary">{{ $stats['persons_count'] }}</div>
                            <div class="text-sm text-gray-600">{{ __('Personas') }}</div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-3xl text-red-600 font-bold text-mf-primary">{{ $stats['families_count'] }}</div>
                            <div class="text-sm text-gray-600">{{ __('Familias') }}</div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-3xl text-red-600 font-bold text-mf-primary">{{ $stats['media_count'] }}</div>
                            <div class="text-sm text-gray-600">{{ __('Archivos') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Familia cercana -->
                @if($family)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">{{ __('Tu familia cercana') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @if($family['father'])
                                    <div class="text-center">
                                        <div class="avatar avatar-lg bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $family['father']->first_name }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Padre') }}</p>
                                    </div>
                                @endif

                                @if($family['mother'])
                                    <div class="text-center">
                                        <div class="avatar avatar-lg bg-pink-100 text-pink-600 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $family['mother']->first_name }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Madre') }}</p>
                                    </div>
                                @endif

                                @if($family['spouse'])
                                    <div class="text-center">
                                        <div class="avatar avatar-lg bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $family['spouse']->first_name }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Conyuge') }}</p>
                                    </div>
                                @endif

                                @if($family['children']->count() > 0)
                                    <div class="text-center">
                                        <div class="avatar avatar-lg bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $family['children']->count() }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Hijos') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 text-center">
                                <a href="{{ route('tree.index') }}" class="text-mf-primary hover:underline text-sm">
                                    {{ __('Ver arbol completo') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Comienza tu arbol') }}</h3>
                            <p class="text-gray-600 mb-4">{{ __('Agrega a tus primeros familiares') }}</p>
                            <a href="{{ route('tree.index') }}" class="btn-primary">
                                {{ __('Ir a mi arbol') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Columna lateral -->
            <div class="space-y-6">
                <!-- Mensajes -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold">{{ __('Mensajes') }}</h3>
                        @if($unreadMessages->count() > 0)
                            <span class="badge badge-primary">{{ $unreadMessages->count() }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($unreadMessages->count() > 0)
                            <div class="space-y-3">
                                @foreach($unreadMessages as $message)
                                    <a href="{{ route('messages.show', $message) }}" class="block p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                        <p class="font-medium text-gray-900 text-sm">{{ Str::limit($message->subject, 40) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->diffForHumans() }}</p>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('messages.inbox') }}" class="block text-center text-mf-primary hover:underline text-sm mt-4">
                                {{ __('Ver todos los mensajes') }}
                            </a>
                        @else
                            <p class="text-gray-500 text-sm text-center py-4">{{ __('No tienes mensajes nuevos') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Acciones rapidas -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold">{{ __('Acciones rapidas') }}</h3>
                    </div>
                    <div class="card-body space-y-2">
                        <a href="{{ route('persons.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">{{ __('Agregar familiar') }}</span>
                        </a>

                        <a href="{{ route('gedcom.import') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">{{ __('Importar GEDCOM') }}</span>
                        </a>

                        <a href="{{ route('search.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">{{ __('Buscar familiares') }}</span>
                        </a>

                        @if($person)
                        <a href="{{ route('persons.family-edit-access') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">{{ __('Editar mi familia') }}</span>
                        </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
