<x-app-layout>
    <x-slot name="title">{{ $family->label }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('families.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Familias') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ $family->label }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Columna izquierda: Conyuges -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Esposo -->
                @if($family->husband)
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                @if($family->husband->photo_path)
                                    <img src="{{ Storage::url($family->husband->photo_path) }}"
                                         alt="{{ $family->husband->full_name }}"
                                         class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-blue-100">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
                                        <span class="text-blue-600 font-bold text-3xl">{{ substr($family->husband->first_name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold">{{ $family->husband->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Esposo') }}</p>
                            @if($family->husband->birth_date)
                                <p class="text-sm text-gray-500 mt-1">{{ $family->husband->birth_date->format('d/m/Y') }}</p>
                            @endif
                            <a href="{{ route('persons.show', $family->husband) }}" class="btn-outline btn-sm mt-4">
                                {{ __('Ver perfil') }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Esposa -->
                @if($family->wife)
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                @if($family->wife->photo_path)
                                    <img src="{{ Storage::url($family->wife->photo_path) }}"
                                         alt="{{ $family->wife->full_name }}"
                                         class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-pink-100">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-pink-100 flex items-center justify-center mx-auto">
                                        <span class="text-pink-600 font-bold text-3xl">{{ substr($family->wife->first_name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold">{{ $family->wife->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Esposa') }}</p>
                            @if($family->wife->birth_date)
                                <p class="text-sm text-gray-500 mt-1">{{ $family->wife->birth_date->format('d/m/Y') }}</p>
                            @endif
                            <a href="{{ route('persons.show', $family->wife) }}" class="btn-outline btn-sm mt-4">
                                {{ __('Ver perfil') }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Acciones -->
                <div class="card">
                    <div class="card-body space-y-2">
                        <a href="{{ route('families.edit', $family) }}" class="btn-primary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('Editar familia') }}
                        </a>
                        @if($family->husband)
                            <a href="{{ route('tree.view', $family->husband) }}" class="btn-outline w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ __('Ver en arbol') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Informacion y Hijos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informacion del matrimonio -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold">{{ __('Informacion') }}</h2>
                    </div>
                    <div class="card-body">
                        <dl class="grid md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">{{ __('Estado') }}</dt>
                                <dd class="text-gray-900 font-medium">
                                    @switch($family->status)
                                        @case('married') {{ __('Casados') }} @break
                                        @case('divorced') {{ __('Divorciados') }} @break
                                        @case('widowed') {{ __('Viudo/a') }} @break
                                        @case('separated') {{ __('Separados') }} @break
                                        @case('partners') {{ __('Pareja') }} @break
                                        @default {{ __('Desconocido') }}
                                    @endswitch
                                </dd>
                            </div>
                            @if($family->marriage_date)
                                <div>
                                    <dt class="text-sm text-gray-500">{{ __('Fecha de matrimonio') }}</dt>
                                    <dd class="text-gray-900">
                                        {{ $family->marriage_date->format('d/m/Y') }}
                                        @if($family->marriage_date_approx)
                                            <span class="text-gray-500">({{ __('aprox.') }})</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            @if($family->marriage_place)
                                <div>
                                    <dt class="text-sm text-gray-500">{{ __('Lugar de matrimonio') }}</dt>
                                    <dd class="text-gray-900">{{ $family->marriage_place }}</dd>
                                </div>
                            @endif
                            @if($family->divorce_date)
                                <div>
                                    <dt class="text-sm text-gray-500">{{ __('Fecha de divorcio') }}</dt>
                                    <dd class="text-gray-900">{{ $family->divorce_date->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                            @if($family->hasEthnicHeritage())
                                <div>
                                    <dt class="text-sm text-gray-500">{{ __('Herencia cultural') }}</dt>
                                    <dd>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-100 text-blue-800 text-sm">
                                            *
                                        </span>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Hijos -->
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="text-lg font-semibold">{{ __('Hijos') }} ({{ $family->children->count() }})</h2>
                    </div>
                    <div class="card-body">
                        @if($family->children->isEmpty())
                            <p class="text-gray-500 text-center py-8">{{ __('Esta familia no tiene hijos registrados.') }}</p>
                        @else
                            <div class="space-y-4">
                                @foreach($family->children as $index => $child)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-4">
                                            <span class="text-gray-400 font-medium">{{ $index + 1 }}</span>
                                            @if($child->photo_path)
                                                <img src="{{ Storage::url($child->photo_path) }}" class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-{{ $child->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                                    <span class="text-{{ $child->gender === 'M' ? 'blue' : 'pink' }}-600 font-bold">{{ substr($child->first_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('persons.show', $child) }}" class="font-medium text-gray-900 hover:text-mf-primary">
                                                    {{ $child->full_name }}
                                                </a>
                                                <p class="text-sm text-gray-500">
                                                    {{ $child->gender === 'M' ? __('Hijo') : __('Hija') }}
                                                    @if($child->birth_date)
                                                        - {{ $child->birth_date->format('Y') }}
                                                    @endif
                                                    @if($child->pivot->relationship_type !== 'biological')
                                                        <span class="text-xs">({{ $child->pivot->relationship_type }})</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('persons.show', $child) }}" class="text-gray-500 hover:text-gray-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('families.children.remove', [$family, $child]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('{{ __('Remover de esta familia?') }}')">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Eventos -->
                @if($family->events && $family->events->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-lg font-semibold">{{ __('Eventos') }}</h2>
                        </div>
                        <div class="card-body">
                            <ul class="space-y-3">
                                @foreach($family->events as $event)
                                    <li class="flex items-start gap-3">
                                        <div class="w-2 h-2 rounded-full bg-mf-primary mt-2"></div>
                                        <div>
                                            <p class="font-medium">{{ $event->type_label }}</p>
                                            @if($event->date)
                                                <p class="text-sm text-gray-500">{{ $event->date->format('d/m/Y') }}</p>
                                            @endif
                                            @if($event->place)
                                                <p class="text-sm text-gray-500">{{ $event->place }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
