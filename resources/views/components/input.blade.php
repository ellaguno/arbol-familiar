@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'error' => null,
    'help' => null,
    'required' => false,
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

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        @if($type === 'date')
            min="1000-01-01"
            max="9999-12-31"
        @endif
        {{ $attributes->merge(['class' => 'form-input' . ($error ? ' border-red-500 focus:border-red-500 focus:ring-red-500' : '')]) }}
    >

    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif

    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
