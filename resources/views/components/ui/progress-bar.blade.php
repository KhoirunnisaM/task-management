@props(['value', 'label' => null])

<div {{ $attributes }}>
    @if ($label)
        <div class="mb-1.5 flex items-center justify-between text-xs">
            <span class="text-content-muted">{{ $label }}</span>
            <span class="font-medium tabular-nums text-content">{{ $value }}%</span>
        </div>
    @endif

    <div
        class="h-1.5 overflow-hidden rounded-full bg-line"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="{{ $label ?? __('projects.progress') }}"
    >
        <div class="h-full rounded-full {{ $value >= 100 ? 'bg-status-completed' : 'bg-brand' }}" style="width: {{ $value }}%"></div>
    </div>
</div>
