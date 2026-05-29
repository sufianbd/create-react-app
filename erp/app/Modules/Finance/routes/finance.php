<?php

use App\Modules\Finance\Http\Controllers\AccountController;
use App\Modules\Finance\Http\Controllers\ContactController;
use App\Modules\Finance\Http\Controllers\InvoiceController;
use App\Modules\Finance\Http\Controllers\JournalEntryController;
use App\Modules\Finance\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {

    // Chart of Accounts
    Route::resource('accounts', AccountController::class)->except(['show']);

    // Contacts
    Route::resource('contacts', ContactController::class)->except(['show']);

    // Journal Entries
    Route::resource('journal-entries', JournalEntryController::class)->except(['edit', 'update']);
    Route::patch('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])
        ->name('journal-entries.post');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::patch('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])
        ->name('invoices.payments.store');

    // Reports
    Route::get('reports/trial-balance', [ReportController::class, 'trialBalance'])
        ->name('reports.trial-balance');
});
