<x-app-layout>
    <x-slot name="title">{{ $message->subject }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('messages.inbox') }}" class="text-gray-500 hover:text-gray-700">{{ __('Mensajes') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium truncate max-w-xs ml-1 md:ml-2">{{ Str::limit($message->subject, 30) }}</span>
                </li>
            </ol>
        </nav>

        <div class="card">
            <!-- Header -->
            <div class="card-header border-b">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        @if($message->isSystemMessage())
                            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @elseif($message->sender && $message->sender->photo)
                            <img src="{{ Storage::url($message->sender->photo) }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-mf-primary text-white flex items-center justify-center font-medium text-lg">
                                {{ $message->sender ? substr($message->sender->name, 0, 1) : '?' }}
                            </div>
                        @endif

                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ $message->subject }}</h1>
                            <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                @if($message->sender_id === Auth::id())
                                    <span>{{ __('Para:') }} <strong>{{ $message->recipient?->name ?? __('Usuario eliminado') }}</strong></span>
                                @else
                                    <span>{{ __('De:') }} <strong>{{ $message->sender?->name ?? __('Sistema') }}</strong></span>
                                @endif
                                <span class="text-gray-300">|</span>
                                <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($message->type !== 'message')
                            <span class="px-3 py-1 text-sm rounded-full
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
                    </div>
                </div>
            </div>

            <!-- Persona relacionada -->
            @if($message->relatedPerson)
                <div class="px-6 py-3 bg-gray-50 border-b flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm text-gray-600">{{ __('Relacionado con:') }}</span>
                    <a href="{{ route('persons.show', $message->relatedPerson) }}" class="text-sm text-mf-primary hover:underline font-medium">
                        {{ $message->relatedPerson->full_name }}
                    </a>
                </div>
            @endif

            <!-- Cuerpo del mensaje -->
            <div class="card-body">
                <div class="prose max-w-none">
                    {!! nl2br(e($message->body)) !!}
                </div>
            </div>

            <!-- Accion requerida -->
            @if($message->action_required && $message->recipient_id === Auth::id())
                <div class="px-6 py-4 bg-yellow-50 border-t">
                    @if($message->action_status === 'pending')
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="text-yellow-800 font-medium">{{ __('Esta solicitud requiere tu respuesta') }}</span>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('messages.deny', $message) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-outline text-red-600 border-red-300 hover:bg-red-50"
                                            onclick="return confirm('{{ __('Estas seguro de denegar esta solicitud?') }}')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ __('Denegar') }}
                                    </button>
                                </form>
                                <form action="{{ route('messages.accept', $message) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ __('Aceptar') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            @if($message->action_status === 'accepted')
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-green-700">{{ __('Aceptado el :date', ['date' => $message->action_taken_at->format('d/m/Y H:i')]) }}</span>
                            @else
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="text-red-700">{{ __('Denegado el :date', ['date' => $message->action_taken_at->format('d/m/Y H:i')]) }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Acciones -->
            <div class="card-body border-t flex flex-wrap gap-2">
                @if($message->recipient_id === Auth::id() && $message->sender_id)
                    <a href="{{ route('messages.reply', $message) }}" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        {{ __('Responder') }}
                    </a>
                @endif

                @if($message->recipient_id === Auth::id())
                    <form action="{{ route('messages.toggleRead', $message) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-outline">
                            @if($message->isRead())
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/>
                                </svg>
                                {{ __('Marcar no leido') }}
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ __('Marcar leido') }}
                            @endif
                        </button>
                    </form>
                @endif

                <form action="{{ route('messages.destroy', $message) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline text-red-600 border-red-300 hover:bg-red-50"
                            onclick="return confirm('{{ __('Eliminar este mensaje?') }}')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('Eliminar') }}
                    </button>
                </form>

                <div class="flex-1"></div>

                <a href="{{ route('messages.inbox') }}" class="btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
