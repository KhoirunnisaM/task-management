@props(['title', 'body' => null])

<div {{ $attributes->class('rounded-card border border-line bg-surface p-8 text-center') }}>
    <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-brand-soft text-brand-text">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h4.5A2.25 2.25 0 0 1 12.75 6v4.5A2.25 2.25 0 0 1 10.5 12.75H6A2.25 2.25 0 0 1 3.75 10.5V6ZM3.75 16.5A2.25 2.25 0 0 1 6 14.25h4.5a2.25 2.25 0 0 1 2.25 2.25V19.5A2.25 2.25 0 0 1 10.5 21.75H6a2.25 2.25 0 0 1-2.25-2.25V16.5ZM14.25 6A2.25 2.25 0 0 1 16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v4.5A2.25 2.25 0 0 1 18 12.75h-1.5a2.25 2.25 0 0 1-2.25-2.25V6Z" />
        </svg>
    </div>

    <h2 class="mt-4 text-base font-semibold text-content">{{ $title }}</h2>

    @if ($body)
        <p class="mx-auto mt-1 max-w-md text-sm text-content-muted">{{ $body }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex justify-center">{{ $slot }}</div>
    @endif
</div>
