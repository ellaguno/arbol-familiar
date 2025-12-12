<x-app-layout>
    <x-slot name="title">{{ __('Mensajes enviados') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Mensajes') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Mensajes enviados') }}</p>
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
                   class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium">
                    {{ __('Recibidos') }}
                </a>
                <a href="{{ route('messages.sent') }}"
                   class="border-b-2 border-mf-primary text-mf-primary py-4 px-1 text-sm font-medium">
                    {{ __('Enviados') }}
                </a>
            </nav>
        </div>

        @if($messages->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Sin mensajes enviados') }}</h3>
                    <p class="text-gray-500 mb-4">{{ __('Aun no has enviado ningun mensaje.') }}</p>
                    <a href="{{ route('messages.compose') }}" class="btn-primary">{{ __('Enviar mensaje') }}</a>
                </div>
            </div>
        @else
            <div class="card divide-y divide-gray-100">
                @foreach($messages as $message)
                    <a href="{{ route('messages.show', $message) }}"
                       class="block p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                @if($message->recipient && $message->recipient->photo)
                                    <img src="{{ Storage::url($message->recipient->photo) }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-medium">
                                        {{ $message->recipient ? substr($message->recipient->name, 0, 1) : '?' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm text-gray-500">{{ __('Para:') }}</span>
                                    <span class="font-medium text-gray-900">
                                        {{ $message->recipient ? $message->recipient->name : __('Usuario eliminado') }}
                                    </span>

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

                                <h3 class="text-gray-900 truncate">{{ $message->subject }}</h3>

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
