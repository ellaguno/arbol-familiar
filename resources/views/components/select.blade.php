@props([
    'name' => '',
    'label' => null,
    'error' => null,
    'help' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-select' . ($error ? ' border-red-500 focus:border-red-500 focus:ring-red-500' : '')]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif

    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
