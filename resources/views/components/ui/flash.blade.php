@if (session('success') || session('error'))
    <div class="pointer-events-none fixed inset-x-4 top-4 z-50 flex flex-col items-center gap-2 sm:inset-x-auto sm:right-6 sm:top-auto sm:bottom-6 sm:items-end">
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 5000)"
                x-transition.opacity.duration.200ms
                role="status"
                aria-live="polite"
                class="pointer-events-auto w-full max-w-[360px] rounded-card border border-line bg-surface p-4 shadow-overlay"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-status-completed-soft text-status-completed">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>

                    <p class="flex-1 text-sm text-content">{{ session('success') }}</p>

                    <button
                        type="button"
                        class="rounded-control text-content-muted transition-colors duration-150 hover:text-content"
                        @click="show = false"
                        aria-label="{{ __('common.close_notification') }}"
                        title="{{ __('common.close_notification') }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition.opacity.duration.200ms
                role="alert"
                aria-live="assertive"
                class="pointer-events-auto w-full max-w-[360px] rounded-card border border-line bg-surface p-4 shadow-overlay"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>

                    <p class="flex-1 text-sm text-content">{{ session('error') }}</p>

                    <button
                        type="button"
                        class="rounded-control text-content-muted transition-colors duration-150 hover:text-content"
                        @click="show = false"
                        aria-label="{{ __('common.close_notification') }}"
                        title="{{ __('common.close_notification') }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
@endif
