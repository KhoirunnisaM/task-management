<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUserRequest;
use App\Models\User;
use App\Services\UserQueryService;
use Illuminate\View\View;

class AdminMemberController extends Controller
{
    public function index(IndexUserRequest $request, UserQueryService $query): View
    {
        return view('admin.members.index', [
            'users' => $query->paginate($request->validated()),
            'filters' => $request->validated(),
        ]);
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->loadCount('projects');

        return view('admin.members.show', [
            'member' => $user,
            'projects' => $user->projects()
                ->orderBy('title')
                ->get(),
        ]);
    }
}
