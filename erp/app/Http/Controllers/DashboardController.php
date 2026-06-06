<?php

namespace App\Http\Controllers;

use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // KPI: revenue this month (invoices issued this month, not cancelled)
        $revenueThisMonth = Invoice::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month)
            ->get()
            ->sum(fn ($inv) => $inv->load('items')->total);

        // KPI: expenses this month (bills issued this month, not cancelled)
        $expensesThisMonth = Bill::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month)
            ->get()
            ->sum(fn ($b) => $b->load('items')->total);

        // KPI: outstanding AR (amount due on unpaid invoices)
        $outstandingAr = Invoice::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with(['items', 'payments'])
            ->get()
            ->sum(fn ($inv) => $inv->amount_due);

        // KPI: outstanding AP (amount due on unpaid bills)
        $outstandingAp = Bill::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with(['items', 'payments'])
            ->get()
            ->sum(fn ($b) => $b->amount_due);

        // Monthly revenue vs expenses — last 12 months
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $label = $date->format('M y');
            $year  = (int) $date->format('Y');
            $month = (int) $date->format('n');

            $rev = Invoice::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->with('items')
                ->get()
                ->sum(fn ($inv) => $inv->total);

            $exp = Bill::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('issue_date', $year)
                ->whereMonth('issue_date', $month)
                ->with('items')
                ->get()
                ->sum(fn ($b) => $b->total);

            $months->push(['month' => $label, 'revenue' => round($rev, 2), 'expenses' => round($exp, 2)]);
        }

        // Recent invoices (last 5)
        $recentInvoices = Invoice::where('tenant_id', $tenantId)
            ->with(['contact', 'items', 'payments'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($inv) => [
                'id'        => $inv->id,
                'number'    => $inv->number,
                'contact'   => $inv->contact?->name,
                'total'     => round($inv->total, 2),
                'amount_due'=> round($inv->amount_due, 2),
                'status'    => $inv->status,
                'issue_date'=> $inv->issue_date,
            ]);

        // Low stock: products where total stock across all warehouses < 10
        $lowStock = Product::where('tenant_id', $tenantId)
            ->with(['stockLevels'])
            ->get()
            ->filter(function ($product) {
                $total = $product->stockLevels->sum('quantity');
                return $total < 10;
            })
            ->take(10)
            ->map(fn ($p) => [
                'id'       => $p->id,
                'sku'      => $p->sku,
                'name'     => $p->name,
                'quantity' => round($p->stockLevels->sum('quantity'), 2),
            ])
            ->values();

        // Overdue invoices count
        $overdueCount = Invoice::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        return Inertia::render('Dashboard', [
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
            ],
            'kpis' => [
                'revenue_this_month'  => round($revenueThisMonth, 2),
                'expenses_this_month' => round($expensesThisMonth, 2),
                'outstanding_ar'      => round($outstandingAr, 2),
                'outstanding_ap'      => round($outstandingAp, 2),
                'overdue_count'       => $overdueCount,
            ],
            'monthly_chart'  => $months->values(),
            'recent_invoices'=> $recentInvoices->values(),
            'low_stock'      => $lowStock,
        ]);
    }
}
