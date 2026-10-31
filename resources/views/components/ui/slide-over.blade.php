@props([
    'title' => null,
    'width' => 'md:w-[480px]',
    'showClose' => true,
    'closeLabel' => null,
])

@php
    $slideOverId = 'slide-over-'.Illuminate\Support\Str::uuid();
@endphp

<div
    x-data="{ open: false }"
    x-effect="if (open) { $nextTick(() => $refs.slideOverTitle?.focus()) }"
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
        class="fixed inset-0 z-50 flex justify-end bg-content/30"
        @click.self="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-trap.inert.noscroll="open"
            role="dialog"
            aria-modal="true"
            @if ($title) aria-labelledby="{{ $slideOverId }}-title" @endif
            class="flex h-full w-full flex-col bg-surface shadow-overlay md:rounded-l-overlay {{ $width }}"
        >
            <header class="flex h-14 shrink-0 items-center justify-between gap-4 border-b border-line px-5">
                @if ($title)
                    <h2
                        id="{{ $slideOverId }}-title"
                        x-ref="slideOverTitle"
                        tabindex="-1"
                        class="text-xl font-semibold tracking-tight text-content focus:outline-none"
                    >
                        {{ $title }}
                    </h2>
                @else
                    <span></span>
                @endif

                @if ($showClose)
                    <button
                        type="button"
                        class="rounded-control p-1.5 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content"
                        @click="open = false"
                        aria-label="{{ $closeLabel ?? __('common.close') }}"
                        title="{{ $closeLabel ?? __('common.close') }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </header>

            <div class="flex-1 overflow-y-auto p-5">
                {{ $slot }}
            </div>

            @isset($footer)
                <footer class="shrink-0 border-t border-line p-4">
                    {{ $footer }}
                </footer>
            @endisset
        </div>
    </div>
</div>
