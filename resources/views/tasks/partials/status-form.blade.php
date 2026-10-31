@props(['project', 'task', 'statuses'])

<form method="POST" action="{{ route('projects.tasks.status', [$project, $task]) }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
    @csrf
    @method('PATCH')

    <x-ui.form-field :label="__('tasks.status_field')" name="status" class="flex-1">
        <select id="status" name="status" class="h-9 w-full rounded-control border border-line bg-surface px-3 text-sm text-content">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-ui.form-field>

    <x-ui.button type="submit" variant="secondary" class="sm:mb-0">{{ __('tasks.update_status') }}</x-ui.button>
</form>
