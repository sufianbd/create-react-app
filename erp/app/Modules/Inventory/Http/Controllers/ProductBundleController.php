<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBundleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductBundleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);
        $bundles = Product::with('bundleItems.componentProduct')
            ->where('tenant_id', app('tenant')->id)
            ->where('is_bundle', true)
            ->paginate(20);
        return Inertia::render('Inventory/ProductBundles/Index', compact('bundles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'sku'                          => 'nullable|string|max:100',
            'description'                  => 'nullable|string',
            'selling_price'                => 'nullable|numeric|min:0',
            'items'                        => 'required|array|min:1',
            'items.*.component_product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity'             => 'required|numeric|min:0.0001',
        ]);

        $bundle = Product::create([
            'tenant_id'   => app('tenant')->id,
            'name'        => $validated['name'],
            'sku'         => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'sale_price'  => $validated['selling_price'] ?? 0,
            'cost_price'  => 0,
            'is_bundle'   => true,
            'is_active'   => true,
        ]);

        foreach ($validated['items'] as $item) {
            ProductBundleItem::create([
                'tenant_id'            => app('tenant')->id,
                'bundle_product_id'    => $bundle->id,
                'component_product_id' => $item['component_product_id'],
                'quantity'             => $item['quantity'],
            ]);
        }

        return redirect()->route('inventory.product-bundles.show', $bundle)
            ->with('success', 'Bundle created.');
    }

    public function show(Product $productBundle): Response
    {
        $this->authorize('view', $productBundle);
        $productBundle->load('bundleItems.componentProduct');
        return Inertia::render('Inventory/ProductBundles/Show', ['bundle' => $productBundle]);
    }

    public function addItem(Request $request, Product $productBundle): RedirectResponse
    {
        $this->authorize('update', $productBundle);
        $validated = $request->validate([
            'component_product_id' => [
                'required',
                Rule::exists('products', 'id'),
                Rule::unique('product_bundle_items')
                    ->where(fn ($q) => $q->where('bundle_product_id', $productBundle->id)),
            ],
            'quantity' => 'required|numeric|min:0.0001',
        ]);

        ProductBundleItem::create([
            'tenant_id'            => app('tenant')->id,
            'bundle_product_id'    => $productBundle->id,
            'component_product_id' => $validated['component_product_id'],
            'quantity'             => $validated['quantity'],
        ]);

        return redirect()->route('inventory.product-bundles.show', $productBundle)
            ->with('success', 'Component added.');
    }

    public function removeItem(Product $productBundle, ProductBundleItem $item): RedirectResponse
    {
        $this->authorize('delete', $productBundle);
        $item->delete();
        return redirect()->route('inventory.product-bundles.show', $productBundle)
            ->with('success', 'Component removed.');
    }

    public function destroy(Product $productBundle): RedirectResponse
    {
        $this->authorize('delete', $productBundle);
        $productBundle->delete();
        return redirect()->route('inventory.product-bundles.index')
            ->with('success', 'Bundle deleted.');
    }
}
