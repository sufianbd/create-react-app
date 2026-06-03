<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankTransaction;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\CreditNote;
use App\Modules\Finance\Models\DeliveryNote;
use App\Modules\Finance\Models\ExchangeRate;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\Quote;
use App\Modules\Finance\Models\RecurringInvoice;
use App\Modules\Finance\Models\SalesOrder;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\PriceList;
use App\Modules\Finance\Models\DepreciationEntry;
use App\Modules\Finance\Models\FixedAsset;
use App\Modules\Finance\Models\Attachment;
use App\Modules\Finance\Models\BatchPayment;
use App\Modules\Finance\Models\Project;
use App\Modules\Finance\Policies\AttachmentPolicy;
use App\Modules\Finance\Policies\BatchPaymentPolicy;
use App\Modules\Finance\Policies\AccountPolicy;
use App\Modules\Finance\Policies\DeliveryNotePolicy;
use App\Modules\Finance\Policies\PriceListPolicy;
use App\Modules\Finance\Policies\ProjectPolicy;
use App\Modules\Finance\Policies\BudgetPolicy;
use App\Modules\Finance\Policies\FixedAssetPolicy;
use App\Modules\Finance\Policies\BankAccountPolicy;
use App\Modules\Finance\Policies\BankTransactionPolicy;
use App\Modules\Finance\Policies\BillPolicy;
use App\Modules\Finance\Policies\ContactPolicy;
use App\Modules\Finance\Policies\CreditNotePolicy;
use App\Modules\Finance\Policies\ExchangeRatePolicy;
use App\Modules\Finance\Policies\InvoicePolicy;
use App\Modules\Finance\Policies\JournalEntryPolicy;
use App\Modules\Finance\Policies\QuotePolicy;
use App\Modules\Finance\Policies\RecurringInvoicePolicy;
use App\Modules\Finance\Policies\SalesOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/finance.php');

        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(DeliveryNote::class, DeliveryNotePolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
        Gate::policy(CreditNote::class, CreditNotePolicy::class);
        Gate::policy(RecurringInvoice::class, RecurringInvoicePolicy::class);
        Gate::policy(SalesOrder::class, SalesOrderPolicy::class);
        Gate::policy(ExchangeRate::class, ExchangeRatePolicy::class);
        Gate::policy(BankAccount::class, BankAccountPolicy::class);
        Gate::policy(BankTransaction::class, BankTransactionPolicy::class);
        Gate::policy(FixedAsset::class, FixedAssetPolicy::class);
        Gate::policy(DepreciationEntry::class, FixedAssetPolicy::class);
        Gate::policy(PriceList::class, PriceListPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(BatchPayment::class, BatchPaymentPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([\App\Modules\Finance\Console\Commands\GenerateRecurringInvoices::class]);
        }
    }
}
