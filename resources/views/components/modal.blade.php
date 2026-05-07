<div x-data="{
        show: @entangle($attributes->wire('model')),
        mainContent: null,
        syncMainScrollState(value) {
            if (this.mainContent) {
                this.mainContent.classList.toggle('overflow-hidden', value)
            }
        }
    }"
    x-init="mainContent = document.getElementById('app-main-content'); syncMainScrollState(show); $watch('show', value => syncMainScrollState(value))"
    x-cloak
    x-show="show"
    class="fixed inset-0 z-[9999] overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-[9999] transition-opacity" aria-hidden="true" @click="show = false"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-[10000] inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-{{ $type ?? 'red' }}-100 sm:mx-0 sm:h-10 sm:w-10">
                        <!-- Heroicon name: outline/check -->
                        <svg class="h-6 w-6 text-{{ $type ?? 'red' }}-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-ref="title">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" x-ref="body">
                                {{ $content }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                {{ $footer }}
            </div>
        </div>
    </div>
</div>
