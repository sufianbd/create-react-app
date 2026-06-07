<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\GoodsReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController
{
    public function index(): Response
    {
        $receipts = GoodsReceipt::with('items')
            ->orderByDesc('receipt_date')
            ->paginate(20);

        return Inertia::render('Inventory/GoodsReceipts/Index', compact('receipts'));
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/GoodsReceipts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_name'      => 'required|string|max:255',
            'receipt_date'       => 'required|date',
            'supplier_reference' => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'warehouse_id'       => 'nullable|exists:warehouses,id',
        ]);

        $data['tenant_id']  = app('tenant')->id;
        $data['created_by'] = auth()->id();

        GoodsReceipt::create($data);

        return redirect()->route('inventory.goods-receipts.index');
    }

    public function show(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load('items.product');
        return Inertia::render('Inventory/GoodsReceipts/Show', ['receipt' => $goodsReceipt]);
    }

    public function edit(GoodsReceipt $goodsReceipt): Response
    {
        return Inertia::render('Inventory/GoodsReceipts/Edit', ['receipt' => $goodsReceipt]);
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $data = $request->validate([
            'supplier_name'      => 'required|string|max:255',
            'receipt_date'       => 'required|date',
            'supplier_reference' => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
        ]);

        $goodsReceipt->update($data);

        return redirect()->route('inventory.goods-receipts.index');
    }

    public function destroy(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $goodsReceipt->delete();
        return redirect()->route('inventory.goods-receipts.index');
    }

    public function confirm(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $goodsReceipt->confirm(auth()->id());
        return redirect()->route('inventory.goods-receipts.index');
    }

    public function post(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $goodsReceipt->post();
        return redirect()->route('inventory.goods-receipts.index');
    }

    public function reject(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $goodsReceipt->reject();
        return redirect()->route('inventory.goods-receipts.index');
    }
}
