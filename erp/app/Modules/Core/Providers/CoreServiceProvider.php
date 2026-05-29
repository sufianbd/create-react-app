<?php

namespace App\Modules\Core\Providers;

use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\HR\Providers\HRServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(InventoryServiceProvider::class);
        $this->app->register(FinanceServiceProvider::class);
        $this->app->register(HRServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/core.php');
    }
}
