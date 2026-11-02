<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        User::query()->create([
            ...$request->safe()->only(['name', 'email', 'password']),
            'role' => UserRole::Member,
        ]);

        return redirect()->route('login')
            ->with('success', __('auth.registered'));
    }
}
