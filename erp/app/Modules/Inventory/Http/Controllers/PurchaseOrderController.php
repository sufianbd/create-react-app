<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\ReceivePurchaseOrderRequest;
use App\Modules\Inventory\Http\Requests\StorePurchaseOrderRequest;
use App\Modules\Inventory\Http\Resources\PurchaseOrderResource;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\Supplier;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Inventory/PurchaseOrders/Index', [
            'orders'      => PurchaseOrderResource::collection($orders),
            'suppliers'   => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters'     => $request->only(['status', 'supplier_id']),
            'breadcrumbs' => [
                ['label' => 'Inventory'],
                ['label' => 'Purchase Orders', 'href' => route('inventory.purchase-orders.index')],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/PurchaseOrders/Create', [
            'suppliers'   => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'warehouses'  => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products'    => Product::active()->with('uom')->orderBy('name')
                ->get(['id', 'name', 'sku', 'cost_price', 'uom_id']),
            'breadcrumbs' => [
                ['label' => 'Inventory'],
                ['label' => 'Purchase Orders', 'href' => route('inventory.purchase-orders.index')],
                ['label' => 'New Order'],
            ],
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $po = PurchaseOrder::create([
            'tenant_id'     => auth()->user()->tenant_id,
            'supplier_id'   => $data['supplier_id'],
            'warehouse_id'  => $data['warehouse_id'],
            'expected_date' => $data['expected_date'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $po->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_cost'  => $item['unit_cost'],
            ]);
        }

        return redirect()->route('inventory.purchase-orders.show', $po)
            ->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'creator']);

        return Inertia::render('Inventory/PurchaseOrders/Show', [
            'order'       => new PurchaseOrderResource($purchaseOrder),
            'transitions' => $purchaseOrder->availableTransitions(),
            'breadcrumbs' => [
                ['label' => 'Inventory'],
                ['label' => 'Purchase Orders', 'href' => route('inventory.purchase-orders.index')],
                ['label' => "PO #{$purchaseOrder->id}"],
            ],
        ]);
    }

    public function receiveForm(PurchaseOrder $purchaseOrder): Response
    {
        if (! $purchaseOrder->canTransitionTo('received')) {
            return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
                ->withErrors(['status' => 'This purchase order cannot be received in its current status.']);
        }

        $purchaseOrder->load(['supplier', 'warehouse', 'items.product']);

        return Inertia::render('Inventory/PurchaseOrders/Receive', [
            'order'       => new PurchaseOrderResource($purchaseOrder),
            'breadcrumbs' => [
                ['label' => 'Inventory'],
                ['label' => 'Purchase Orders', 'href' => route('inventory.purchase-orders.index')],
                ['label' => "PO-" . str_pad($purchaseOrder->id, 4, '0', STR_PAD_LEFT), 'href' => route('inventory.purchase-orders.show', $purchaseOrder)],
                ['label' => 'Receive Items'],
            ],
        ]);
    }

    public function transition(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', 'string']])['status'];

        try {
            if ($status === 'received') {
                $lines = $purchaseOrder->items->map(fn ($i) => [
                    'id'                => $i->id,
                    'received_quantity' => $i->quantity,
                ])->all();
                $purchaseOrder->receive($lines);
            } else {
                $purchaseOrder->transitionTo($status);
            }
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Order {$status}.");
    }

    public function submit(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $purchaseOrder->transitionTo('submitted');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Purchase order submitted.');
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $purchaseOrder->transitionTo('approved');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Purchase order approved.');
    }

    public function receive(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $purchaseOrder->receive($request->validated()['lines']);
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Items received and stock updated.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $purchaseOrder->transitionTo('cancelled');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Purchase order cancelled.');
    }
}
