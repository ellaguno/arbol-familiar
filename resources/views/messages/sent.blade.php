<x-app-layout>
    <x-slot name="title">{{ __('Mensajes enviados') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-theme">{{ __('Mensajes') }}</h1>
                <p class="text-theme-secondary mt-1">{{ __('Mensajes enviados') }}</p>
            </div>
            <a href="{{ route('messages.compose') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Nuevo mensaje') }}
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-theme mb-6">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('messages.inbox') }}"
                   class="border-b-2 border-transparent text-theme-muted hover:text-theme-secondary hover:border-theme py-4 px-1 text-sm font-medium">
                    {{ __('Recibidos') }}
                </a>
                <a href="{{ route('messages.sent') }}"
                   class="border-b-2 border-mf-primary text-mf-primary py-4 px-1 text-sm font-medium">
                    {{ __('Enviados') }}
                </a>
            </nav>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-700 dark:text-red-300 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if($messages->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-theme-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <h3 class="text-lg font-medium text-theme mb-2">{{ __('Sin mensajes enviados') }}</h3>
                    <p class="text-theme-muted mb-4">{{ __('Aun no has enviado ningun mensaje.') }}</p>
                    <a href="{{ route('messages.compose') }}" class="btn-primary">{{ __('Enviar mensaje') }}</a>
                </div>
            </div>
        @else
            <div class="card divide-y divide-theme">
                @foreach($messages as $message)
                    <a href="{{ route('messages.show', $message) }}"
                       class="block p-4 hover:bg-theme-secondary transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                @if($message->isBroadcast())
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                        </svg>
                                    </div>
                                @elseif($message->recipient && $message->recipient->photo)
                                    <img src="{{ Storage::url($message->recipient->photo) }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center font-medium">
                                        {{ $message->recipient ? substr($message->recipient->name, 0, 1) : '?' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm text-theme-muted">{{ __('Para:') }}</span>
                                    @if($message->isBroadcast())
                                        <span class="font-medium text-theme">
                                            {{ $message->getBroadcastScopeLabel() }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                            {{ $message->recipient_count }} {{ __('destinatarios') }}
                                        </span>
                                    @else
                                        <span class="font-medium text-theme">
                                            {{ $message->recipient ? $message->recipient->name : __('Usuario eliminado') }}
                                        </span>
                                    @endif

                                    @if($message->action_required)
                                        <span class="px-2 py-0.5 text-xs rounded-full
                                            {{ $message->action_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $message->action_status === 'accepted' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $message->action_status === 'denied' ? 'bg-red-100 text-red-700' : '' }}">
                                            @switch($message->action_status)
                                                @case('pending')
                                                    {{ __('Pendiente') }}
                                                    @break
                                                @case('accepted')
                                                    {{ __('Aceptado') }}
                                                    @break
                                                @case('denied')
                                                    {{ __('Denegado') }}
                                                    @break
                                            @endswitch
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-theme truncate">{{ $message->subject }}</h3>

                                <p class="text-sm text-theme-muted truncate mt-1">
                                    {{ Str::limit(strip_tags($message->body), 100) }}
                                </p>

                                @if($message->relatedPerson)
                                    <p class="text-xs text-theme-muted mt-1">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $message->relatedPerson->full_name }}
                                    </p>
                                @endif
                            </div>

                            <!-- Fecha -->
                            <div class="flex-shrink-0 text-right">
                                <span class="text-sm text-theme-muted">
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
