<?php

use App\Modules\Finance\Http\Controllers\AccountController;
use App\Modules\Finance\Http\Controllers\BillController;
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
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
        ->name('invoices.print');

    // Bills (AP)
    Route::resource('bills', BillController::class)->except(['edit', 'update']);
    Route::patch('bills/{bill}/receive', [BillController::class, 'receive'])->name('bills.receive');
    Route::patch('bills/{bill}/cancel', [BillController::class, 'cancel'])->name('bills.cancel');
    Route::post('bills/{bill}/payments', [BillController::class, 'recordPayment'])
        ->name('bills.payments.store');

    // Reports
    Route::get('reports/trial-balance', [ReportController::class, 'trialBalance'])
        ->name('reports.trial-balance');
    Route::get('reports/profit-loss', [ReportController::class, 'profitAndLoss'])
        ->name('reports.profit-loss');
    Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])
        ->name('reports.balance-sheet');
    Route::get('reports/aged-receivables', [ReportController::class, 'agedReceivables'])->name('reports.aged-receivables');
    Route::get('reports/aged-payables',    [ReportController::class, 'agedPayables'])->name('reports.aged-payables');
    Route::get('reports/account-ledger',               [ReportController::class, 'accountLedgerIndex'])->name('reports.account-ledger.index');
    Route::get('reports/account-ledger/{account}',     [ReportController::class, 'accountLedger'])->name('reports.account-ledger');
});
