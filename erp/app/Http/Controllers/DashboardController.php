<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Finance\Models\Invoice;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\StockLevel;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $stats = [
            'products_count'        => $user->can('inventory.view')  ? Product::count() : null,
            'low_stock_count'       => $user->can('inventory.view')  ? $this->lowStockCount() : null,
            'pending_pos_count'     => $user->can('inventory.view')  ? PurchaseOrder::whereIn('status', ['submitted', 'approved'])->count() : null,
            'open_invoices_count'   => $user->can('finance.view')    ? Invoice::whereIn('status', ['draft', 'sent'])->count() : null,
            'open_invoices_total'   => $user->can('finance.view')    ? $this->openInvoicesTotal() : null,
            'revenue_mtd'           => $user->can('finance.view')    ? $this->revenueMtd() : null,
            'employees_count'       => $user->can('hr.view')         ? Employee::active()->count() : null,
            'pending_leaves_count'  => $user->can('hr.view')         ? LeaveRequest::where('status', 'pending')->count() : null,
            'users_count'           => $user->can('users.view')      ? User::where('tenant_id', $user->tenant_id)->count() : null,
        ];

        $recentInvoices = $user->can('finance.view')
            ? Invoice::with('contact')->latest()->limit(5)->get()->map(fn ($inv) => [
                'id'         => $inv->id,
                'number'     => $inv->number ?? "#{$inv->id}",
                'contact'    => $inv->contact?->name ?? 'No contact',
                'status'     => $inv->status,
                'issue_date' => $inv->issue_date?->toDateString(),
            ])
            : collect();

        $recentPos = $user->can('inventory.view')
            ? PurchaseOrder::with('supplier')->latest()->limit(5)->get()->map(fn ($po) => [
                'id'       => $po->id,
                'supplier' => $po->supplier?->name ?? 'Unknown',
                'status'   => $po->status,
                'date'     => $po->created_at?->toDateString(),
            ])
            : collect();

        return Inertia::render('Dashboard/Index', [
            'stats'          => $stats,
            'recentInvoices' => $recentInvoices,
            'recentPos'      => $recentPos,
            'breadcrumbs'    => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
            ],
        ]);
    }

    private function lowStockCount(): int
    {
        return StockLevel::join('products', 'products.id', '=', 'stock_levels.product_id')
            ->whereColumn('stock_levels.quantity', '<=', 'products.reorder_point')
            ->distinct('products.id')
            ->count('products.id');
    }

    private function openInvoicesTotal(): float
    {
        return Invoice::whereIn('status', ['draft', 'sent'])
            ->with('items')
            ->get()
            ->sum(fn ($inv) => $inv->total);
    }

    private function revenueMtd(): float
    {
        return Invoice::where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->with('items')
            ->get()
            ->sum(fn ($inv) => $inv->total);
    }
}
