{{-- Widget "Quien esta en linea" para el dashboard --}}
<div class="card mt-6" x-data="presenceWidget()" x-init="init()">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-theme flex items-center gap-2">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full shrink-0"></span>
                {{ __('Usuarios en linea') }}
            </h3>
            <span class="badge badge-success" x-text="totalCount + ' ' + '{{ __('en linea') }}'"></span>
        </div>

        <div x-show="loading" class="text-center py-4">
            <svg class="animate-spin h-6 w-6 mx-auto text-theme-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <div x-show="!loading && totalCount === 0" class="text-center py-4 text-theme-secondary">
            {{ __('No hay usuarios conectados en este momento') }}
        </div>

        {{-- Seccion: Familia --}}
        <div x-show="!loading && familyUsers.length > 0" class="mb-3">
            <h4 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('Familia') }}
                <span class="text-xs font-normal" x-text="'(' + familyUsers.length + ')'"></span>
            </h4>
            <div class="space-y-2">
                <template x-for="user in familyUsers" :key="'f-' + user.id">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-theme-secondary/10 hover:bg-theme-secondary/20 transition-colors">
                        <div class="flex items-center gap-3">
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
                            <div>
                                <span class="text-sm font-medium text-theme" x-text="user.name"></span>
                            </div>
                        </div>
                        <button @click="openChat(user.id, user.name, user.photo)"
                                class="text-xs btn-outline btn-sm px-2 py-1">
                            {{ __('Chat') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Seccion: Comunidad --}}
        <div x-show="!loading && communityUsers.length > 0" class="mb-3">
            <h4 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
                {{ __('Comunidad') }}
                <span class="text-xs font-normal" x-text="'(' + communityUsers.length + ')'"></span>
            </h4>
            <div class="space-y-2">
                <template x-for="user in communityUsers" :key="'c-' + user.id">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-theme-secondary/10 hover:bg-theme-secondary/20 transition-colors">
                        <div class="flex items-center gap-3">
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
                            <div>
                                <span class="text-sm font-medium text-theme" x-text="user.name"></span>
                            </div>
                        </div>
                        <button @click="openChat(user.id, user.name, user.photo)"
                                class="text-xs btn-outline btn-sm px-2 py-1">
                            {{ __('Chat') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Seccion: Publico --}}
        <div x-show="!loading && publicUsers.length > 0" class="mb-3">
            <h4 class="text-xs font-semibold text-theme-muted uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('Publico') }}
                <span class="text-xs font-normal" x-text="'(' + publicUsers.length + ')'"></span>
            </h4>
            <div class="space-y-2">
                <template x-for="user in publicUsers" :key="'p-' + user.id">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-theme-secondary/10 hover:bg-theme-secondary/20 transition-colors">
                        <div class="flex items-center gap-3">
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
                            <div>
                                <span class="text-sm font-medium text-theme" x-text="user.name"></span>
                            </div>
                        </div>
                        <button @click="openChat(user.id, user.name, user.photo)"
                                class="text-xs btn-outline btn-sm px-2 py-1">
                            {{ __('Chat') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function presenceWidget() {
    return {
        familyUsers: [],
        communityUsers: [],
        publicUsers: [],
        totalCount: 0,
        loading: true,

        init() {
            this.fetchOnline();
            setInterval(() => this.fetchOnline(), 30000);
        },

        async fetchOnline() {
            try {
                const response = await fetch('{{ route("presence.online") }}');
                const data = await response.json();
                this.familyUsers = data.family || [];
                this.communityUsers = data.community || [];
                this.publicUsers = data.public || [];
                this.totalCount = data.count || 0;
            } catch (e) {
                console.error('Error fetching online users:', e);
            } finally {
                this.loading = false;
            }
        },

        openChat(userId, userName, userPhoto) {
            window.dispatchEvent(new CustomEvent('open-chat', {
                detail: { userId, userName, userPhoto }
            }));
        }
    };
}
</script>
