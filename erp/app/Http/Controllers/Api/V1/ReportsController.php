<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Bill;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollRun;

class ReportsController extends ApiController
{
    public function financial(Request $request): JsonResponse
    {
        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;
        $year = $request->integer('year', now()->year);

        $invoiceTotals = Invoice::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total')
            ->groupBy('status')
            ->get();

        $monthlyRevenue = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereYear('created_at', $year)
            ->selectRaw("strftime('%m', created_at) as month, SUM(total) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $billTotals = Bill::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->selectRaw('SUM(total) as total_expenses')
            ->first();

        return $this->success([
            'year'            => $year,
            'invoice_summary' => $invoiceTotals,
            'monthly_revenue' => $monthlyRevenue,
            'total_expenses'  => $billTotals?->total_expenses ?? 0,
        ]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;

        $stockValue = Product::where('tenant_id', $tenantId)
            ->selectRaw('COUNT(*) as total_products, SUM(quantity_on_hand * cost_price) as stock_value')
            ->first();

        $lowStock = Product::where('tenant_id', $tenantId)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point')
            ->where('reorder_point', '>', 0)
            ->count();

        $recentMovements = StockMovement::where('tenant_id', $tenantId)
            ->with('product:id,name,sku')
            ->latest()
            ->limit(10)
            ->get(['id', 'product_id', 'type', 'quantity', 'created_at']);

        return $this->success([
            'total_products'   => $stockValue?->total_products ?? 0,
            'stock_value'      => $stockValue?->stock_value ?? 0,
            'low_stock_count'  => $lowStock,
            'recent_movements' => $recentMovements,
        ]);
    }

    public function hr(Request $request): JsonResponse
    {
        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;

        $headcount = Employee::where('tenant_id', $tenantId)
            ->selectRaw('employment_status, COUNT(*) as count')
            ->groupBy('employment_status')
            ->get();

        $payrollSummary = PayrollRun::where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->selectRaw('SUM(total_gross) as total_gross, SUM(total_net) as total_net, COUNT(*) as run_count')
            ->first();

        return $this->success([
            'headcount'       => $headcount,
            'payroll_summary' => $payrollSummary,
        ]);
    }
}
