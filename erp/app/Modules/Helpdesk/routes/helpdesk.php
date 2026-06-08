<?php

use App\Modules\Helpdesk\Http\Controllers\HelpdeskDashboardController;
use App\Modules\Helpdesk\Http\Controllers\HelpdeskTeamController;
use App\Modules\Helpdesk\Http\Controllers\HelpdeskTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('helpdesk')->name('helpdesk.')->group(function () {
    Route::get('dashboard', [HelpdeskDashboardController::class, 'index'])->name('dashboard');

    // Ticket actions BEFORE resource
    Route::post('tickets/{ticket}/resolve', [HelpdeskTicketController::class, 'resolve'])->name('tickets.resolve');
    Route::post('tickets/{ticket}/close',   [HelpdeskTicketController::class, 'close'])->name('tickets.close');
    Route::post('tickets/{ticket}/reopen',  [HelpdeskTicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('tickets/{ticket}/reply',   [HelpdeskTicketController::class, 'reply'])->name('tickets.reply');
    Route::resource('tickets', HelpdeskTicketController::class);

    Route::resource('teams', HelpdeskTeamController::class)->except(['show', 'create', 'edit']);
});
