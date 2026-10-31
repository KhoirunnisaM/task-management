@props(['members', 'limit' => 4])

@php
    $visible = $members->take($limit);
    $remaining = $members->count() - $visible->count();
@endphp

<div {{ $attributes->class('flex items-center') }}>
    @foreach ($visible as $member)
        <x-ui.avatar :user="$member" size="md" class="-ml-2 ring-2 ring-surface first:ml-0" />
    @endforeach

    @if ($remaining > 0)
        <span class="-ml-2 inline-flex size-8 items-center justify-center rounded-full bg-line text-xs font-semibold text-content-muted ring-2 ring-surface">
            +{{ $remaining }}
        </span>
    @endif
</div>
