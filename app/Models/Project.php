<?php

namespace App\Models;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(ProjectMember::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('members', function (Builder $members) use ($user) {
            $members->whereKey($user->getKey());
        });
    }

    public function isOwner(User $user): bool
    {
        return (int) $this->owner_id === (int) $user->getKey();
    }

    public function roleOf(User $user): ?ProjectRole
    {
        $member = $this->relationLoaded('members')
            ? $this->members->firstWhere('id', $user->getKey())
            : $this->members()->whereKey($user->getKey())->first();

        $role = $member?->pivot?->role;

        if ($role instanceof ProjectRole) {
            return $role;
        }

        return $role === null ? null : ProjectRole::tryFrom((string) $role);
    }

    public function isArchived(): bool
    {
        return $this->status === ProjectStatus::Archived;
    }
}
