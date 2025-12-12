<x-app-layout>
    <x-slot name="title">{{ __('Bandeja de entrada') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Mensajes') }}</h1>
                <p class="text-gray-600 mt-1">
                    @if($unreadCount > 0)
                        {{ __(':count sin leer', ['count' => $unreadCount]) }}
                    @else
                        {{ __('Todos leidos') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('messages.compose') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Nuevo mensaje') }}
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('messages.inbox') }}"
                   class="border-b-2 border-mf-primary text-mf-primary py-4 px-1 text-sm font-medium">
                    {{ __('Recibidos') }}
                    @if($unreadCount > 0)
                        <span class="ml-2 bg-mf-primary text-white text-xs px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    @endif
                </a>
                <a href="{{ route('messages.sent') }}"
                   class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium">
                    {{ __('Enviados') }}
                </a>
            </nav>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('messages.inbox') }}" method="GET" class="flex flex-wrap gap-4 items-center">
                    <div>
                        <select name="type" class="form-input text-sm">
                            <option value="">{{ __('Todos los tipos') }}</option>
                            <option value="message" {{ request('type') === 'message' ? 'selected' : '' }}>{{ __('Mensajes') }}</option>
                            <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>{{ __('Sistema') }}</option>
                            <option value="invitation" {{ request('type') === 'invitation' ? 'selected' : '' }}>{{ __('Invitaciones') }}</option>
                            <option value="consent_request" {{ request('type') === 'consent_request' ? 'selected' : '' }}>{{ __('Consentimientos') }}</option>
                        </select>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="unread" value="1" class="form-checkbox"
                               {{ request('unread') ? 'checked' : '' }}>
                        <span class="text-sm">{{ __('Solo no leidos') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="action_required" value="1" class="form-checkbox"
                               {{ request('action_required') ? 'checked' : '' }}>
                        <span class="text-sm">{{ __('Requieren accion') }}</span>
                    </label>

                    <button type="submit" class="btn-outline text-sm">{{ __('Filtrar') }}</button>
                    <a href="{{ route('messages.inbox') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Limpiar') }}</a>
                </form>
            </div>
        </div>

        @if($actionCount > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-yellow-800">
                    {{ __('Tienes :count solicitudes pendientes que requieren tu atencion.', ['count' => $actionCount]) }}
                </span>
            </div>
        @endif

        @if($messages->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Bandeja vacia') }}</h3>
                    <p class="text-gray-500">{{ __('No tienes mensajes en tu bandeja de entrada.') }}</p>
                </div>
            </div>
        @else
            <div class="flex justify-end mb-4">
                <form action="{{ route('messages.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-mf-primary hover:underline">
                        {{ __('Marcar todos como leidos') }}
                    </button>
                </form>
            </div>

            <div class="card divide-y divide-gray-100">
                @foreach($messages as $message)
                    <a href="{{ route('messages.show', $message) }}"
                       class="block p-4 hover:bg-gray-50 transition-colors {{ !$message->isRead() ? 'bg-blue-50' : '' }}">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                @if($message->isSystemMessage())
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @elseif($message->sender && $message->sender->photo)
                                    <img src="{{ Storage::url($message->sender->photo) }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-mf-primary text-white flex items-center justify-center font-medium">
                                        {{ $message->sender ? substr($message->sender->name, 0, 1) : '?' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    @if(!$message->isRead())
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    @endif
                                    <span class="font-medium text-gray-900 {{ !$message->isRead() ? 'font-semibold' : '' }}">
                                        {{ $message->sender ? $message->sender->name : __('Sistema') }}
                                    </span>

                                    @if($message->type !== 'message')
                                        <span class="px-2 py-0.5 text-xs rounded-full
                                            {{ $message->type === 'invitation' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $message->type === 'consent_request' ? 'bg-purple-100 text-purple-700' : '' }}
                                            {{ $message->type === 'system' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            @switch($message->type)
                                                @case('invitation')
                                                    {{ __('Invitacion') }}
                                                    @break
                                                @case('consent_request')
                                                    {{ __('Consentimiento') }}
                                                    @break
                                                @case('system')
                                                    {{ __('Sistema') }}
                                                    @break
                                            @endswitch
                                        </span>
                                    @endif

                                    @if($message->action_required && $message->action_status === 'pending')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                            {{ __('Accion requerida') }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-gray-900 truncate {{ !$message->isRead() ? 'font-semibold' : '' }}">
                                    {{ $message->subject }}
                                </h3>

                                <p class="text-sm text-gray-500 truncate mt-1">
                                    {{ Str::limit(strip_tags($message->body), 100) }}
                                </p>

                                @if($message->relatedPerson)
                                    <p class="text-xs text-gray-400 mt-1">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $message->relatedPerson->full_name }}
                                    </p>
                                @endif
                            </div>

                            <!-- Fecha -->
                            <div class="flex-shrink-0 text-right">
                                <span class="text-sm text-gray-500">
                                    {{ $message->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
