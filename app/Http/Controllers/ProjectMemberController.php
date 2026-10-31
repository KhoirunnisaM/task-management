<?php

namespace App\Http\Controllers;

use App\Actions\Members\AddMemberAction;
use App\Actions\Members\ChangeMemberRoleAction;
use App\Actions\Members\RemoveMemberAction;
use App\Enums\ProjectRole;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ProjectMemberController extends Controller
{
    public function store(
        StoreMemberRequest $request,
        Project $project,
        AddMemberAction $action,
    ): RedirectResponse {
        $action->execute($project, User::findOrFail($request->validated('user_id')));

        return back()->with('success', __('members.added'));
    }

    public function update(
        UpdateMemberRequest $request,
        Project $project,
        User $member,
        ChangeMemberRoleAction $action,
    ): RedirectResponse {
        $action->execute($project, $member, ProjectRole::from($request->validated('role')));

        return back()->with('success', __('members.role_updated'));
    }

    public function destroy(
        Project $project,
        User $member,
        RemoveMemberAction $action,
    ): RedirectResponse {
        $this->authorize('removeMember', [$project, $member]);

        $action->execute($project, $member);

        return back()->with('success', __('members.removed'));
    }
}
