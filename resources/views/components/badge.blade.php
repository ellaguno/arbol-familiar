@props([
    'type' => 'primary',
])

@php
    $types = [
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
    ];
@endphp

<span {{ $attributes->merge(['class' => $types[$type] ?? $types['primary']]) }}>
    {{ $slot }}
</span>
