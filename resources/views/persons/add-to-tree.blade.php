<x-app-layout>
    <x-slot name="title">{{ __('Agregar a mi árbol') }} - {{ $person->full_name }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h1 class="text-xl font-bold text-gray-900">{{ __('Agregar a mi árbol') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('Establece la relacion familiar con esta persona') }}</p>
            </div>
            <div class="card-body">
                <!-- Persona a agregar -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-4">
                        @if($person->photo_path)
                            <img src="{{ Storage::url($person->photo_path) }}" alt="{{ $person->full_name }}"
                                 class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-100 flex items-center justify-center">
                                <span class="text-{{ $person->gender === 'M' ? 'blue' : 'pink' }}-600 text-xl font-bold">
                                    {{ substr($person->first_name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-900 text-lg">{{ $person->full_name }}</p>
                            @if($person->birth_year || $person->birth_date)
                                <p class="text-sm text-gray-500">
                                    {{ $person->birth_year ?? $person->birth_date->format('Y') }}
                                    @if(!$person->is_living)
                                        - {{ $person->death_year ?? ($person->death_date ? $person->death_date->format('Y') : '?') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tu perfil -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-600 mb-2">{{ __('Tu perfil') }}</p>
                    <div class="flex items-center gap-3">
                        @if($userPerson->photo_path)
                            <img src="{{ Storage::url($userPerson->photo_path) }}" alt="{{ $userPerson->full_name }}"
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-blue-200 flex items-center justify-center">
                                <span class="text-blue-600 font-bold">{{ substr($userPerson->first_name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $userPerson->full_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('persons.add-to-tree.store', $person) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Selector de relacion -->
                    <div>
                        <label class="form-label">{{ __('¿Que es :name para ti?', ['name' => $person->first_name]) }} <span class="text-red-500">*</span></label>

                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <!-- Padre -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="relationship" value="father" class="sr-only peer" required
                                       {{ $person->gender === 'M' ? '' : 'disabled' }}>
                                <div class="peer-checked:ring-2 peer-checked:ring-[#3b82f6] peer-checked:border-[#3b82f6] absolute inset-0 rounded-lg border-2 border-transparent"></div>
                                <div class="flex items-center gap-3 {{ $person->gender !== 'M' ? 'opacity-40' : '' }}">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('Mi padre') }}</span>
                                </div>
                            </label>

                            <!-- Madre -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="relationship" value="mother" class="sr-only peer" required
                                       {{ $person->gender === 'F' ? '' : 'disabled' }}>
                                <div class="peer-checked:ring-2 peer-checked:ring-[#3b82f6] peer-checked:border-[#3b82f6] absolute inset-0 rounded-lg border-2 border-transparent"></div>
                                <div class="flex items-center gap-3 {{ $person->gender !== 'F' ? 'opacity-40' : '' }}">
                                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('Mi madre') }}</span>
                                </div>
                            </label>

                            <!-- Hijo/a -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="relationship" value="child" class="sr-only peer" required>
                                <div class="peer-checked:ring-2 peer-checked:ring-[#3b82f6] peer-checked:border-[#3b82f6] absolute inset-0 rounded-lg border-2 border-transparent"></div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('Mi hijo/a') }}</span>
                                </div>
                            </label>

                            <!-- Hermano/a -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="relationship" value="sibling" class="sr-only peer" required>
                                <div class="peer-checked:ring-2 peer-checked:ring-[#3b82f6] peer-checked:border-[#3b82f6] absolute inset-0 rounded-lg border-2 border-transparent"></div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('Mi hermano/a') }}</span>
                                </div>
                            </label>

                            <!-- Conyuge -->
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="relationship" value="spouse" class="sr-only peer" required>
                                <div class="peer-checked:ring-2 peer-checked:ring-[#3b82f6] peer-checked:border-[#3b82f6] absolute inset-0 rounded-lg border-2 border-transparent"></div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ __('Mi conyuge') }}</span>
                                </div>
                            </label>
                        </div>

                        @error('relationship')
                            <p class="form-error mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nota informativa -->
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-amber-700">
                                <p class="font-medium mb-1">{{ __('¿Que significa esto?') }}</p>
                                <p>{{ __('Al agregar esta persona a tu arbol, se creara una relacion familiar entre tu perfil y el suyo. Ambos apareceran conectados en el arbol genealogico.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center gap-4 pt-4 border-t">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Agregar a mi árbol') }}
                        </button>
                        <a href="{{ route('persons.show', $person) }}" class="btn-outline">
                            {{ __('Cancelar') }}
                        </a>
                    </div>
                </form>

                <!-- Enlace a fusionar (menos prominente) -->
                @if(!$person->user_id)
                    <div class="mt-8 pt-6 border-t">
                        <p class="text-sm text-gray-500 mb-2">{{ __('¿Esta persona eres tu mismo pero con otro registro?') }}</p>
                        <a href="{{ route('persons.merge', $person) }}" class="text-sm text-gray-600 hover:text-gray-800 underline">
                            {{ __('Fusionar registros duplicados') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
