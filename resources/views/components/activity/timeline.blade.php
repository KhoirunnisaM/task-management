@props(['logs', 'emptyTitle' => null, 'emptyBody' => null])

@if ($logs->isEmpty())
    <x-ui.empty-state
        class="mt-3"
        :title="$emptyTitle ?? __('activity.empty_title')"
        :body="$emptyBody ?? __('activity.empty_body')"
    />
@else
    <ol {{ $attributes->class('mt-3 divide-y divide-line rounded-card border border-line bg-surface') }}>
        @foreach ($logs as $log)
            <li class="flex items-start gap-3 p-4">
                <x-ui.avatar :user="$log->user" size="md" />

                <div class="min-w-0 flex-1">
                    <p class="text-sm text-content">
                        <span class="font-medium">{{ $log->user?->name ?? __('common.not_set') }}</span>
                        {{ $log->actionLabel() }}
                        @if ($log->subjectTitle())
                            <span class="font-medium">“{{ $log->subjectTitle() }}”</span>
                        @endif
                    </p>

                    @if ($log->hasChange())
                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-content-muted">
                            <span class="font-medium text-content">{{ $log->fieldLabel() }}:</span>
                            <span class="line-through">{{ $log->oldValueLabel() }}</span>
                            <span aria-hidden="true">&rarr;</span>
                            <span class="font-medium text-content">{{ $log->newValueLabel() }}</span>
                        </p>
                    @elseif ($log->excerpt())
                        <p class="mt-1 text-xs text-content-muted">“{{ $log->excerpt() }}”</p>
                    @endif

                    <p class="mt-1 text-xs text-content-muted">
                        <time datetime="{{ $log->created_at?->toIso8601String() }}">
                            {{ $log->created_at?->diffForHumans() }}
                        </time>
                    </p>
                </div>
            </li>
        @endforeach
    </ol>
@endif
