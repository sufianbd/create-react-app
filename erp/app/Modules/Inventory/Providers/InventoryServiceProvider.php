<?php

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetMaintenance;
use App\Modules\Inventory\Models\BinStockLocation;
use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\ProductAttribute;
use App\Modules\Inventory\Models\ProductVariant;
use App\Modules\Inventory\Models\ProductVariantValue;
use App\Modules\Inventory\Policies\ProductVariantPolicy;
use App\Modules\Inventory\Models\ForecastAlert;
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
use App\Modules\Inventory\Models\WarehouseBin;
use App\Modules\Inventory\Models\WarehouseStock;
use App\Modules\Inventory\Models\WarehouseTransfer;
use App\Modules\Inventory\Models\WarehouseZone;
use App\Modules\Inventory\Policies\AssetPolicy;
use App\Modules\Inventory\Policies\ForecastPolicy;
use App\Modules\Inventory\Policies\ProductCategoryPolicy;
use App\Modules\Inventory\Policies\ProductPolicy;
use App\Modules\Inventory\Policies\PurchaseRequisitionPolicy;
use App\Modules\Inventory\Policies\QcPolicy;
use App\Modules\Inventory\Policies\StockAdjustmentPolicy;
use App\Modules\Inventory\Policies\StockTransferPolicy;
use App\Modules\Inventory\Policies\WarehouseBinPolicy;
use App\Modules\Inventory\Policies\WarehouseTransferPolicy;
use App\Modules\Inventory\Models\CostingLayer;
use App\Modules\Inventory\Models\Vehicle;
use App\Modules\Inventory\Models\VehicleLog;
use App\Modules\Inventory\Policies\VehiclePolicy;
use App\Modules\Inventory\Models\ProductCostSnapshot;
use App\Modules\Inventory\Policies\CostingPolicy;
use App\Modules\Inventory\Models\SupplierReview;
use App\Modules\Inventory\Models\SupplierContract;
use App\Modules\Inventory\Models\LotNumber;
use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\PurchaseOrderItem;
use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Inventory\Policies\LotSerialPolicy;
use App\Modules\Inventory\Policies\PurchaseOrderPolicy;
use App\Modules\Inventory\Models\SalesOrder;
use App\Modules\Inventory\Models\SalesOrderItem;
use App\Modules\Inventory\Policies\SalesOrderPolicy;
use App\Modules\Inventory\Policies\SupplierReviewPolicy;
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
        Gate::policy(DemandForecast::class, ForecastPolicy::class);
        Gate::policy(ForecastAlert::class,    ForecastPolicy::class);
        Gate::policy(WarehouseBin::class,         WarehouseBinPolicy::class);
        Gate::policy(WarehouseZone::class,        WarehouseBinPolicy::class);
        Gate::policy(BinStockLocation::class,     WarehouseBinPolicy::class);
        Gate::policy(ProductAttribute::class,     ProductVariantPolicy::class);
        Gate::policy(Vehicle::class,    VehiclePolicy::class);
        Gate::policy(VehicleLog::class, VehiclePolicy::class);
        Gate::policy(ProductVariant::class,       ProductVariantPolicy::class);
        Gate::policy(ProductVariantValue::class,  ProductVariantPolicy::class);
        Gate::policy(SupplierReview::class,   SupplierReviewPolicy::class);
        Gate::policy(SupplierContract::class, SupplierReviewPolicy::class);
        Gate::policy(LotNumber::class,    LotSerialPolicy::class);
        Gate::policy(SerialNumber::class, LotSerialPolicy::class);
        Gate::policy(PurchaseOrder::class,     PurchaseOrderPolicy::class);
        Gate::policy(PurchaseOrderItem::class, PurchaseOrderPolicy::class);
        Gate::policy(SalesOrder::class,     SalesOrderPolicy::class);
        Gate::policy(SalesOrderItem::class, SalesOrderPolicy::class);
    }
}
