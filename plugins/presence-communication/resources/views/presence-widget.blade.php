{{-- Widget de presencia (version standalone para uso directo) --}}
<div x-data="presenceWidgetStandalone()" x-init="init()">
    <div class="flex items-center gap-2 mb-3">
        <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
        <span class="text-sm font-medium text-theme" x-text="totalCount + ' {{ __('en linea') }}'"></span>
    </div>

    {{-- Familia --}}
    <div x-show="familyUsers.length > 0" class="mb-2">
        <span class="text-xs font-semibold text-theme-muted uppercase tracking-wider">{{ __('Familia') }}</span>
        <div class="space-y-1 mt-1">
            <template x-for="user in familyUsers" :key="'f-' + user.id">
                <div class="flex items-center gap-2 py-1">
                    <template x-if="user.photo">
                        <img :src="user.photo" class="w-6 h-6 rounded-full object-cover">
                    </template>
                    <template x-if="!user.photo">
                        <div class="w-6 h-6 rounded-full bg-mf-primary text-white flex items-center justify-center text-xs font-bold"
                             x-text="user.name.charAt(0).toUpperCase()">
                        </div>
                    </template>
                    <span class="text-sm text-theme" x-text="user.name"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Comunidad --}}
    <div x-show="communityUsers.length > 0" class="mb-2">
        <span class="text-xs font-semibold text-theme-muted uppercase tracking-wider">{{ __('Comunidad') }}</span>
        <div class="space-y-1 mt-1">
            <template x-for="user in communityUsers" :key="'c-' + user.id">
                <div class="flex items-center gap-2 py-1">
                    <template x-if="user.photo">
                        <img :src="user.photo" class="w-6 h-6 rounded-full object-cover">
                    </template>
                    <template x-if="!user.photo">
                        <div class="w-6 h-6 rounded-full bg-gray-400 dark:bg-gray-600 text-white flex items-center justify-center text-xs font-bold"
                             x-text="user.name.charAt(0).toUpperCase()">
                        </div>
                    </template>
                    <span class="text-sm text-theme" x-text="user.name"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Publico --}}
    <div x-show="publicUsers.length > 0">
        <span class="text-xs font-semibold text-theme-muted uppercase tracking-wider">{{ __('Publico') }}</span>
        <div class="space-y-1 mt-1">
            <template x-for="user in publicUsers" :key="'p-' + user.id">
                <div class="flex items-center gap-2 py-1">
                    <template x-if="user.photo">
                        <img :src="user.photo" class="w-6 h-6 rounded-full object-cover">
                    </template>
                    <template x-if="!user.photo">
                        <div class="w-6 h-6 rounded-full bg-blue-400 dark:bg-blue-600 text-white flex items-center justify-center text-xs font-bold"
                             x-text="user.name.charAt(0).toUpperCase()">
                        </div>
                    </template>
                    <span class="text-sm text-theme" x-text="user.name"></span>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function presenceWidgetStandalone() {
    return {
        familyUsers: [],
        communityUsers: [],
        publicUsers: [],
        totalCount: 0,

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
                // Silenciar errores
            }
        }
    };
}
</script>
