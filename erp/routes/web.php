<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('settings')->middleware(['auth', 'verified'])->group(function () {
    Route::get('users',                          [UserManagementController::class, 'index'])->name('settings.users.index');
    Route::post('users/invite',                  [UserManagementController::class, 'invite'])->name('settings.users.invite');
    Route::patch('users/{user}/role',            [UserManagementController::class, 'updateRole'])->name('settings.users.update-role');
    Route::patch('users/{user}/toggle-active',   [UserManagementController::class, 'toggleActive'])->name('settings.users.toggle-active');
    Route::delete('users/{user}',                [UserManagementController::class, 'destroy'])->name('settings.users.destroy');

    Route::get('company',        [CompanySettingsController::class, 'show'])->name('settings.company.show');
    Route::patch('company',      [CompanySettingsController::class, 'update'])->name('settings.company.update');
    Route::post('company/logo',  [CompanySettingsController::class, 'uploadLogo'])->name('settings.company.logo');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('settings.audit-log');
});

require __DIR__ . '/auth.php';
