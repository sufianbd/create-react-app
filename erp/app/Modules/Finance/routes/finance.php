<?php

use App\Modules\Finance\Http\Controllers\AccountController;
use App\Modules\Finance\Http\Controllers\BillController;
use App\Modules\Finance\Http\Controllers\ContactController;
use App\Modules\Finance\Http\Controllers\CreditNoteController;
use App\Modules\Finance\Http\Controllers\InvoiceController;
use App\Modules\Finance\Http\Controllers\JournalEntryController;
use App\Modules\Finance\Http\Controllers\QuoteController;
use App\Modules\Finance\Http\Controllers\RecurringInvoiceController;
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

    // Quotes
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::patch('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::patch('quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::patch('quotes/{quote}/decline', [QuoteController::class, 'decline'])->name('quotes.decline');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert');
    Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Recurring Invoices
    Route::get('recurring-invoices', [RecurringInvoiceController::class, 'index'])->name('recurring-invoices.index');
    Route::get('recurring-invoices/create', [RecurringInvoiceController::class, 'create'])->name('recurring-invoices.create');
    Route::post('recurring-invoices', [RecurringInvoiceController::class, 'store'])->name('recurring-invoices.store');
    Route::get('recurring-invoices/{recurringInvoice}', [RecurringInvoiceController::class, 'show'])->name('recurring-invoices.show');
    Route::patch('recurring-invoices/{recurringInvoice}/pause', [RecurringInvoiceController::class, 'pause'])->name('recurring-invoices.pause');
    Route::patch('recurring-invoices/{recurringInvoice}/resume', [RecurringInvoiceController::class, 'resume'])->name('recurring-invoices.resume');
    Route::post('recurring-invoices/{recurringInvoice}/generate', [RecurringInvoiceController::class, 'generateNow'])->name('recurring-invoices.generate');
    Route::delete('recurring-invoices/{recurringInvoice}', [RecurringInvoiceController::class, 'destroy'])->name('recurring-invoices.destroy');

    // Credit Notes
    Route::get('credit-notes', [CreditNoteController::class, 'index'])->name('credit-notes.index');
    Route::get('credit-notes/create', [CreditNoteController::class, 'create'])->name('credit-notes.create');
    Route::post('credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');
    Route::get('credit-notes/{creditNote}', [CreditNoteController::class, 'show'])->name('credit-notes.show');
    Route::patch('credit-notes/{creditNote}/issue', [CreditNoteController::class, 'issue'])->name('credit-notes.issue');
    Route::patch('credit-notes/{creditNote}/apply', [CreditNoteController::class, 'apply'])->name('credit-notes.apply');
    Route::patch('credit-notes/{creditNote}/cancel', [CreditNoteController::class, 'cancel'])->name('credit-notes.cancel');
    Route::delete('credit-notes/{creditNote}', [CreditNoteController::class, 'destroy'])->name('credit-notes.destroy');

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
    Route::get('reports/customer-statement', [ReportController::class, 'customerStatementIndex'])->name('reports.customer-statement.index');
    Route::get('reports/customer-statement/{contact}', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
});
