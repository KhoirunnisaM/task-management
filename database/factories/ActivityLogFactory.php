<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'task_id' => null,
            'user_id' => User::factory(),
            'action' => 'task.created',
            'properties' => [
                'title' => fake()->sentence(4),
            ],
        ];
    }
}
