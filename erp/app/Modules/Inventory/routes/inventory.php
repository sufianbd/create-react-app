<?php

use App\Modules\Inventory\Http\Controllers\AssetController;
use App\Modules\Inventory\Http\Controllers\CostingController;
use App\Modules\Inventory\Http\Controllers\AssetMaintenanceController;
use App\Modules\Inventory\Http\Controllers\CategoryController;
use App\Modules\Inventory\Http\Controllers\ProductBundleController;
use App\Modules\Inventory\Http\Controllers\ProductCategoryController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Inventory\Http\Controllers\PurchaseOrderController;
use App\Modules\Inventory\Http\Controllers\PurchaseRequisitionController;
use App\Modules\Inventory\Http\Controllers\QcChecklistController;
use App\Modules\Inventory\Http\Controllers\QcInspectionController;
use App\Modules\Inventory\Http\Controllers\ReorderController;
use App\Modules\Inventory\Http\Controllers\StockAdjustmentController;
use App\Modules\Inventory\Http\Controllers\StockMovementController;
use App\Modules\Inventory\Http\Controllers\SupplierController;
use App\Modules\Inventory\Http\Controllers\WarehouseBinController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use App\Modules\Inventory\Http\Controllers\WarehouseTransferController;
use App\Modules\Inventory\Http\Controllers\WarehouseStockController;
use App\Modules\Inventory\Http\Controllers\WarehouseZoneController;
use App\Modules\Inventory\Http\Controllers\DemandForecastController;
use App\Modules\Inventory\Http\Controllers\ProductAttributeController;
use App\Modules\Inventory\Http\Controllers\ProductVariantController;
use App\Modules\Inventory\Http\Controllers\StockTransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('inventory')->name('inventory.')->group(function () {

    // Products
    Route::resource('products', ProductController::class)
        ->names('products');

    // Product Categories (flat, colour-coded)
    Route::resource('product-categories', ProductCategoryController::class)
        ->except(['create', 'edit', 'show'])
        ->names('product-categories');

    // Legacy Categories (hierarchical - index + inline CRUD via back())
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Warehouses (index + inline CRUD)
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

    // Suppliers
    Route::resource('suppliers', SupplierController::class)
        ->except(['show'])
        ->names('suppliers');

    // Stock Movements
    Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::post('stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

    // Purchase Orders
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive-form');
    Route::patch('purchase-orders/{purchaseOrder}/transition', [PurchaseOrderController::class, 'transition'])->name('purchase-orders.transition');
    Route::post('purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

    // Reorder Suggestions
    Route::get('reorder', [ReorderController::class, 'index'])->name('reorder.index');
    Route::post('reorder/purchase-order', [ReorderController::class, 'createPurchaseOrder'])->name('reorder.create-po');

    // Warehouse Transfers
    Route::resource('warehouse-transfers', WarehouseTransferController::class)->only(['index', 'create', 'store']);

    // Stock Adjustments
    Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['edit', 'update']);
    Route::post('stock-adjustments/{stockAdjustment}/confirm', [StockAdjustmentController::class, 'confirm'])->name('stock-adjustments.confirm');
    Route::post('stock-adjustments/{stockAdjustment}/cancel', [StockAdjustmentController::class, 'cancel'])->name('stock-adjustments.cancel');

    // Purchase Requisitions
    Route::resource('purchase-requisitions', PurchaseRequisitionController::class)->except(['edit', 'update']);
    Route::post('purchase-requisitions/{purchaseRequisition}/submit', [PurchaseRequisitionController::class, 'submit'])->name('purchase-requisitions.submit');
    Route::post('purchase-requisitions/{purchaseRequisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('purchase-requisitions.approve');
    Route::post('purchase-requisitions/{purchaseRequisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('purchase-requisitions.reject');

    // Assets
    Route::post('assets/{asset}/dispose', [AssetController::class, 'dispose'])->name('assets.dispose');
    Route::patch('assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::resource('assets', AssetController::class)->except(['edit', 'update']);

    // Asset Maintenances
    Route::patch('asset-maintenances/{assetMaintenance}/complete', [AssetMaintenanceController::class, 'complete'])->name('asset-maintenances.complete');
    Route::resource('asset-maintenances', AssetMaintenanceController::class)->except(['edit', 'update']);

    // Product Bundles
    Route::post('product-bundles/{productBundle}/items', [ProductBundleController::class, 'addItem'])->name('product-bundles.items.add');
    Route::delete('product-bundles/{productBundle}/items/{item}', [ProductBundleController::class, 'removeItem'])->name('product-bundles.items.remove');
    Route::resource('product-bundles', ProductBundleController::class)
        ->except(['edit', 'update'])
        ->parameters(['product-bundles' => 'productBundle']);

    // Warehouse Stock
    Route::resource('warehouse-stock', WarehouseStockController::class)->only(['index', 'show', 'update']);

    // Stock Transfers
    Route::post('stock-transfers/{stockTransfer}/complete', [StockTransferController::class, 'complete'])->name('stock-transfers.complete');
    Route::post('stock-transfers/{stockTransfer}/cancel',   [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel');
    Route::resource('stock-transfers', StockTransferController::class)->except(['edit', 'update']);

    // QC Checklists
    Route::resource('qc-checklists', QcChecklistController::class)->except(['edit', 'update']);

    // QC Inspections
    Route::patch('qc-inspections/{qcInspection}/results/{result}', [QcInspectionController::class, 'updateResult'])->name('qc-inspections.results.update');
    Route::post('qc-inspections/{qcInspection}/complete', [QcInspectionController::class, 'complete'])->name('qc-inspections.complete');
    Route::resource('qc-inspections', QcInspectionController::class)->except(['edit', 'update']);

    // Inventory Costing
    Route::get('costing',                         [CostingController::class, 'index'])->name('costing.index');
    Route::get('costing/report',                  [CostingController::class, 'report'])->name('costing.report');
    Route::get('costing/{product}/layers',        [CostingController::class, 'layers'])->name('costing.layers');
    Route::post('costing/add-layer',              [CostingController::class, 'addLayer'])->name('costing.add-layer');
    Route::post('costing/snapshot',               [CostingController::class, 'snapshot'])->name('costing.snapshot');

    // Warehouse Zones
    Route::get('warehouse-zones',                    [WarehouseZoneController::class, 'index'])->name('warehouse-zones.index');
    Route::post('warehouse-zones',                   [WarehouseZoneController::class, 'store'])->name('warehouse-zones.store');
    Route::delete('warehouse-zones/{warehouseZone}', [WarehouseZoneController::class, 'destroy'])->name('warehouse-zones.destroy');

    // Warehouse Bins
    Route::post('warehouse-bins/{warehouseBin}/stock',              [WarehouseBinController::class, 'addStock'])->name('warehouse-bins.stock.add');
    Route::delete('warehouse-bins/{warehouseBin}/stock/{location}', [WarehouseBinController::class, 'removeStock'])->name('warehouse-bins.stock.remove');
    Route::resource('warehouse-bins', WarehouseBinController::class)->except(['edit', 'update']);

    // Product Attributes
    Route::resource('product-attributes', ProductAttributeController::class)->except(['show', 'create', 'edit']);

    // Product Variants
    Route::patch('product-variants/{productVariant}/adjust-stock', [ProductVariantController::class, 'adjustStock'])->name('product-variants.adjust-stock');
    Route::resource('product-variants', ProductVariantController::class)->except(['edit', 'update']);
});

// Demand Forecasting - custom actions BEFORE resource
Route::middleware(['web', 'auth', 'verified'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('demand-forecasts/alerts',                      [DemandForecastController::class, 'alerts'])->name('demand-forecasts.alerts');
    Route::post('demand-forecasts/generate',                   [DemandForecastController::class, 'generateForecast'])->name('demand-forecasts.generate');
    Route::post('demand-forecasts/alerts/{alert}/resolve',     [DemandForecastController::class, 'resolveAlert'])->name('demand-forecasts.alerts.resolve');
    Route::resource('demand-forecasts', DemandForecastController::class)->except(['edit']);
});
