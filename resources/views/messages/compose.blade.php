<x-app-layout>
    <x-slot name="title">{{ __('Nuevo mensaje') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="flex items-center">
                    <a href="{{ route('messages.inbox') }}" class="text-gray-500 hover:text-gray-700">{{ __('Mensajes') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700 font-medium ml-1 md:ml-2">{{ __('Nuevo mensaje') }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                @isset($originalMessage)
                    {{ __('Responder mensaje') }}
                @else
                    {{ __('Nuevo mensaje') }}
                @endisset
            </h1>
        </div>

        @isset($originalMessage)
            <div class="card mb-6 bg-gray-50">
                <div class="card-header">
                    <h3 class="text-sm font-medium text-gray-500">{{ __('Mensaje original') }}</h3>
                </div>
                <div class="card-body">
                    <div class="text-sm text-gray-600 mb-2">
                        <strong>{{ $originalMessage->sender->name }}</strong>
                        <span class="text-gray-400">{{ $originalMessage->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-gray-700 prose prose-sm max-w-none">
                        {!! nl2br(e($originalMessage->body)) !!}
                    </div>
                </div>
            </div>
        @endisset

        <form action="{{ route('messages.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="card">
                <div class="card-body space-y-4">
                    <div>
                        <label for="recipient_id" class="form-label">{{ __('Para') }} *</label>
                        <select name="recipient_id" id="recipient_id" required
                                class="form-input @error('recipient_id') border-red-500 @enderror">
                            <option value="">{{ __('Seleccionar destinatario') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ (old('recipient_id', $recipient?->id) == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                    @if($user->email)
                                        ({{ $user->email }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="form-label">{{ __('Asunto') }} *</label>
                        <input type="text" name="subject" id="subject" required
                               value="{{ old('subject', $replySubject ?? '') }}"
                               class="form-input @error('subject') border-red-500 @enderror"
                               placeholder="{{ __('Escribe el asunto del mensaje') }}">
                        @error('subject')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="body" class="form-label">{{ __('Mensaje') }} *</label>
                        <textarea name="body" id="body" rows="8" required
                                  class="form-input @error('body') border-red-500 @enderror"
                                  placeholder="{{ __('Escribe tu mensaje aqui...') }}">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="related_person_id" class="form-label">{{ __('Relacionar con persona') }}</label>
                        <select name="related_person_id" id="related_person_id" class="form-input">
                            <option value="">{{ __('Ninguna') }}</option>
                            @foreach($persons as $person)
                                <option value="{{ $person->id }}"
                                    {{ (old('related_person_id', $relatedPerson?->id) == $person->id) ? 'selected' : '' }}>
                                    {{ $person->full_name }}
                                    @if($person->birth_date)
                                        ({{ $person->birth_date->format('Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="form-help">{{ __('Opcional: relaciona este mensaje con una persona del arbol.') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('messages.inbox') }}" class="btn-outline">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    {{ __('Enviar') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
