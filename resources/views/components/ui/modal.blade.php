@props([
    'title' => null,
    'maxWidth' => 'max-w-[440px]',
    'showClose' => true,
    'closeLabel' => null,
])

@php
    $modalId = 'modal-'.Illuminate\Support\Str::uuid();
@endphp

<div
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    {{ $attributes }}
>
    @isset($trigger)
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @endisset

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-content/30 p-4"
        @click.self="open = false"
    >
        <div
            x-show="open"
            x-trap.inert.noscroll="open"
            role="dialog"
            aria-modal="true"
            @if ($title) aria-labelledby="{{ $modalId }}-title" @endif
            class="w-full {{ $maxWidth }} rounded-overlay bg-surface p-6 shadow-overlay"
        >
            @if ($title || $showClose)
                <div class="flex items-start justify-between gap-4">
                    @if ($title)
                        <h2 id="{{ $modalId }}-title" class="text-lg font-semibold tracking-tight text-content">
                            {{ $title }}
                        </h2>
                    @endif

                    @if ($showClose)
                        <button
                            type="button"
                            class="-mr-1 rounded-control p-1 text-content-muted transition-colors duration-150 hover:text-content"
                            @click="open = false"
                            aria-label="{{ $closeLabel ?? __('common.close') }}"
                            title="{{ $closeLabel ?? __('common.close') }}"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <div @class(['mt-3' => $title || $showClose])>
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="mt-6 flex justify-end gap-2">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
