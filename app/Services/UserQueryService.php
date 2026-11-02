<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserQueryService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        [$column, $direction] = match ($filters['sort'] ?? 'joined_desc') {
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'joined_asc' => ['created_at', 'asc'],
            default => ['created_at', 'desc'],
        };

        return User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->withCount('projects')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query
                ->where('role', $role))
            ->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();
    }
}
