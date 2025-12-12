@php
    $family = $family ?? null;
    $childIds = $childIds ?? [];
@endphp

<!-- Conyuges -->
<div class="card">
    <div class="card-header">
        <h2 class="text-lg font-semibold">{{ __('Conyuges') }}</h2>
    </div>
    <div class="card-body">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label for="husband_id" class="form-label">{{ __('Esposo') }}</label>
                <select name="husband_id" id="husband_id" class="form-input @error('husband_id') border-red-500 @enderror">
                    <option value="">{{ __('Seleccionar...') }}</option>
                    @foreach($persons->where('gender', 'M') as $person)
                        <option value="{{ $person->id }}" {{ old('husband_id', $family?->husband_id) == $person->id ? 'selected' : '' }}>
                            {{ $person->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('husband_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="wife_id" class="form-label">{{ __('Esposa') }}</label>
                <select name="wife_id" id="wife_id" class="form-input @error('wife_id') border-red-500 @enderror">
                    <option value="">{{ __('Seleccionar...') }}</option>
                    @foreach($persons->where('gender', 'F') as $person)
                        <option value="{{ $person->id }}" {{ old('wife_id', $family?->wife_id) == $person->id ? 'selected' : '' }}>
                            {{ $person->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('wife_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p class="form-help mt-2">{{ __('Debe seleccionar al menos un conyuge.') }}</p>
    </div>
</div>

<!-- Matrimonio -->
<div class="card">
    <div class="card-header">
        <h2 class="text-lg font-semibold">{{ __('Matrimonio') }}</h2>
    </div>
    <div class="card-body">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label for="status" class="form-label">{{ __('Estado') }} <span class="text-red-500">*</span></label>
                <select name="status" id="status" class="form-input @error('status') border-red-500 @enderror" required>
                    <option value="married" {{ old('status', $family?->status ?? 'married') === 'married' ? 'selected' : '' }}>{{ __('Casados') }}</option>
                    <option value="partners" {{ old('status', $family?->status) === 'partners' ? 'selected' : '' }}>{{ __('Pareja') }}</option>
                    <option value="divorced" {{ old('status', $family?->status) === 'divorced' ? 'selected' : '' }}>{{ __('Divorciados') }}</option>
                    <option value="separated" {{ old('status', $family?->status) === 'separated' ? 'selected' : '' }}>{{ __('Separados') }}</option>
                    <option value="widowed" {{ old('status', $family?->status) === 'widowed' ? 'selected' : '' }}>{{ __('Viudo/a') }}</option>
                    <option value="annulled" {{ old('status', $family?->status) === 'annulled' ? 'selected' : '' }}>{{ __('Anulado') }}</option>
                </select>
                @error('status')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="marriage_date" class="form-label">{{ __('Fecha de matrimonio') }}</label>
                <div class="flex gap-2">
                    <input type="date" name="marriage_date" id="marriage_date"
                           value="{{ old('marriage_date', $family?->marriage_date?->format('Y-m-d')) }}"
                           min="1000-01-01" max="9999-12-31"
                           class="form-input flex-1">
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" name="marriage_date_approx" value="1"
                               class="form-checkbox"
                               {{ old('marriage_date_approx', $family?->marriage_date_approx) ? 'checked' : '' }}>
                        <span>{{ __('Aprox.') }}</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="marriage_place" class="form-label">{{ __('Lugar de matrimonio') }}</label>
                <input type="text" name="marriage_place" id="marriage_place"
                       value="{{ old('marriage_place', $family?->marriage_place) }}"
                       placeholder="{{ __('Ciudad, Pais') }}"
                       class="form-input">
            </div>

            @if($family)
                <div>
                    <label for="divorce_date" class="form-label">{{ __('Fecha de divorcio') }}</label>
                    <input type="date" name="divorce_date" id="divorce_date"
                           value="{{ old('divorce_date', $family?->divorce_date?->format('Y-m-d')) }}"
                           min="1000-01-01" max="9999-12-31"
                           class="form-input">
                </div>

                <div>
                    <label for="divorce_place" class="form-label">{{ __('Lugar de divorcio') }}</label>
                    <input type="text" name="divorce_place" id="divorce_place"
                           value="{{ old('divorce_place', $family?->divorce_place) }}"
                           class="form-input">
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Hijos -->
<div class="card" x-data="{ children: {{ json_encode(old('children', $childIds)) }} }">
    <div class="card-header">
        <h2 class="text-lg font-semibold">{{ __('Hijos') }}</h2>
    </div>
    <div class="card-body">
        <div class="space-y-4">
            <div>
                <label class="form-label">{{ __('Seleccionar hijos') }}</label>
                <div class="grid md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 border rounded-lg">
                    @foreach($persons as $person)
                        <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" name="children[]" value="{{ $person->id }}"
                                   class="form-checkbox"
                                   {{ in_array($person->id, old('children', $childIds)) ? 'checked' : '' }}>
                            <span class="text-sm">{{ $person->full_name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="form-help mt-2">{{ __('Los hijos seleccionados se agregaran a esta familia.') }}</p>
            </div>
        </div>
    </div>
</div>
