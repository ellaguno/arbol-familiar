@props(['hideLogo' => false])

<!-- Header -->
<header class="shadow-md sticky top-0 z-50" style="background-color: var(--mf-header-bg);" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-2">
            <!-- Logo -->
            @unless($hideLogo)
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Mi Familia" class="h-14 md:h-16 object-contain"
                     onerror="this.outerHTML='<span class=\'text-2xl font-bold text-[#3b82f6]\'>Mi Familia</span>'">
            </a>
            @else
            <div></div>
            @endunless

            <!-- Menús a la derecha (desktop) - SIEMPRE visible en lg+ -->
            <div class="desktop-menu">
                <!-- Fila superior: Idioma + controles -->
                <div class="flex items-center gap-1 text-xs">
                    <!-- Selector de idioma -->
                    <nav class="flex items-center gap-1">
                        <a href="?lang=es" class="{{ app()->getLocale() == 'es' ? 'text-red-600 font-semibold' : 'text-theme-muted hover:text-red-600' }}">ESP</a>
                        <span class="text-theme-muted">|</span>
                        <a href="?lang=en" class="{{ app()->getLocale() == 'en' ? 'text-red-600 font-semibold' : 'text-theme-muted hover:text-red-600' }}">ENG</a>
                    </nav>

                    @auth
                        <span class="text-theme-muted mx-1">|</span>
                        <!-- Salir -->
                        <form method="POST" action="{{ route('logout') }}" class="mx-1 inline">
                            @csrf
                            <button type="submit" class="text-theme-muted hover:text-red-600 font-medium">
                                {{ __('SALIR') }}
                            </button>
                        </form>
                    @else
                        <span class="text-theme-muted mx-1">|</span>
                        <a href="{{ route('login') }}" class="text-theme-muted hover:text-[#3b82f6] font-medium">
                            {{ __('INGRESAR') }}
                        </a>
                    @endauth
                </div>

                @auth
                    <!-- Fila inferior: Navegación principal -->
                    <nav class="flex items-center text-[13px] whitespace-nowrap mt-1" style="line-height: 21.31px;">
                        <a href="{{ route('dashboard') }}" class="px-2 py-0.5 {{ request()->routeIs('dashboard') ? 'text-[#EC1C24]' : 'text-[#8896C9] hover:text-[#EC1C24]' }}" title="{{ __('Inicio') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>
                        <a href="{{ route('tree.index') }}" class="px-2 py-0.5 {{ request()->routeIs('tree.*') ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                            {{ __('ÁRBOL') }}
                        </a>
                        <a href="{{ route('messages.inbox') }}" class="px-2 py-0.5 {{ request()->routeIs('messages.*') ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }} relative">
                            {{ __('MENSAJES') }}
                            @php $unreadCount = auth()->user()->unreadMessages()->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-3 w-3 flex items-center justify-center">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                        @if(auth()->user()->person)
                            <a href="{{ route('persons.show', auth()->user()->person) }}" class="px-2 py-0.5 {{ request()->routeIs('profile.*') || (request()->routeIs('persons.show') && request()->route('person')?->id == auth()->user()->person_id) ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                                {{ __('PERFIL') }}
                            </a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="px-2 py-0.5 {{ request()->routeIs('profile.*') ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                                {{ __('PERFIL') }}
                            </a>
                        @endif
                        <a href="{{ route('search.index') }}" class="px-2 py-0.5 {{ request()->routeIs('search.*') ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                            {{ __('BÚSQUEDA') }}
                        </a>
                        @php
                            $isOwnProfile = request()->routeIs('persons.show') && request()->route('person')?->id == auth()->user()->person_id;
                            $isCommunityActive = request()->routeIs('persons.*') && !$isOwnProfile;
                        @endphp
                        <a href="{{ route('persons.index') }}" class="px-2 py-0.5 {{ $isCommunityActive ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                            {{ __('COMUNIDAD') }}
                        </a>
                        @if($navShowResearch ?? false)
                            <span class="px-2 py-0.5 text-theme-muted cursor-not-allowed relative group" title="{{ __('Próximamente') }}">
                                {{ __('INVESTIGACIÓN') }}
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                    {{ __('Próximamente') }}
                                </span>
                            </span>
                        @endif
                        @if($navShowHelp ?? false)
                            <a href="{{ route('help') }}" class="px-2 py-0.5 {{ request()->routeIs('help') ? 'text-[#EC1C24] font-semibold' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                                {{ __('¿CÓMO USAR MI FAMILIA?') }}
                            </a>
                        @endif
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.index') }}" class="px-2 py-0.5 {{ request()->routeIs('admin.*') ? 'text-[#EC1C24] font-semibold underline' : 'text-[#8896C9] hover:text-[#EC1C24]' }}">
                                {{ __('ADMIN') }}
                            </a>
                        @endif
                    </nav>
                @endauth
            </div>

            <!-- Botón hamburguesa (SOLO móvil/tablet, SOLO usuarios autenticados) -->
            @auth
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="mobile-hamburger p-2 rounded-md text-theme-secondary hover:text-[#3b82f6] hover:bg-theme-secondary focus:outline-none focus:ring-2 focus:ring-[#3b82f6]"
                    :aria-expanded="mobileMenuOpen">
                <span class="sr-only">{{ __('Abrir menu') }}</span>
                <!-- Icono hamburguesa -->
                <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <!-- Icono X (cerrar) -->
                <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            @endauth

            <!-- Idioma y login para invitados en móvil/tablet -->
            @guest
            <div class="mobile-hamburger flex items-center gap-2 text-xs">
                <nav class="flex items-center gap-1">
                    <a href="?lang=es" class="{{ app()->getLocale() == 'es' ? 'text-red-600 font-semibold' : 'text-theme-muted hover:text-red-600' }}">ES</a>
                    <span class="text-theme-muted">|</span>
                    <a href="?lang=en" class="{{ app()->getLocale() == 'en' ? 'text-red-600 font-semibold' : 'text-theme-muted hover:text-red-600' }}">EN</a>
                </nav>
                <span class="text-theme-muted">|</span>
                <a href="{{ route('login') }}" class="text-theme-muted hover:text-[#3b82f6] font-medium">
                    {{ __('INGRESAR') }}
                </a>
            </div>
            @endguest
        </div>
    </div>

    <!-- Menú móvil/tablet desplegable (SOLO usuarios autenticados) -->
    @auth
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="mobile-menu border-theme bg-theme-secondary"
         x-cloak>
        <div class="px-4 py-3 space-y-1">
            <!-- Navegación principal -->
            <a href="{{ route('tree.index') }}"
               class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('tree.*') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                {{ __('Árbol') }}
            </a>

            <a href="{{ route('messages.inbox') }}"
               class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('messages.*') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ __('Mensajes') }}
                @php $unreadCount = auth()->user()->unreadMessages()->count(); @endphp
                @if($unreadCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </a>

            @if(auth()->user()->person)
                <a href="{{ route('persons.show', auth()->user()->person) }}"
                   class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('profile.*') || (request()->routeIs('persons.show') && request()->route('person')?->id == auth()->user()->person_id) ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ __('Perfil') }}
                </a>
            @else
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('profile.*') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ __('Perfil') }}
                </a>
            @endif

            <a href="{{ route('search.index') }}"
               class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('search.*') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ __('Búsqueda') }}
            </a>

            @php
                $mobileIsOwnProfile = request()->routeIs('persons.show') && request()->route('person')?->id == auth()->user()->person_id;
                $mobileIsCommunityActive = request()->routeIs('persons.*') && !$mobileIsOwnProfile;
            @endphp
            <a href="{{ route('persons.index') }}"
               class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ $mobileIsCommunityActive ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Comunidad') }}
            </a>

            @if($navShowResearch ?? false)
                <span class="flex items-center px-3 py-2 rounded-md text-sm font-medium text-theme-muted cursor-not-allowed">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Investigación') }}
                    <span class="ml-2 text-xs text-theme-muted">({{ __('Próximamente') }})</span>
                </span>
            @endif

            <!-- Separador -->
            <div class="border-t border-theme my-2"></div>

            <!-- Selector de idioma -->
            <div x-data="{ langOpen: false }" class="relative">
                <button @click="langOpen = !langOpen"
                        class="flex items-center justify-between w-full px-3 py-2 rounded-md text-sm font-medium text-theme-secondary hover:bg-theme">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        {{ __('Idioma') }}
                        <span class="ml-2 text-xs text-theme-muted">
                            ({{ app()->getLocale() == 'es' ? 'Español' : 'English' }})
                        </span>
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': langOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="langOpen" x-transition class="pl-8 mt-1 space-y-1">
                    <a href="?lang=es" class="block px-3 py-2 rounded-md text-sm {{ app()->getLocale() == 'es' ? 'bg-red-100 text-red-700 font-semibold' : 'text-theme-secondary hover:bg-theme' }}">
                        Español
                    </a>
                    <a href="?lang=en" class="block px-3 py-2 rounded-md text-sm {{ app()->getLocale() == 'en' ? 'bg-red-100 text-red-700 font-semibold' : 'text-theme-secondary hover:bg-theme' }}">
                        English
                    </a>
                </div>
            </div>

            <!-- Separador -->
            <div class="border-t border-theme my-2"></div>

            <!-- Ayuda -->
            @if($navShowHelp ?? false)
                <a href="{{ route('help') }}"
                   class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('help') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('¿Cómo usar Mi Familia?') }}
                </a>
            @endif

            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.index') }}"
                   class="flex items-center px-3 py-2 rounded-md text-[13px] font-medium {{ request()->routeIs('admin.*') ? 'text-[#EC1C24] bg-red-50' : 'text-[#8896C9] hover:text-[#EC1C24] hover:bg-theme' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('Admin') }}
                </a>
            @endif

            <!-- Salir -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center w-full px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    {{ __('Salir') }}
                </button>
            </form>
        </div>
    </div>
    @endauth
</header>

<style>
    [x-cloak] { display: none !important; }
</style>
