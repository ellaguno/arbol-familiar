@props(['class' => 'h-10 w-auto'])

<img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Mi Familia') }}" {{ $attributes->merge(['class' => $class . ' object-contain']) }}
     onerror="this.outerHTML='<span class=\'font-bold text-[#3b82f6]\'>Mi Familia</span>'">
