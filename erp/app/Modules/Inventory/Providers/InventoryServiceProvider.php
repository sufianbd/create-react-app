<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetMaintenance;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBundleItem;
use App\Modules\Inventory\Models\ProductCategory;
use App\Modules\Inventory\Models\PurchaseRequisition;
use App\Modules\Inventory\Models\QcChecklist;
use App\Modules\Inventory\Models\QcChecklistItem;
use App\Modules\Inventory\Models\QcInspection;
use App\Modules\Inventory\Models\QcInspectionResult;
use App\Modules\Inventory\Models\StockAdjustment;
use App\Modules\Inventory\Models\StockTransfer;
use App\Modules\Inventory\Models\StockTransferItem;
use App\Modules\Inventory\Models\WarehouseStock;
use App\Modules\Inventory\Models\WarehouseTransfer;
use App\Modules\Inventory\Policies\AssetPolicy;
use App\Modules\Inventory\Policies\ProductCategoryPolicy;
use App\Modules\Inventory\Policies\ProductPolicy;
use App\Modules\Inventory\Policies\PurchaseRequisitionPolicy;
use App\Modules\Inventory\Policies\QcPolicy;
use App\Modules\Inventory\Policies\StockAdjustmentPolicy;
use App\Modules\Inventory\Policies\StockTransferPolicy;
use App\Modules\Inventory\Policies\WarehouseTransferPolicy;
use App\Modules\Inventory\Models\CostingLayer;
use App\Modules\Inventory\Models\ProductCostSnapshot;
use App\Modules\Inventory\Policies\CostingPolicy;
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
        Gate::policy(WarehouseStock::class,    StockTransferPolicy::class);
        Gate::policy(StockTransfer::class,     StockTransferPolicy::class);
        Gate::policy(StockTransferItem::class, StockTransferPolicy::class);
        Gate::policy(QcChecklist::class,        QcPolicy::class);
        Gate::policy(QcChecklistItem::class,    QcPolicy::class);
        Gate::policy(QcInspection::class,       QcPolicy::class);
        Gate::policy(QcInspectionResult::class, QcPolicy::class);
        Gate::policy(CostingLayer::class, CostingPolicy::class);
        Gate::policy(ProductCostSnapshot::class, CostingPolicy::class);
    }
}
