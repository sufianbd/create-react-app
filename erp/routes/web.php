<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Modules\Core\Http\Controllers\AuditLogController as CoreAuditLogController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/audit-logs', [CoreAuditLogController::class, 'index'])->name('audit-logs.index');
});

require __DIR__ . '/auth.php';

// Public Customer Portal (no auth required)
Route::get('/portal/{token}', [\App\Modules\Finance\Http\Controllers\CustomerPortalController::class, 'show'])->name('portal.show');
Route::get('/portal/{token}/invoices/{invoice}', [\App\Modules\Finance\Http\Controllers\CustomerPortalController::class, 'invoice'])->name('portal.invoice');

Route::post('/notifications/refresh', function (\Illuminate\Http\Request $request) {
    \App\Services\NotificationService::clearCache($request->user()->tenant_id);
    return back();
})->name('notifications.refresh')->middleware(['auth', 'verified']);
