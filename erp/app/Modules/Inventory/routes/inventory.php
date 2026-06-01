<?php

use App\Modules\Inventory\Http\Controllers\CategoryController;
use App\Modules\Inventory\Http\Controllers\ProductCategoryController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Inventory\Http\Controllers\PurchaseOrderController;
use App\Modules\Inventory\Http\Controllers\ReorderController;
use App\Modules\Inventory\Http\Controllers\StockMovementController;
use App\Modules\Inventory\Http\Controllers\SupplierController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use App\Modules\Inventory\Http\Controllers\WarehouseTransferController;
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
});
