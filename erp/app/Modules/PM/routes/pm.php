<?php

use App\Modules\PM\Http\Controllers\MilestoneController;
use App\Modules\PM\Http\Controllers\PMDashboardController;
use App\Modules\PM\Http\Controllers\ProjectController;
use App\Modules\PM\Http\Controllers\TaskController;
use App\Modules\PM\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('pm')->name('pm.')->group(function () {
    Route::get('dashboard', [PMDashboardController::class, 'index'])->name('dashboard');

    // Task complete action BEFORE resource
    Route::post('projects/{project}/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');

    // Milestone actions
    Route::post('projects/{project}/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
    Route::post('projects/{project}/milestones/{milestone}/complete', [MilestoneController::class, 'complete'])->name('milestones.complete');
    Route::delete('projects/{project}/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');

    // Time entries
    Route::post('tasks/{task}/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
    Route::delete('time-entries/{entry}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');
    Route::get('time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');

    // Nested task routes
    Route::resource('projects.tasks', TaskController::class)->except(['index']);
    Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');

    // Project resource
    Route::resource('projects', ProjectController::class);
});
