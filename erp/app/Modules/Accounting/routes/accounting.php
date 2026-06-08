<?php

use App\Modules\Accounting\Http\Controllers\AccountController;
use App\Modules\Accounting\Http\Controllers\AccountingPeriodController;
use App\Modules\Accounting\Http\Controllers\AccountingReportController;
use App\Modules\Accounting\Http\Controllers\JournalEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('accounting')->name('accounting.')->group(function () {
    // Reports
    Route::get('reports/trial-balance',    [AccountingReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('reports/balance-sheet',    [AccountingReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('reports/income-statement', [AccountingReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/general-ledger/{account}', [AccountingReportController::class, 'generalLedger'])->name('reports.general-ledger');

    // Account actions BEFORE resource
    Route::post('accounts/seed-defaults', [AccountController::class, 'seedDefaults'])->name('accounts.seed-defaults');
    Route::resource('accounts', AccountController::class)->except(['show']);

    // Journal entry actions BEFORE resource
    Route::post('journal-entries/{journalEntry}/post',    [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('journal-entries/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
    Route::resource('journal-entries', JournalEntryController::class)->except(['edit', 'update']);

    // Periods
    Route::post('periods/{period}/close', [AccountingPeriodController::class, 'close'])->name('periods.close');
    Route::resource('periods', AccountingPeriodController::class)->except(['show', 'edit', 'update']);
});
