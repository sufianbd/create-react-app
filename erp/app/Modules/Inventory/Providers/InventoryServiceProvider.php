<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductCategory;
use App\Modules\Inventory\Models\WarehouseTransfer;
use App\Modules\Inventory\Policies\ProductCategoryPolicy;
use App\Modules\Inventory\Policies\ProductPolicy;
use App\Modules\Inventory\Policies\WarehouseTransferPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/inventory.php');

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
        Gate::policy(WarehouseTransfer::class, WarehouseTransferPolicy::class);
    }
}
