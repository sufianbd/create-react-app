<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/inventory.php');

        Gate::policy(Product::class, ProductPolicy::class);
    }
}
