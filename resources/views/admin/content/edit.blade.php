<x-app-layout>
    <x-slot name="title">{{ __('Editar') }} {{ $groupName }} - {{ __('Administracion') }}</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ $groupName }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Edita los textos e imagenes de esta seccion') }}</p>
            </div>
            <a href="{{ route('admin.content') }}" class="btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Volver') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('admin.content.update', $group) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                @foreach($settings as $key => $setting)
                    <div class="card">
                        <div class="card-body">
                            <label for="settings_{{ $key }}" class="form-label text-base">
                                {{ __(str_replace('_', ' ', ucfirst($key))) }}
                                <span class="text-xs text-theme-muted ml-2">({{ $setting->type }})</span>
                            </label>

                            @if($key === 'feature_images_shape')
                                <select name="settings_{{ $key }}" id="settings_{{ $key }}" class="form-input">
                                    <option value="round" {{ old('settings_' . $key, $setting->value) === 'round' ? 'selected' : '' }}>{{ __('Redondas') }}</option>
                                    <option value="square" {{ old('settings_' . $key, $setting->value) === 'square' ? 'selected' : '' }}>{{ __('Cuadradas') }}</option>
                                </select>

                            @elseif($setting->type === 'text')
                                <input type="text"
                                       name="settings_{{ $key }}"
                                       id="settings_{{ $key }}"
                                       value="{{ old('settings_' . $key, $setting->value) }}"
                                       class="form-input">

                            @elseif($setting->type === 'textarea')
                                <textarea name="settings_{{ $key }}"
                                          id="settings_{{ $key }}"
                                          rows="4"
                                          class="form-input resize-y">{{ old('settings_' . $key, $setting->value) }}</textarea>

                            @elseif($setting->type === 'html')
                                <textarea name="settings_{{ $key }}"
                                          id="settings_{{ $key }}"
                                          rows="8"
                                          class="form-input resize-y font-mono text-sm">{{ old('settings_' . $key, $setting->value) }}</textarea>
                                <p class="text-xs text-theme-muted mt-1">{{ __('Se permite HTML. Los enlaces, imagenes y estilos se renderizaran directamente.') }}</p>

                            @elseif($setting->type === 'image')
                                <div class="space-y-3">
                                    @if($setting->value)
                                        <div class="flex items-center gap-4">
                                            <div class="w-32 h-20 rounded-lg overflow-hidden bg-theme-secondary border">
                                                <img src="{{ asset($setting->value) }}"
                                                     alt="{{ $key }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center w-full h-full text-gray-400 text-xs\'>{{ __("Sin imagen") }}</div>'">
                                            </div>
                                            <span class="text-sm text-theme-muted">{{ $setting->value }}</span>
                                        </div>
                                    @endif
                                    <input type="file"
                                           name="settings_{{ $key }}"
                                           id="settings_{{ $key }}"
                                           accept="image/*"
                                           class="block w-full text-sm text-theme-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="text-xs text-theme-muted">{{ __('Deja vacio para mantener la imagen actual. Max 2MB.') }}</p>
                                </div>

                            @elseif($setting->type === 'color')
                                <div class="flex items-center gap-3">
                                    <input type="color"
                                           name="settings_{{ $key }}"
                                           id="settings_{{ $key }}"
                                           value="{{ old('settings_' . $key, $setting->value) }}"
                                           class="h-10 w-20 rounded border border-theme cursor-pointer">
                                    <input type="text"
                                           value="{{ $setting->value }}"
                                           class="form-input w-32 font-mono text-sm"
                                           readonly>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('admin.content') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Guardar cambios') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
