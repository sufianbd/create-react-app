<?php

use App\Modules\Finance\Http\Controllers\AccountController;
use App\Modules\Finance\Http\Controllers\BudgetController;
use App\Modules\Finance\Http\Controllers\BudgetLineController;
use App\Modules\Finance\Http\Controllers\BankAccountController;
use App\Modules\Finance\Http\Controllers\BankStatementController;
use App\Modules\Finance\Http\Controllers\BillController;
use App\Modules\Finance\Http\Controllers\ContactController;
use App\Modules\Finance\Http\Controllers\CreditNoteController;
use App\Modules\Finance\Http\Controllers\ExchangeRateController;
use App\Modules\Finance\Http\Controllers\InvoiceController;
use App\Modules\Finance\Http\Controllers\JournalEntryController;
use App\Modules\Finance\Http\Controllers\QuoteController;
use App\Modules\Finance\Http\Controllers\ReconciliationController;
use App\Modules\Finance\Http\Controllers\RecurringInvoiceController;
use App\Modules\Finance\Http\Controllers\ReportController;
use App\Modules\Finance\Http\Controllers\SalesOrderController;
use App\Modules\Finance\Http\Controllers\FixedAssetController;
use App\Modules\Finance\Http\Controllers\PriceListController;
use App\Modules\Finance\Http\Controllers\AttachmentController;
use App\Modules\Finance\Http\Controllers\BatchPaymentController;
use App\Modules\Finance\Http\Controllers\DeliveryNoteController;
use App\Modules\Finance\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use App\Modules\Finance\Http\Controllers\VendorProfileController;
use App\Modules\Finance\Http\Controllers\VendorEvaluationController;
use App\Modules\Finance\Http\Controllers\DocumentTemplateController;
use App\Modules\Finance\Http\Controllers\SubscriptionController;
use App\Modules\Finance\Http\Controllers\SubscriptionPlanController;
use App\Modules\Finance\Http\Controllers\CommissionController;
use App\Modules\Finance\Http\Controllers\CommissionRuleController;
use App\Modules\Finance\Http\Controllers\ContractController;

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

    // PDF + Email routes
    Route::get('/invoices/{invoice}/pdf',   [InvoiceController::class, 'pdf'])->name('finance.invoices.pdf');
    Route::post('/invoices/{invoice}/email', [InvoiceController::class, 'email'])->name('finance.invoices.email');
    Route::get('/quotes/{quote}/pdf',       [QuoteController::class, 'pdf'])->name('finance.quotes.pdf');
    Route::post('/quotes/{quote}/email',     [QuoteController::class, 'email'])->name('finance.quotes.email');
    Route::get('/bills/{bill}/pdf',         [BillController::class, 'pdf'])->name('finance.bills.pdf');
    Route::post('/bills/{bill}/email',       [BillController::class, 'email'])->name('finance.bills.email');

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

    // Sales Orders
    Route::get('sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
    Route::get('sales-orders/create', [SalesOrderController::class, 'create'])->name('sales-orders.create');
    Route::post('sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
    Route::get('sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
    Route::patch('sales-orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm');
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm-post');
    Route::post('sales-orders/{salesOrder}/fulfill', [SalesOrderController::class, 'fulfill'])->name('sales-orders.fulfill');
    Route::patch('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel-post');
    Route::post('sales-orders/{salesOrder}/convert', [SalesOrderController::class, 'convertToInvoice'])->name('sales-orders.convert');
    Route::post('sales-orders/{salesOrder}/convert-to-invoice', [SalesOrderController::class, 'convertToInvoice'])->name('sales-orders.convert-to-invoice');
    Route::delete('sales-orders/{salesOrder}', [SalesOrderController::class, 'destroy'])->name('sales-orders.destroy');

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
    Route::resource('credit-notes', CreditNoteController::class)->except(['edit', 'update']);
    Route::post('credit-notes/{creditNote}/issue', [CreditNoteController::class, 'issue'])->name('credit-notes.issue');
    Route::post('credit-notes/{creditNote}/void', [CreditNoteController::class, 'void'])->name('credit-notes.void');

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
    Route::get('reports/customer-statement/export', [ReportController::class, 'exportCustomerStatement'])->name('reports.customer-statement.export');
    Route::get('reports/customer-statement/{contact}', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
    Route::get('reports/supplier-statement', [ReportController::class, 'supplierStatement'])->name('reports.supplier-statement');
    Route::get('reports/supplier-statement/export', [ReportController::class, 'exportSupplierStatement'])->name('reports.supplier-statement.export');
    Route::get('reports/vat-report', [ReportController::class, 'vatReport'])->name('reports.vat-report');
    Route::get('reports/cash-flow-forecast', [ReportController::class, 'cashFlowForecast'])->name('reports.cash-flow-forecast');

    // CSV exports
    Route::get('reports/profit-loss/export',              [ReportController::class, 'exportProfitLoss'])->name('reports.profit-loss.export');
    Route::get('reports/balance-sheet/export',            [ReportController::class, 'exportBalanceSheet'])->name('reports.balance-sheet.export');
    Route::get('reports/aged-receivables/export',         [ReportController::class, 'exportAgedReceivables'])->name('reports.aged-receivables.export');
    Route::get('reports/aged-payables/export',            [ReportController::class, 'exportAgedPayables'])->name('reports.aged-payables.export');
    Route::get('reports/account-ledger/{account}/export', [ReportController::class, 'exportAccountLedger'])->name('reports.account-ledger.export');
    Route::get('reports/vat-report/export',               [ReportController::class, 'exportVatReport'])->name('reports.vat-report.export');
    Route::get('reports/comparative-profit-loss', [ReportController::class, 'comparativeProfitLoss'])->name('reports.comparative-profit-loss');
    Route::get('reports/comparative-profit-loss/export', [ReportController::class, 'exportComparativeProfitLoss'])->name('reports.comparative-profit-loss.export');
    Route::get('reports/cash-flow-forecast/export',       [ReportController::class, 'exportCashFlowForecast'])->name('reports.cash-flow-forecast.export');

    // Exchange Rates — report must come before resource to avoid 'report' being treated as an ID
    Route::get('exchange-rates/report', [ExchangeRateController::class, 'report'])->name('exchange-rates.report');
    Route::resource('exchange-rates', ExchangeRateController::class)->only(['index', 'create', 'store', 'destroy']);

    // Budgets
    Route::post('budgets/{budget}/activate', [BudgetController::class, 'activate'])->name('budgets.activate');
    Route::post('budgets/{budget}/close',    [BudgetController::class, 'close'])->name('budgets.close');
    Route::resource('budgets', BudgetController::class)->except(['edit', 'update']);

    // Budget Lines
    Route::patch('budget-lines/{budgetLine}', [BudgetLineController::class, 'update'])->name('budget-lines.update');
    Route::delete('budget-lines/{budgetLine}', [BudgetLineController::class, 'destroy'])->name('budget-lines.destroy');

    // Bank Accounts
    Route::resource('bank-accounts', BankAccountController::class);
    Route::post('bank-accounts/{bankAccount}/import', [BankStatementController::class, 'import'])->name('bank-accounts.import');

    // Reconciliation
    Route::get('reconciliation',                                 [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('reconciliation/{bankTransaction}/match',        [ReconciliationController::class, 'match'])->name('reconciliation.match');
    Route::post('reconciliation/{bankTransaction}/unmatch',      [ReconciliationController::class, 'unmatch'])->name('reconciliation.unmatch');

    // Fixed Assets
    Route::post('fixed-assets/{fixedAsset}/depreciate', [FixedAssetController::class, 'depreciate'])->name('fixed-assets.depreciate');
    Route::post('fixed-assets/{fixedAsset}/dispose',    [FixedAssetController::class, 'dispose'])->name('fixed-assets.dispose');
    Route::resource('fixed-assets', FixedAssetController::class)->except(['edit', 'update']);

    // Price Lists
    Route::get('price-lists/price-for-contact', [PriceListController::class, 'priceForContact'])->name('price-lists.price-for-contact');
    Route::resource('price-lists', PriceListController::class)->except(['edit']);

    // Projects
    Route::resource('projects', ProjectController::class)->except(['edit']);
    Route::post('projects/{project}/time-entries', [ProjectController::class, 'storeTimeEntry'])->name('projects.time-entries.store');
    Route::post('projects/{project}/mark-billed', [ProjectController::class, 'markBilled'])->name('projects.mark-billed');

    // Batch Payments
    Route::resource('batch-payments', BatchPaymentController::class)->except(['edit', 'update']);

    // File Attachments
    Route::post('attachments/{modelType}/{modelId}', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    // Delivery Notes
    Route::resource('delivery-notes', DeliveryNoteController::class)->except(['edit', 'update']);
    Route::post('delivery-notes/{deliveryNote}/dispatch', [DeliveryNoteController::class, 'dispatch'])->name('delivery-notes.dispatch');
    Route::post('delivery-notes/{deliveryNote}/deliver', [DeliveryNoteController::class, 'deliver'])->name('delivery-notes.deliver');

    // Vendor Profiles (nested under contacts)
    Route::get('vendors/{contact}/profile',  [VendorProfileController::class, 'show'])->name('vendors.profile.show');
    Route::put('vendors/{contact}/profile',  [VendorProfileController::class, 'update'])->name('vendors.profile.update');

    // Vendor Evaluations (nested under contacts)
    Route::get('vendors/{contact}/evaluations',        [VendorEvaluationController::class, 'index'])->name('vendors.evaluations.index');
    Route::post('vendors/{contact}/evaluations',       [VendorEvaluationController::class, 'store'])->name('vendors.evaluations.store');
    Route::delete('vendors/{contact}/evaluations/{evaluation}', [VendorEvaluationController::class, 'destroy'])->name('vendors.evaluations.destroy');

    // Document Templates
    Route::get('document-templates/preview', [DocumentTemplateController::class, 'preview'])->name('document-templates.preview');
    Route::resource('document-templates', DocumentTemplateController::class)->except(['show']);
    Route::get('document-templates/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('document-templates.show');

    // Subscription Plans
    Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['edit', 'update']);

    // Subscriptions
    Route::post('subscriptions/{subscription}/activate',        [SubscriptionController::class, 'activate'])->name('subscriptions.activate');
    Route::post('subscriptions/{subscription}/cancel',          [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{subscription}/pause',           [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::post('subscriptions/{subscription}/generate-invoice',[SubscriptionController::class, 'generateInvoice'])->name('subscriptions.generate-invoice');
    Route::resource('subscriptions', SubscriptionController::class)->except(['edit', 'update']);

    // Customer Portal Token (admin generates token for a contact)
    Route::post('contacts/{contact}/portal-token', [\App\Modules\Finance\Http\Controllers\CustomerPortalController::class, 'generateToken'])->name('contacts.portal-token');


    // Commission Rules
    Route::resource('commission-rules', CommissionRuleController::class)->except(['edit', 'update']);

    // Commissions
    Route::post('commissions/{commission}/approve',  [CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('commissions/{commission}/mark-paid',[CommissionController::class, 'markPaid'])->name('commissions.mark-paid');
    Route::post('commissions/generate',             [CommissionController::class, 'generate'])->name('commissions.generate');
    Route::resource('commissions', CommissionController::class)->except(['edit', 'update']);


    // Contracts
    Route::post('contracts/{contract}/activate',  [ContractController::class, 'activate'])->name('contracts.activate');
    Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
    Route::resource('contracts', ContractController::class)->except(['show']);
    Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');

});