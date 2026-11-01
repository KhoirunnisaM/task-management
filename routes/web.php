<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('projects', ProjectController::class);

    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])
        ->name('projects.members.store');
    Route::patch('projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])
        ->name('projects.members.update');
    Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])
        ->name('projects.members.destroy');

    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/board', [TaskController::class, 'board'])
            ->name('projects.tasks.board');

        Route::resource('projects.tasks', TaskController::class);

        Route::patch('projects/{project}/tasks/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('projects.tasks.status');
        Route::patch('projects/{project}/tasks/{task}/reorder', [TaskController::class, 'reorder'])
            ->name('projects.tasks.reorder');

        Route::post('projects/{project}/tasks/{task}/comments', [CommentController::class, 'store'])
            ->name('projects.tasks.comments.store');
        Route::patch('projects/{project}/tasks/{task}/comments/{comment}', [CommentController::class, 'update'])
            ->name('projects.tasks.comments.update');
        Route::delete('projects/{project}/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy'])
            ->name('projects.tasks.comments.destroy');
    });
});
