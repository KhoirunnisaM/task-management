@props(['project', 'task', 'priorities', 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-card border border-line bg-surface p-5 sm:p-6">
        <div class="space-y-5">
            <x-ui.form-field :label="__('tasks.title_field')" name="title">
                <x-ui.input
                    name="title"
                    :value="old('title', $task->title)"
                    required
                    autofocus
                    :placeholder="__('tasks.title_placeholder')"
                />
            </x-ui.form-field>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.form-field :label="__('tasks.priority_label')" name="priority">
                    <x-ui.select id="priority" name="priority" required>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $task->priority?->value ?? App\Enums\TaskPriority::Medium->value) === $priority->value)>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.form-field>

                <x-ui.form-field :label="__('tasks.due_date')" name="due_date" :optional="true">
                    <x-ui.input
                        name="due_date"
                        type="date"
                        :value="old('due_date', $task->due_date?->format('Y-m-d'))"
                        :min="$task->exists ? null : today()->toDateString()"
                    />
                </x-ui.form-field>

                <x-ui.form-field :label="__('tasks.assignee')" name="assigned_to" :optional="true">
                    <x-ui.select id="assigned_to" name="assigned_to">
                        <option value="">{{ __('tasks.unassigned') }}</option>
                        @foreach ($project->members->sortBy('name') as $member)
                            <option value="{{ $member->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $member->id)>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.form-field>
            </div>

            <x-ui.form-field :label="__('tasks.description')" name="description" :optional="true">
                <x-ui.textarea
                    name="description"
                    rows="5"
                    :value="old('description', $task->description)"
                    :placeholder="__('tasks.description_placeholder')"
                />
            </x-ui.form-field>

            @unless ($task->exists)
                <p class="text-xs text-content-muted">{{ __('tasks.create_hint') }}</p>
            @endunless
        </div>
    </div>

    <div class="flex items-center justify-end gap-2">
        <x-ui.button :href="$task->exists ? route('projects.tasks.show', [$project, $task]) : route('projects.tasks.index', $project)" variant="secondary">
            {{ __('common.cancel') }}
        </x-ui.button>
        <x-ui.button type="submit">{{ __('common.save') }}</x-ui.button>
    </div>
</form>
