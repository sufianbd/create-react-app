<?php

use Illuminate\Support\Facades\Route;

// Core module routes (phase 2 will add more)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
});
