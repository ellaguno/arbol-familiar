{{-- Indicador de usuarios en linea + mensajes no leidos en el menu --}}
<div class="flex items-center gap-1"
     x-data="menuPresenceIndicator()"
     x-init="init()">
    <a href="{{ route('chat.index') }}"
       class="relative px-2 py-0.5 text-[#8896C9] hover:text-[#EC1C24] flex items-center gap-1"
       title="{{ __('Chat') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="text-xs font-bold" x-show="onlineCount > 0" x-cloak>
            <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
            <span x-text="onlineCount"></span>
        </span>
        {{-- Badge de mensajes no leidos --}}
        <span x-show="unreadCount > 0" x-cloak
              class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[16px] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full animate-pulse"
              x-text="unreadCount > 9 ? '9+' : unreadCount">
        </span>
    </a>

    {{-- Popup flotante de mensajes --}}
    <template x-teleport="body">
        <div x-show="showPopup" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-4 right-4 z-40 w-80 max-h-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
             @click.outside="showPopup = false">
            {{-- Header del popup --}}
            <div class="flex items-center justify-between px-4 py-3 bg-mf-primary text-white">
                <span class="font-semibold text-sm">{{ __('Mensajes nuevos') }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('chat.index') }}" class="text-white/80 hover:text-white text-xs underline">{{ __('Ver todos') }}</a>
                    <button @click="showPopup = false" class="text-white/80 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Lista de mensajes --}}
            <div class="max-h-72 overflow-y-auto">
                <template x-for="msg in popupMessages" :key="'pm-' + msg.id">
                    <a :href="'{{ route('chat.index') }}'" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors">
                        <div class="flex items-start gap-3">
                            <template x-if="msg.sender_photo">
                                <img :src="msg.sender_photo" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                            </template>
                            <template x-if="!msg.sender_photo">
                                <div class="w-8 h-8 rounded-full bg-mf-primary text-white flex items-center justify-center text-sm font-bold flex-shrink-0"
                                     x-text="msg.sender_name ? msg.sender_name.charAt(0).toUpperCase() : '?'">
                                </div>
                            </template>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-theme" x-text="msg.sender_name"></p>
                                <p class="text-xs text-theme-secondary truncate" x-text="msg.message"></p>
                                <p class="text-[10px] text-theme-muted mt-0.5" x-text="formatTimeAgo(msg.created_at)"></p>
                            </div>
                        </div>
                    </a>
                </template>
                <div x-show="popupMessages.length === 0" class="px-4 py-6 text-center text-theme-secondary text-sm">
                    {{ __('No hay mensajes nuevos') }}
                </div>
            </div>
        </div>
    </template>

    {{-- Overlay de llamada entrante (fuera de /chat) --}}
    <template x-teleport="body">
        <div x-show="incomingCallFrom" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="mb-4">
                    <div class="w-24 h-24 mx-auto rounded-full overflow-hidden animate-pulse ring-4 ring-green-400">
                        <template x-if="incomingCallPhoto">
                            <img :src="incomingCallPhoto" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!incomingCallPhoto">
                            <div class="w-full h-full bg-mf-primary text-white flex items-center justify-center text-3xl font-bold"
                                 x-text="incomingCallName ? incomingCallName.charAt(0).toUpperCase() : '?'"></div>
                        </template>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-theme mb-1" x-text="incomingCallName"></h3>
                <p class="text-theme-secondary mb-6">
                    <span x-show="incomingCallType === 'video'">{{ __('Videollamada entrante...') }}</span>
                    <span x-show="incomingCallType === 'voice'">{{ __('Llamada de voz entrante...') }}</span>
                </p>
                <div class="flex justify-center gap-6">
                    <button @click="rejectIncomingCall()"
                            class="w-14 h-14 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-lg transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <button @click="acceptIncomingCall()"
                            class="w-14 h-14 rounded-full bg-green-500 hover:bg-green-600 text-white flex items-center justify-center shadow-lg animate-bounce transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
/**
 * Utilidad global de sonido para notificaciones de chat.
 * Usa Web Audio API — cero archivos externos.
 */
