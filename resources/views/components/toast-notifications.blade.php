{{-- Toast Notifications Component --}}
<div x-data="toastNotifications()"
     x-init="init()"
     class="fixed top-4 right-4 z-50 space-y-2"
     style="max-width: 400px;">

    {{-- Toast container --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-8"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-8"
             :class="{
                 'bg-green-50 border-green-400 text-green-800': toast.type === 'success',
                 'bg-red-50 border-red-400 text-red-800': toast.type === 'error',
                 'bg-yellow-50 border-yellow-400 text-yellow-800': toast.type === 'warning',
                 'bg-blue-50 border-blue-400 text-blue-800': toast.type === 'info'
             }"
             class="rounded-lg border-l-4 p-4 shadow-lg flex items-start gap-3">

            {{-- Icon --}}
            <div class="flex-shrink-0">
                {{-- Success icon --}}
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </template>
                {{-- Error icon --}}
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </template>
                {{-- Warning icon --}}
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </template>
                {{-- Info icon --}}
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </div>

            {{-- Content --}}
            <div class="flex-1">
                <p class="text-sm font-medium" x-text="toast.message"></p>
            </div>

            {{-- Close button --}}
            <button @click="removeToast(toast.id)"
                    class="flex-shrink-0 ml-2 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
function toastNotifications() {
    return {
        toasts: [],
        counter: 0,

        init() {
            // Load flash messages from Laravel session
            @if(session('success'))
                this.addToast('success', @json(session('success')));
            @endif

            @if(session('error'))
                this.addToast('error', @json(session('error')));
            @endif

            @if(session('warning'))
                this.addToast('warning', @json(session('warning')));
            @endif

            @if(session('info'))
                this.addToast('info', @json(session('info')));
            @endif

            // Listen for custom toast events (for AJAX responses)
            window.addEventListener('show-toast', (event) => {
                this.addToast(event.detail.type, event.detail.message);
            });
        },

        addToast(type, message) {
            const id = ++this.counter;
            this.toasts.push({
                id: id,
                type: type,
                message: message,
                show: true
            });

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.removeToast(id);
            }, 5000);
        },

        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].show = false;
                // Remove from array after animation
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }
}
</script>
