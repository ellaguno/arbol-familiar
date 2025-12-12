@props([
    'src' => null,
    'alt' => '',
    'size' => 'md',
    'initials' => null,
])

@php
    $sizes = [
        'sm' => 'avatar-sm text-xs',
        'md' => 'avatar-md text-sm',
        'lg' => 'avatar-lg text-base',
        'xl' => 'avatar-xl text-xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => 'avatar ' . $sizeClass]) }}
    >
@else
    <div {{ $attributes->merge(['class' => 'avatar ' . $sizeClass . ' bg-mf-primary text-white flex items-center justify-center font-medium']) }}>
        {{ $initials ?? strtoupper(substr($alt, 0, 2)) }}
    </div>
@endif
