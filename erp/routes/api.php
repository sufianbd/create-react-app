<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CrmApiController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\HelpdeskApiController;
use App\Http\Controllers\Api\V1\HrApiController;
use App\Http\Controllers\Api\V1\InventoryApiController;
use App\Http\Controllers\Api\V1\InvoiceApiController;
use App\Http\Controllers\Api\V1\ManufacturingApiController;
use App\Http\Controllers\Api\V1\PosApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth (public)
    Route::post('auth/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',     [AuthController::class, 'me']);

        Route::get('dashboard', [DashboardApiController::class, 'index']);

        // Products
        Route::apiResource('products', ProductApiController::class);

        // Invoices
        Route::put('invoices/{invoice}/status', [InvoiceApiController::class, 'updateStatus']);
        Route::apiResource('invoices', InvoiceApiController::class)->except(['update']);

        // Customers
        Route::apiResource('customers', CustomerApiController::class);

        // Inventory
        Route::get('inventory/stock',     [InventoryApiController::class, 'stock']);
        Route::get('inventory/movements', [InventoryApiController::class, 'movements']);
        Route::post('inventory/adjust',   [InventoryApiController::class, 'adjust']);

        // CRM
        Route::post('crm/leads/{lead}/won',  [CrmApiController::class, 'markWon']);
        Route::post('crm/leads/{lead}/lost', [CrmApiController::class, 'markLost']);
        Route::apiResource('crm/leads', CrmApiController::class);

        // Helpdesk
        Route::post('helpdesk/tickets/{ticket}/reply',   [HelpdeskApiController::class, 'reply']);
        Route::post('helpdesk/tickets/{ticket}/resolve', [HelpdeskApiController::class, 'resolve']);
        Route::apiResource('helpdesk/tickets', HelpdeskApiController::class);

        // HR
        Route::get('hr/employees',      [HrApiController::class, 'employees']);
        Route::get('hr/employees/{id}', [HrApiController::class, 'employee']);
        Route::get('hr/departments',    [HrApiController::class, 'departments']);
        Route::get('hr/leave-requests', [HrApiController::class, 'leaveRequests']);

        // Manufacturing
        Route::put('manufacturing/orders/{order}/status', [ManufacturingApiController::class, 'updateStatus']);
        Route::get('manufacturing/orders/{order}',        [ManufacturingApiController::class, 'show']);
        Route::get('manufacturing/orders',                [ManufacturingApiController::class, 'orders']);
        Route::get('manufacturing/boms',                  [ManufacturingApiController::class, 'boms']);

        // POS
        Route::get('pos/sessions',                  [PosApiController::class, 'sessions']);
        Route::get('pos/sessions/{session}/orders', [PosApiController::class, 'sessionOrders']);
        Route::post('pos/orders',                   [PosApiController::class, 'createOrder']);
        Route::get('pos/orders/{order}',            [PosApiController::class, 'showOrder']);
    });
});
