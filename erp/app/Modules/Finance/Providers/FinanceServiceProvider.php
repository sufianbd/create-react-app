<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Policies\AccountPolicy;
use App\Modules\Finance\Policies\BillPolicy;
use App\Modules\Finance\Policies\ContactPolicy;
use App\Modules\Finance\Policies\InvoicePolicy;
use App\Modules\Finance\Policies\JournalEntryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/finance.php');

        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
    }
}
