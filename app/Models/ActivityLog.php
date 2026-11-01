<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'action',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return __("activity.actions.{$this->action}");
    }

    public function subjectTitle(): ?string
    {
        return $this->properties['title'] ?? $this->task?->title;
    }

    public function excerpt(): ?string
    {
        return $this->properties['excerpt'] ?? null;
    }

    public function fieldLabel(): ?string
    {
        $field = $this->properties['field'] ?? null;

        return $field ? __("activity.fields.{$field}") : null;
    }

    public function hasChange(): bool
    {
        $properties = $this->properties ?? [];

        return $this->fieldLabel() !== null
            && (array_key_exists('old', $properties) || array_key_exists('new', $properties));
    }

    public function oldValueLabel(): ?string
    {
        return $this->valueLabel('old');
    }

    public function newValueLabel(): ?string
    {
        return $this->valueLabel('new');
    }

    private function valueLabel(string $key): ?string
    {
        $properties = $this->properties ?? [];

        if (! array_key_exists($key, $properties)) {
            return null;
        }

        $value = $properties[$key];

        if ($value === null || $value === '') {
            return __('common.not_set');
        }

        return match ($properties['field'] ?? null) {
            'status' => __("tasks.status.{$value}"),
            'priority' => __("tasks.priority.{$value}"),
            'due_date' => $this->formatDate((string) $value),
            'body' => str((string) $value)->limit(160)->toString(),
            default => (string) $value,
        };
    }

    private function formatDate(string $value): string
    {
        try {
            return Carbon::parse($value)->translatedFormat('d M Y');
        } catch (\Throwable) {
            return $value;
        }
    }
}
