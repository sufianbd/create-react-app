<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Models\AuditLog;
use App\Modules\Core\Models\Company;
use App\Modules\Core\Policies\AuditLogPolicy;
use App\Modules\Core\Policies\CompanyPolicy;
use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\HR\Providers\HRServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Modules\Manufacturing\Providers\ManufacturingServiceProvider;
use App\Modules\CRM\Providers\CRMServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(InventoryServiceProvider::class);
        $this->app->register(FinanceServiceProvider::class);
        $this->app->register(HRServiceProvider::class);
        $this->app->register(ManufacturingServiceProvider::class);
        $this->app->register(CRMServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/core.php');
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
    }
}
