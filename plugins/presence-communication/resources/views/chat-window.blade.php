<x-app-layout>
    <x-slot name="title">{{ __('Chat') }} - {{ config('app.name') }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('Chat') }}</h1>

        <div class="grid lg:grid-cols-3 gap-6" x-data="chatApp()" x-init="init()">
            {{-- Lista de conversaciones --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="text-lg font-semibold text-theme mb-4">{{ __('Conversaciones') }}</h2>

                    {{-- Familia en linea --}}
                    <div class="mb-4" x-show="familyUsers.length > 0">
                        <h3 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                            {{ __('Familia') }}
                            <span class="text-xs font-normal" x-text="'(' + familyUsers.length + ')'"></span>
                        </h3>
                        <div class="space-y-1">
                            <template x-for="user in familyUsers" :key="'fam-' + user.id">
                                <button @click="selectUser(user.id, user.name, user.photo)"
                                        class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-theme-secondary/20 transition-colors text-left"
                                        :class="{ 'bg-mf-primary/10': selectedUserId === user.id }">
                                    <div class="relative">
                                        <template x-if="user.photo">
                                            <img :src="user.photo" class="w-8 h-8 rounded-full object-cover">
                                        </template>
                                        <template x-if="!user.photo">
                                            <div class="w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold"
                                                 x-text="user.name.charAt(0).toUpperCase()">
                                            </div>
                                        </template>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                    </div>
                                    <span class="text-sm text-theme" x-text="user.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Comunidad en linea --}}
                    <div class="mb-4" x-show="communityUsers.length > 0">
                        <h3 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                            {{ __('Comunidad') }}
                            <span class="text-xs font-normal" x-text="'(' + communityUsers.length + ')'"></span>
                        </h3>
                        <div class="space-y-1">
                            <template x-for="user in communityUsers" :key="'com-' + user.id">
                                <button @click="selectUser(user.id, user.name, user.photo)"
                                        class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-theme-secondary/20 transition-colors text-left"
                                        :class="{ 'bg-mf-primary/10': selectedUserId === user.id }">
                                    <div class="relative">
                                        <template x-if="user.photo">
                                            <img :src="user.photo" class="w-8 h-8 rounded-full object-cover">
                                        </template>
                                        <template x-if="!user.photo">
                                            <div class="w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-600 text-white flex items-center justify-center text-sm font-bold"
                                                 x-text="user.name.charAt(0).toUpperCase()">
                                            </div>
                                        </template>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                    </div>
                                    <span class="text-sm text-theme" x-text="user.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Publico --}}
                    <div class="mb-4" x-show="publicUsers.length > 0">
                        <h3 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                            {{ __('Publico') }}
                            <span class="text-xs font-normal" x-text="'(' + publicUsers.length + ')'"></span>
                        </h3>
                        <div class="space-y-1">
                            <template x-for="user in publicUsers" :key="'pub-' + user.id">
                                <button @click="selectUser(user.id, user.name, user.photo)"
                                        class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-theme-secondary/20 transition-colors text-left"
                                        :class="{ 'bg-mf-primary/10': selectedUserId === user.id }">
                                    <div class="relative">
                                        <template x-if="user.photo">
                                            <img :src="user.photo" class="w-8 h-8 rounded-full object-cover">
                                        </template>
                                        <template x-if="!user.photo">
                                            <div class="w-8 h-8 rounded-full bg-blue-400 dark:bg-blue-600 text-white flex items-center justify-center text-sm font-bold"
                                                 x-text="user.name.charAt(0).toUpperCase()">
                                            </div>
                                        </template>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                    </div>
                                    <span class="text-sm text-theme" x-text="user.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Conversaciones recientes --}}
                    <h3 class="text-sm font-medium text-theme-secondary mb-2">{{ __('Recientes') }}</h3>
                    <div x-show="loadingConversations" class="text-center py-4">
                        <svg class="animate-spin h-5 w-5 mx-auto text-theme-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <template x-for="conv in conversations" :key="'conv-' + conv.user_id">
                            <button @click="selectUser(conv.user_id, conv.name, conv.photo)"
                                    class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-theme-secondary/20 transition-colors text-left"
                                    :class="{ 'bg-mf-primary/10': selectedUserId === conv.user_id }">
                                <template x-if="conv.photo">
                                    <img :src="conv.photo" class="w-8 h-8 rounded-full object-cover">
                                </template>
                                <template x-if="!conv.photo">
                                    <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center text-sm font-bold"
                                         x-text="conv.name.charAt(0).toUpperCase()">
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-theme" x-text="conv.name"></span>
                                        <span x-show="conv.unread_count > 0"
                                              class="badge badge-primary text-xs"
                                              x-text="conv.unread_count"></span>
                                    </div>
                                    <p class="text-xs text-theme-secondary truncate" x-show="conv.last_message"
                                       x-text="conv.last_message ? (conv.last_message.is_mine ? '{{ __('Tu') }}: ' : '') + conv.last_message.message : ''">
                                    </p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Area de mensajes --}}
            <div class="lg:col-span-2 card">
                <div class="card-body flex flex-col" style="min-height: 500px;">
                    {{-- Header del chat --}}
                    <div x-show="selectedUserId" class="flex items-center justify-between pb-4 border-b border-theme">
                        <div class="flex items-center gap-3">
                            <template x-if="selectedUserPhoto">
                                <img :src="selectedUserPhoto" class="w-10 h-10 rounded-full object-cover">
                            </template>
                            <template x-if="!selectedUserPhoto">
                                <div class="w-10 h-10 rounded-full bg-mf-primary text-white flex items-center justify-center text-lg font-bold"
                                     x-text="selectedUserName ? selectedUserName.charAt(0).toUpperCase() : ''">
                                </div>
                            </template>
                            <div>
                                <h3 class="font-semibold text-theme" x-text="selectedUserName"></h3>
                                <span class="text-xs text-green-500" x-show="isUserOnline(selectedUserId)">{{ __('En linea') }}</span>
                            </div>
                        </div>
                        {{-- Botones de llamada --}}
                        <div class="flex items-center gap-2" x-show="isUserOnline(selectedUserId)">
                            <button @click="initiateCall('voice')"
                                    class="p-2 rounded-full hover:bg-green-100 dark:hover:bg-green-900/30 text-green-600 transition-colors"
                                    title="{{ __('Llamada de voz') }}"
                                    :disabled="callState !== 'idle'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                            <button @click="initiateCall('video')"
                                    class="p-2 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-600 transition-colors"
                                    title="{{ __('Videollamada') }}"
                                    :disabled="callState !== 'idle'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Seleccionar conversacion --}}
                    <div x-show="!selectedUserId" class="flex-1 flex items-center justify-center">
                        <div class="text-center text-theme-secondary">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p>{{ __('Selecciona una conversacion para empezar') }}</p>
                        </div>
                    </div>

                    {{-- Mensajes --}}
                    <div x-show="selectedUserId" x-ref="messagesContainer"
                         class="flex-1 overflow-y-auto py-4 space-y-3" style="max-height: 400px;">
                        <div x-show="loadingMessages" class="text-center py-4">
                            <svg class="animate-spin h-5 w-5 mx-auto text-theme-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>

                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                                     :class="msg.is_mine
                                         ? 'bg-mf-primary text-white rounded-br-none'
                                         : 'bg-theme-secondary/20 text-theme rounded-bl-none'">
                                    <p class="text-sm" x-text="msg.message"></p>
                                    <p class="text-xs mt-1 opacity-70"
                                       x-text="formatTime(msg.created_at)"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Input de mensaje --}}
                    <div x-show="selectedUserId" class="pt-4 border-t border-theme">
                        <form @submit.prevent="sendMessage()" class="flex gap-2">
                            <input type="text"
                                   x-model="newMessage"
                                   :placeholder="'{{ __('Escribe un mensaje...') }}'"
                                   class="input-field flex-1"
                                   maxlength="2000"
                                   autocomplete="off">
                            <button type="submit"
                                    class="btn-primary px-4"
                                    :disabled="!newMessage.trim() || sending">
                                <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg x-show="sending" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Overlay: Llamada entrante --}}
            <template x-teleport="body">
                <div x-show="callState === 'incoming'" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                        <div class="mb-4">
                            <div class="w-24 h-24 mx-auto rounded-full overflow-hidden animate-pulse ring-4 ring-green-400">
                                <template x-if="callPeerPhoto">
                                    <img :src="callPeerPhoto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!callPeerPhoto">
                                    <div class="w-full h-full bg-mf-primary text-white flex items-center justify-center text-3xl font-bold"
                                         x-text="callPeerName ? callPeerName.charAt(0).toUpperCase() : '?'"></div>
                                </template>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-theme mb-1" x-text="callPeerName"></h3>
                        <p class="text-theme-secondary mb-6">
                            <span x-show="callType === 'video'">{{ __('Videollamada entrante...') }}</span>
                            <span x-show="callType === 'voice'">{{ __('Llamada de voz entrante...') }}</span>
                        </p>
                        <div class="flex justify-center gap-6">
                            <button @click="rejectCall()"
                                    class="w-14 h-14 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-lg transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <button @click="acceptCall()"
                                    class="w-14 h-14 rounded-full bg-green-500 hover:bg-green-600 text-white flex items-center justify-center shadow-lg animate-bounce transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Overlay: Llamando (saliente) --}}
            <template x-teleport="body">
                <div x-show="callState === 'calling'" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                        <div class="mb-4">
                            <div class="w-24 h-24 mx-auto rounded-full overflow-hidden ring-4 ring-blue-400 animate-pulse">
                                <template x-if="callPeerPhoto">
                                    <img :src="callPeerPhoto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!callPeerPhoto">
                                    <div class="w-full h-full bg-mf-primary text-white flex items-center justify-center text-3xl font-bold"
                                         x-text="callPeerName ? callPeerName.charAt(0).toUpperCase() : '?'"></div>
                                </template>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-theme mb-1" x-text="callPeerName"></h3>
                        <p class="text-theme-secondary mb-6">{{ __('Llamando...') }}</p>
                        <button @click="endCall()"
                                class="w-14 h-14 mx-auto rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-lg transition-colors">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            {{-- Overlay: Llamada activa --}}
            <template x-teleport="body">
                <div x-show="callState === 'active'" x-cloak
                     class="fixed inset-0 z-50 flex flex-col bg-gray-900">
                    {{-- Video remoto (fullscreen) --}}
                    <div class="flex-1 relative" x-show="callType === 'video'">
                        <video x-ref="remoteVideo" autoplay playsinline
                               class="w-full h-full object-cover"></video>
                        {{-- Video local (mini esquina) --}}
                        <div class="absolute top-4 right-4 w-32 h-24 rounded-lg overflow-hidden shadow-lg border-2 border-white/20">
                            <video x-ref="localVideo" autoplay playsinline muted
                                   class="w-full h-full object-cover"
                                   :class="isCameraOff ? 'hidden' : ''"></video>
                            <div x-show="isCameraOff" class="w-full h-full bg-gray-700 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Solo voz: avatar grande --}}
                    <div class="flex-1 flex items-center justify-center" x-show="callType === 'voice'">
                        <div class="text-center">
                            <div class="w-32 h-32 mx-auto rounded-full overflow-hidden ring-4 ring-green-400 mb-4">
                                <template x-if="callPeerPhoto">
                                    <img :src="callPeerPhoto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!callPeerPhoto">
                                    <div class="w-full h-full bg-mf-primary text-white flex items-center justify-center text-5xl font-bold"
                                         x-text="callPeerName ? callPeerName.charAt(0).toUpperCase() : '?'"></div>
                                </template>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-1" x-text="callPeerName"></h3>
                            <p class="text-gray-300 text-lg" x-text="formatDuration(callDuration)"></p>
                        </div>
                    </div>

                    {{-- Controles de llamada --}}
                    <div class="bg-gray-800/90 px-6 py-4">
                        {{-- Timer para video --}}
                        <p class="text-center text-gray-300 text-sm mb-3" x-show="callType === 'video'" x-text="formatDuration(callDuration)"></p>
                        <div class="flex items-center justify-center gap-4">
                            <button @click="toggleMute()"
                                    class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                                    :class="isMuted ? 'bg-red-500 text-white' : 'bg-gray-600 text-white hover:bg-gray-500'">
                                <svg x-show="!isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                <svg x-show="isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                </svg>
                            </button>
                            <button x-show="callType === 'video'" @click="toggleCamera()"
                                    class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                                    :class="isCameraOff ? 'bg-red-500 text-white' : 'bg-gray-600 text-white hover:bg-gray-500'">
                                <svg x-show="!isCameraOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <svg x-show="isCameraOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </button>
                            <button @click="endCall()"
                                    class="w-14 h-14 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-lg transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.516l2.257-1.13a1 1 0 00.502-1.21L8.228 3.684A1 1 0 007.28 3H5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
    <script>
    function chatApp() {
        return {
            // Chat
            conversations: [],
            onlineUsers: [],
            familyUsers: [],
            communityUsers: [],
            publicUsers: [],
            messages: [],
            selectedUserId: null,
            selectedUserName: '',
            selectedUserPhoto: null,
            newMessage: '',
            sending: false,
            loadingConversations: true,
            loadingMessages: false,
            pollInterval: null,
            messagePollInterval: null,

            // WebRTC
            callState: 'idle', // idle, calling, incoming, active
            callType: 'video', // voice, video
            callPeerId: null,
            callPeerName: '',
            callPeerPhoto: null,
            callDuration: 0,
            callDurationInterval: null,
            callTimeoutHandle: null,
            peerConnection: null,
            localStream: null,
            remoteStream: null,
            isMuted: false,
            isCameraOff: false,
            signalPollInterval: null,
            _ringtoneOscillators: [],

            init() {
                this.fetchOnline();
                this.fetchConversations();

                // Poll para usuarios en linea cada 30s
                this.pollInterval = setInterval(() => this.fetchOnline(), 30000);

                // Heartbeat cada 30s
                this.heartbeat();
                setInterval(() => this.heartbeat(), 30000);

                // Poll de senales WebRTC cada 3s
                this.startSignalPolling();

                // Escuchar evento de abrir chat desde widget
                window.addEventListener('open-chat', (e) => {
                    this.selectUser(e.detail.userId, e.detail.userName, e.detail.userPhoto || null);
                });

                // Check URL para llamada entrante redirect
                const params = new URLSearchParams(window.location.search);
                const incomingCall = params.get('incoming_call');
                if (incomingCall) {
                    // Limpiar URL
                    window.history.replaceState({}, '', window.location.pathname);
                }

                // Enviar offline al cerrar pagina
                window.addEventListener('beforeunload', () => {
                    if (this.callState !== 'idle') {
                        this.endCall();
                    }
                    navigator.sendBeacon('{{ route("presence.offline") }}', new URLSearchParams({
                        _token: '{{ csrf_token() }}'
                    }));
                });
            },

            // ===================== CHAT =====================

            async heartbeat() {
                try {
                    await fetch('{{ route("presence.heartbeat") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ page: window.location.pathname }),
                    });
                } catch (e) {}
            },

            async fetchOnline() {
                try {
                    const response = await fetch('{{ route("presence.online") }}');
                    const data = await response.json();
                    this.familyUsers = data.family || [];
                    this.communityUsers = data.community || [];
                    this.publicUsers = data.public || [];
                    this.onlineUsers = [...this.familyUsers, ...this.communityUsers, ...this.publicUsers];
                } catch (e) {}
            },

            async fetchConversations() {
                try {
                    const response = await fetch('{{ route("chat.conversations") }}');
                    const data = await response.json();
                    this.conversations = data.conversations || [];
                } catch (e) {
                    console.error('Error fetching conversations:', e);
                } finally {
                    this.loadingConversations = false;
                }
            },

            async selectUser(userId, userName, photo) {
                this.selectedUserId = userId;
                this.selectedUserName = userName;
                this.selectedUserPhoto = photo || null;
                this.messages = [];
                this.loadingMessages = true;

                // Limpiar poll anterior
                if (this.messagePollInterval) {
                    clearInterval(this.messagePollInterval);
                }

                await this.fetchMessages();

                // Poll para nuevos mensajes cada 5s
                this.messagePollInterval = setInterval(() => this.fetchMessages(), 5000);
            },

            async fetchMessages() {
                try {
                    const response = await fetch(`/chat/messages/${this.selectedUserId}`);
                    const data = await response.json();
                    const oldCount = this.messages.length;
                    const oldLastId = oldCount > 0 ? this.messages[oldCount - 1].id : 0;
                    this.messages = data.messages || [];

                    // Detectar mensajes nuevos entrantes y sonar
                    if (!this.loadingMessages && this.messages.length > oldCount) {
                        const hasNewIncoming = this.messages.some(m => m.id > oldLastId && !m.is_mine);
                        if (hasNewIncoming && typeof window.ChatNotificationSound !== 'undefined') {
                            window.ChatNotificationSound.play();
                        }
                    }

                    // Scroll al final si hay nuevos mensajes
                    if (this.messages.length > oldCount || this.loadingMessages) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                } catch (e) {
                    console.error('Error fetching messages:', e);
                } finally {
                    this.loadingMessages = false;
                }
            },

            async sendMessage() {
                if (!this.newMessage.trim() || this.sending) return;

                this.sending = true;
                try {
                    const response = await fetch('{{ route("chat.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            recipient_id: this.selectedUserId,
                            message: this.newMessage.trim(),
                        }),
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.messages.push(data.message);
                        this.newMessage = '';
                        this.$nextTick(() => this.scrollToBottom());
                        this.fetchConversations();
                    }
                } catch (e) {
                    console.error('Error sending message:', e);
                } finally {
                    this.sending = false;
                }
            },

            scrollToBottom() {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },

            isUserOnline(userId) {
                return this.onlineUsers.some(u => u.id === userId);
            },

            formatTime(isoString) {
                const date = new Date(isoString);
                const now = new Date();
                const isToday = date.toDateString() === now.toDateString();

                if (isToday) {
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' +
                       date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            // ===================== WEBRTC =====================

            getUserInfo(userId) {
                const all = [...this.familyUsers, ...this.communityUsers, ...this.publicUsers];
                return all.find(u => u.id === userId) || null;
            },

            async initiateCall(mediaType) {
                if (this.callState !== 'idle' || !this.selectedUserId) return;

                this.callType = mediaType;
                this.callPeerId = this.selectedUserId;
                this.callPeerName = this.selectedUserName;
                this.callPeerPhoto = this.selectedUserPhoto;
                this.callState = 'calling';

                try {
                    const res = await fetch('{{ route("call.initiate") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ callee_id: this.callPeerId, media_type: mediaType }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.error || '{{ __("Error al iniciar llamada") }}');
                        this.resetCall();
                        return;
                    }

                    this.playRingtone('outgoing');

                    // Timeout 30s
                    this.callTimeoutHandle = setTimeout(() => {
                        if (this.callState === 'calling') {
                            this.endCall();
                        }
                    }, 30000);

                } catch (e) {
                    console.error('Error initiating call:', e);
                    this.resetCall();
                }
            },

            async acceptCall() {
                if (this.callState !== 'incoming') return;

                this.stopRingtone();

                try {
                    await fetch('{{ route("call.respond") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ caller_id: this.callPeerId, response: 'accept' }),
                    });

                    // Obtener media y esperar offer del caller
                    await this.getLocalMedia();

                } catch (e) {
                    console.error('Error accepting call:', e);
                    this.resetCall();
                }
            },

            async rejectCall() {
                if (this.callState !== 'incoming') return;

                this.stopRingtone();

                try {
                    await fetch('{{ route("call.respond") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ caller_id: this.callPeerId, response: 'reject' }),
                    });
                } catch (e) {}

                this.resetCall();
            },

            async endCall() {
                this.stopRingtone();

                if (this.callPeerId) {
                    try {
                        await fetch('{{ route("call.end") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ peer_id: this.callPeerId }),
                        });
                    } catch (e) {}
                }

                this.cleanupWebRTC();
                this.resetCall();
            },

            async getLocalMedia() {
                const constraints = {
                    audio: true,
                    video: this.callType === 'video' ? { width: { ideal: 640 }, height: { ideal: 480 } } : false,
                };

                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia(constraints);

                    if (this.callType === 'video' && this.$refs.localVideo) {
                        this.$refs.localVideo.srcObject = this.localStream;
                    }
                } catch (e) {
                    console.error('Error getting user media:', e);
                    alert('{{ __("No se pudo acceder a la camara/microfono. Verifica los permisos.") }}');
                    this.endCall();
                }
            },

            async setupWebRTC(isCaller) {
                const config = {
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                    ]
                };

                this.peerConnection = new RTCPeerConnection(config);

                // Agregar tracks locales
                if (this.localStream) {
                    this.localStream.getTracks().forEach(track => {
                        this.peerConnection.addTrack(track, this.localStream);
                    });
                }

                // Recibir tracks remotos
                this.remoteStream = new MediaStream();
                this.peerConnection.ontrack = (event) => {
                    event.streams[0].getTracks().forEach(track => {
                        this.remoteStream.addTrack(track);
                    });
                    if (this.callType === 'video' && this.$refs.remoteVideo) {
                        this.$refs.remoteVideo.srcObject = this.remoteStream;
                    }
                };

                // ICE candidates
                this.peerConnection.onicecandidate = async (event) => {
                    if (event.candidate) {
                        await this.sendSignal('ice-candidate', JSON.stringify(event.candidate));
                    }
                };

                // Estado de conexion
                this.peerConnection.onconnectionstatechange = () => {
                    const state = this.peerConnection?.connectionState;
                    if (state === 'connected') {
                        this.callState = 'active';
                        this.stopRingtone();
                        this.startCallTimer();
                    } else if (state === 'disconnected' || state === 'failed' || state === 'closed') {
                        this.cleanupWebRTC();
                        this.resetCall();
                    }
                };

                if (isCaller) {
                    const offer = await this.peerConnection.createOffer();
                    await this.peerConnection.setLocalDescription(offer);
                    await this.sendSignal('offer', JSON.stringify(offer));
                }
            },

            async processSignal(signal) {
                if (!this.peerConnection && (signal.type === 'offer' || signal.type === 'answer')) {
                    // Si no hay peerConnection y recibimos offer, crear uno
                    if (signal.type === 'offer') {
                        await this.setupWebRTC(false);
                    }
                }

                if (!this.peerConnection) return;

                try {
                    if (signal.type === 'offer') {
                        await this.peerConnection.setRemoteDescription(JSON.parse(signal.payload));
                        const answer = await this.peerConnection.createAnswer();
                        await this.peerConnection.setLocalDescription(answer);
                        await this.sendSignal('answer', JSON.stringify(answer));
                    } else if (signal.type === 'answer') {
                        await this.peerConnection.setRemoteDescription(JSON.parse(signal.payload));
                    } else if (signal.type === 'ice-candidate') {
                        await this.peerConnection.addIceCandidate(JSON.parse(signal.payload));
                    }
                } catch (e) {
                    console.error('Error processing signal:', e);
                }
            },

            async sendSignal(type, payload) {
                try {
                    await fetch('{{ route("call.signal") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            peer_id: this.callPeerId,
                            type: type,
                            payload: payload,
                        }),
                    });
                } catch (e) {
                    console.error('Error sending signal:', e);
                }
            },

            startSignalPolling() {
                this.signalPollInterval = setInterval(() => this.pollSignals(), 3000);
            },

            async pollSignals() {
                try {
                    const res = await fetch('{{ route("call.poll") }}');
                    const data = await res.json();
                    const signals = data.signals || [];

                    for (const signal of signals) {
                        await this.handleSignal(signal);
                    }
                } catch (e) {}
            },

            async handleSignal(signal) {
                const currentUserId = {{ Auth::id() }};

                switch (signal.type) {
                    case 'call-request':
                        if (signal.callee_id === currentUserId && this.callState === 'idle') {
                            this.callState = 'incoming';
                            this.callType = signal.media_type;
                            this.callPeerId = signal.caller_id;
                            this.callPeerName = signal.caller_name || '{{ __("Usuario") }}';
                            // Buscar foto del caller
                            const callerInfo = this.getUserInfo(signal.caller_id);
                            this.callPeerPhoto = callerInfo ? callerInfo.photo : null;
                            this.playRingtone('incoming');
                        }
                        break;

                    case 'call-accept':
                        if (this.callState === 'calling') {
                            this.stopRingtone();
                            if (this.callTimeoutHandle) {
                                clearTimeout(this.callTimeoutHandle);
                                this.callTimeoutHandle = null;
                            }
                            // Caller: obtener media, crear peer connection, enviar offer
                            await this.getLocalMedia();
                            await this.setupWebRTC(true);
                        }
                        break;

                    case 'call-reject':
                        if (this.callState === 'calling') {
                            this.stopRingtone();
                            if (this.callTimeoutHandle) {
                                clearTimeout(this.callTimeoutHandle);
                            }
                            this.resetCall();
                        }
                        break;

                    case 'call-end':
                        if (this.callState !== 'idle') {
                            this.stopRingtone();
                            this.cleanupWebRTC();
                            this.resetCall();
                        }
                        break;

                    case 'offer':
                    case 'answer':
                    case 'ice-candidate':
                        if (this.callState !== 'idle') {
                            await this.processSignal(signal);
                        }
                        break;
                }
            },

            cleanupWebRTC() {
                if (this.localStream) {
                    this.localStream.getTracks().forEach(t => t.stop());
                    this.localStream = null;
                }
                if (this.peerConnection) {
                    this.peerConnection.close();
                    this.peerConnection = null;
                }
                this.remoteStream = null;

                if (this.$refs.localVideo) this.$refs.localVideo.srcObject = null;
                if (this.$refs.remoteVideo) this.$refs.remoteVideo.srcObject = null;
            },

            resetCall() {
                this.callState = 'idle';
                this.callPeerId = null;
                this.callPeerName = '';
                this.callPeerPhoto = null;
                this.callDuration = 0;
                this.isMuted = false;
                this.isCameraOff = false;

                if (this.callDurationInterval) {
                    clearInterval(this.callDurationInterval);
                    this.callDurationInterval = null;
                }
                if (this.callTimeoutHandle) {
                    clearTimeout(this.callTimeoutHandle);
                    this.callTimeoutHandle = null;
                }
            },

            startCallTimer() {
                this.callDuration = 0;
                this.callDurationInterval = setInterval(() => {
                    this.callDuration++;
                }, 1000);
            },

            formatDuration(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                return m + ':' + s;
            },

            toggleMute() {
                this.isMuted = !this.isMuted;
                if (this.localStream) {
                    this.localStream.getAudioTracks().forEach(t => { t.enabled = !this.isMuted; });
                }
            },

            toggleCamera() {
                this.isCameraOff = !this.isCameraOff;
                if (this.localStream) {
                    this.localStream.getVideoTracks().forEach(t => { t.enabled = !this.isCameraOff; });
                }
            },

            // ===================== RINGTONE (Web Audio API) =====================

            playRingtone(type) {
                this.stopRingtone();
                if (typeof window.ChatNotificationSound === 'undefined') return;

                const ctx = window.ChatNotificationSound.getContext();
                if (ctx.state === 'suspended') ctx.resume();

                const playTone = () => {
                    if (this.callState !== 'calling' && this.callState !== 'incoming') return;

                    try {
                        const t = ctx.currentTime;
                        const gain = ctx.createGain();
                        gain.gain.setValueAtTime(0.3, t);
                        gain.gain.exponentialRampToValueAtTime(0.01, t + 0.8);
                        gain.connect(ctx.destination);

                        if (type === 'incoming') {
                            // Tono entrante: 2 pulsos rapidos
                            const o1 = ctx.createOscillator();
                            o1.type = 'sine';
                            o1.frequency.setValueAtTime(880, t);
                            o1.connect(gain);
                            o1.start(t);
                            o1.stop(t + 0.15);
                            this._ringtoneOscillators.push(o1);

                            const g2 = ctx.createGain();
                            g2.gain.setValueAtTime(0.3, t + 0.2);
                            g2.gain.exponentialRampToValueAtTime(0.01, t + 0.8);
                            g2.connect(ctx.destination);

                            const o2 = ctx.createOscillator();
                            o2.type = 'sine';
                            o2.frequency.setValueAtTime(880, t + 0.2);
                            o2.connect(g2);
                            o2.start(t + 0.2);
                            o2.stop(t + 0.35);
                            this._ringtoneOscillators.push(o2);
                        } else {
                            // Tono saliente: tono largo suave
                            const o1 = ctx.createOscillator();
                            o1.type = 'sine';
                            o1.frequency.setValueAtTime(440, t);
                            o1.connect(gain);
                            o1.start(t);
                            o1.stop(t + 0.8);
                            this._ringtoneOscillators.push(o1);
                        }
                    } catch (e) {}
                };

                playTone();
                this._ringtoneInterval = setInterval(playTone, 2000);
            },

            stopRingtone() {
                if (this._ringtoneInterval) {
                    clearInterval(this._ringtoneInterval);
                    this._ringtoneInterval = null;
                }
                this._ringtoneOscillators.forEach(o => {
                    try { o.stop(); } catch (e) {}
                });
                this._ringtoneOscillators = [];
            },

            destroy() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                if (this.messagePollInterval) clearInterval(this.messagePollInterval);
                if (this.signalPollInterval) clearInterval(this.signalPollInterval);
                this.cleanupWebRTC();
                this.stopRingtone();
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
