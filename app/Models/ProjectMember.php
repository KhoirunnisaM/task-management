<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    protected $table = 'project_user';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => ProjectRole::class,
        ];
    }
}
