@props(['project', 'statuses', 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-card border border-line bg-surface p-5 sm:p-6">
        <div class="space-y-5">
            <x-ui.form-field :label="__('projects.title_field')" name="title">
                <x-ui.input
                    name="title"
                    :value="old('title', $project->title)"
                    required
                    autofocus
                    :placeholder="__('projects.title_placeholder')"
                />
            </x-ui.form-field>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.form-field :label="__('common.status')" name="status">
                    <x-ui.select id="status" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $project->status?->value ?? App\Enums\ProjectStatus::Planning->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.form-field>

                <x-ui.form-field :label="__('projects.start_date')" name="start_date" :optional="true">
                    <x-ui.input
                        name="start_date"
                        type="date"
                        :value="old('start_date', $project->start_date?->format('Y-m-d'))"
                    />
                </x-ui.form-field>

                <x-ui.form-field :label="__('projects.end_date')" name="end_date" :optional="true">
                    <x-ui.input
                        name="end_date"
                        type="date"
                        :value="old('end_date', $project->end_date?->format('Y-m-d'))"
                    />
                </x-ui.form-field>
            </div>

            <x-ui.form-field :label="__('projects.description')" name="description" :optional="true">
                <x-ui.textarea
                    name="description"
                    rows="5"
                    :value="old('description', $project->description)"
                    :placeholder="__('projects.description_placeholder')"
                />
            </x-ui.form-field>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2">
        <x-ui.button :href="route('projects.index')" variant="secondary">{{ __('common.cancel') }}</x-ui.button>
        <x-ui.button type="submit">{{ __('common.save') }}</x-ui.button>
    </div>
</form>
