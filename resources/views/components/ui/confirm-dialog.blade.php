@props([
    'action',
    'title',
    'body' => null,
    'method' => 'DELETE',
    'confirm' => null,
])

<x-ui.modal :title="$title" :show-close="false">
    <x-slot:trigger>
        <button type="button" {{ $attributes->class('cursor-pointer') }}>
            {{ $slot }}
        </button>
    </x-slot:trigger>

    @if ($body)
        <p class="text-sm text-content-muted">{{ $body }}</p>
    @endif

    <x-slot:footer>
        <x-ui.button type="button" variant="secondary" @click="open = false">
            {{ __('common.cancel') }}
        </x-ui.button>

        <form method="POST" action="{{ $action }}">
            @csrf
            @method($method)
            <x-ui.button type="submit" variant="danger">
                {{ $confirm ?? __('common.delete') }}
            </x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.modal>
