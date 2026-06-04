<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetMaintenance;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBundleItem;
use App\Modules\Inventory\Models\ProductCategory;
use App\Modules\Inventory\Models\PurchaseRequisition;
use App\Modules\Inventory\Models\StockAdjustment;
use App\Modules\Inventory\Models\WarehouseTransfer;
use App\Modules\Inventory\Policies\AssetPolicy;
use App\Modules\Inventory\Policies\ProductCategoryPolicy;
use App\Modules\Inventory\Policies\ProductPolicy;
use App\Modules\Inventory\Policies\PurchaseRequisitionPolicy;
use App\Modules\Inventory\Policies\StockAdjustmentPolicy;
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
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(PurchaseRequisition::class, PurchaseRequisitionPolicy::class);
        Gate::policy(Asset::class,            AssetPolicy::class);
        Gate::policy(AssetMaintenance::class, AssetPolicy::class);
        Gate::policy(ProductBundleItem::class, ProductPolicy::class);
    }
}