window.ChatNotificationSound = {
    _audioCtx: null,
    _lastPlayed: 0,
    _unlocked: false,

    getContext() {
        if (!this._audioCtx && this._unlocked) {
            try {
                this._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {}
        }
        return this._audioCtx;
    },

    unlock() {
        if (this._unlocked) return;
        this._unlocked = true;
        const ctx = this.getContext();
        if (ctx && ctx.state === 'suspended') ctx.resume();
    },

    play() {
        if (!this._unlocked) return;
        const now = Date.now();
        if (now - this._lastPlayed < 1000) return;
        this._lastPlayed = now;

        try {
            const ctx = this.getContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();

            // Chime de dos tonos: C5 → E5, E5 → G5
            const t = ctx.currentTime;
            const gain = ctx.createGain();
            gain.gain.setValueAtTime(0.25, t);
            gain.gain.exponentialRampToValueAtTime(0.01, t + 0.5);
            gain.connect(ctx.destination);

            const osc1 = ctx.createOscillator();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(523.25, t);
            osc1.frequency.setValueAtTime(659.25, t + 0.12);
            osc1.connect(gain);
            osc1.start(t);
            osc1.stop(t + 0.5);

            const osc2 = ctx.createOscillator();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(659.25, t);
            osc2.frequency.setValueAtTime(783.99, t + 0.12);
            const gain2 = ctx.createGain();
            gain2.gain.setValueAtTime(0.15, t);
            gain2.gain.exponentialRampToValueAtTime(0.01, t + 0.5);
            gain2.connect(ctx.destination);
            osc2.connect(gain2);
            osc2.start(t);
            osc2.stop(t + 0.5);
        } catch (e) {
            // Audio no disponible
        }
    }
};

// Desbloquear AudioContext al primer gesto del usuario (click, touch, keydown)
['click', 'touchstart', 'keydown'].forEach(function(evt) {
    document.addEventListener(evt, function() {
        window.ChatNotificationSound.unlock();
    }, { once: true });
});

function menuPresenceIndicator() {
    return {
        onlineCount: 0,
        unreadCount: 0,
        _prevUnreadCount: -1,

        // Popup flotante
        showPopup: false,
        popupMessages: [],
        _popupDismissedIds: [],

        // Llamadas entrantes (fuera de /chat)
        incomingCallFrom: null,
        incomingCallName: '',
        incomingCallPhoto: null,
        incomingCallType: 'video',
        _callRingtoneInterval: null,
        _callRingtoneOscillators: [],
        _isOnChatPage: false,

        init() {
            this._isOnChatPage = window.location.pathname === '/chat';

            this.fetchCount();
            this.fetchUnread();
            setInterval(() => this.fetchCount(), 30000);
            setInterval(() => this.fetchUnread(), 10000);

            // Poll de llamadas solo si NO estamos en /chat (chat tiene su propio poll)
            if (!this._isOnChatPage) {
                setInterval(() => this.pollCalls(), 3000);
            }
        },

        async fetchCount() {
            try {
                const response = await fetch('{{ route("presence.online") }}');
                const data = await response.json();
                this.onlineCount = data.count || 0;
            } catch (e) {}
        },

        async fetchUnread() {
            try {
                const response = await fetch('{{ route("chat.unread-count") }}');
                const data = await response.json();
                const newCount = data.count || 0;

                // Sonar y mostrar popup si hay mas no leidos que antes
                if (this._prevUnreadCount >= 0 && newCount > this._prevUnreadCount) {
                    if (typeof window.ChatNotificationSound !== 'undefined') {
                        window.ChatNotificationSound.play();
                    }
                    // Cargar mensajes para el popup
                    await this.fetchPopupMessages();
                    this.showPopup = true;

                    // Auto-ocultar popup despues de 8 segundos
                    setTimeout(() => { this.showPopup = false; }, 8000);
                }
                this._prevUnreadCount = this.unreadCount;
                this.unreadCount = newCount;
            } catch (e) {}
        },

        async fetchPopupMessages() {
            try {
                const response = await fetch('{{ route("chat.unread-messages") }}');
                const data = await response.json();
                this.popupMessages = data.messages || [];
            } catch (e) {}
        },

        formatTimeAgo(isoString) {
            const date = new Date(isoString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return '{{ __("ahora") }}';
            if (diff < 3600) return Math.floor(diff / 60) + ' min';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h';
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        },

        // ===================== LLAMADAS ENTRANTES =====================

        async pollCalls() {
            try {
                const res = await fetch('{{ route("call.poll") }}');
                const data = await res.json();
                const signals = data.signals || [];

                for (const signal of signals) {
                    if (signal.type === 'call-request' && signal.callee_id === {{ Auth::id() }}) {
                        if (!this.incomingCallFrom) {
                            this.incomingCallFrom = signal.caller_id;
                            this.incomingCallName = signal.caller_name || '{{ __("Usuario") }}';
                            this.incomingCallType = signal.media_type || 'video';
                            this.incomingCallPhoto = null;
                            this.playCallRingtone();
                        }
                    }
                    if (signal.type === 'call-end' && this.incomingCallFrom) {
                        this.dismissIncomingCall();
                    }
                }
            } catch (e) {}
        },

        acceptIncomingCall() {
            this.stopCallRingtone();
            // Redirigir a /chat para manejar la llamada ahi
            window.location.href = '{{ route("chat.index") }}?incoming_call=' + this.incomingCallFrom;
        },

        async rejectIncomingCall() {
            this.stopCallRingtone();

            if (this.incomingCallFrom) {
                try {
                    await fetch('{{ route("call.respond") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ caller_id: this.incomingCallFrom, response: 'reject' }),
                    });
                } catch (e) {}
            }

            this.dismissIncomingCall();
        },

        dismissIncomingCall() {
            this.stopCallRingtone();
            this.incomingCallFrom = null;
            this.incomingCallName = '';
            this.incomingCallPhoto = null;
        },

        playCallRingtone() {
            this.stopCallRingtone();
            if (typeof window.ChatNotificationSound === 'undefined') return;

            const ctx = window.ChatNotificationSound.getContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();

            const playTone = () => {
                if (!this.incomingCallFrom) return;
                try {
                    const t = ctx.currentTime;
                    const gain = ctx.createGain();
                    gain.gain.setValueAtTime(0.3, t);
                    gain.gain.exponentialRampToValueAtTime(0.01, t + 0.8);
                    gain.connect(ctx.destination);

                    const o1 = ctx.createOscillator();
                    o1.type = 'sine';
                    o1.frequency.setValueAtTime(880, t);
                    o1.connect(gain);
                    o1.start(t);
                    o1.stop(t + 0.15);
                    this._callRingtoneOscillators.push(o1);

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
                    this._callRingtoneOscillators.push(o2);
                } catch (e) {}
            };

            playTone();
            this._callRingtoneInterval = setInterval(playTone, 2000);
        },

        stopCallRingtone() {
            if (this._callRingtoneInterval) {
                clearInterval(this._callRingtoneInterval);
                this._callRingtoneInterval = null;
            }
            this._callRingtoneOscillators.forEach(o => {
                try { o.stop(); } catch (e) {}
            });
            this._callRingtoneOscillators = [];
        }
    };
}
</script>
