<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBundleItem;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'BundleCorp', 'slug' => 'bundle-corp-' . uniqid()]);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makePBundleProduct(): Product
{
    return Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Component ' . uniqid(),
        'sku'       => 'SKU-' . strtoupper(substr(uniqid(), -6)),
        'is_active' => true,
    ]);
}

function makeBundleProduct(): Product
{
    return Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Bundle ' . uniqid(),
        'is_bundle' => true,
        'is_active' => true,
    ]);
}

it('admin can list product bundles', function () {
    makeBundleProduct();
    $this->get('/inventory/product-bundles')->assertOk();
});

it('admin can create a bundle with components', function () {
    $c1 = makePBundleProduct();
    $c2 = makePBundleProduct();

    $this->post('/inventory/product-bundles', [
        'name'  => 'Starter Kit',
        'items' => [
            ['component_product_id' => $c1->id, 'quantity' => 2],
            ['component_product_id' => $c2->id, 'quantity' => 1],
        ],
    ])->assertRedirect();

    $bundle = Product::where('name', 'Starter Kit')->first();
    expect($bundle)->not->toBeNull();
    expect($bundle->is_bundle)->toBeTrue();
    expect($bundle->bundleItems()->count())->toBe(2);
});

it('admin can view bundle', function () {
    $bundle = makeBundleProduct();
    $c1     = makePBundleProduct();
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c1->id,
        'quantity'             => 1,
    ]);
    $this->get("/inventory/product-bundles/{$bundle->id}")->assertOk();
});

it('admin can add item to bundle', function () {
    $bundle = makeBundleProduct();
    $c      = makePBundleProduct();

    $this->post("/inventory/product-bundles/{$bundle->id}/items", [
        'component_product_id' => $c->id,
        'quantity'             => 3,
    ])->assertRedirect();

    expect($bundle->bundleItems()->count())->toBe(1);
});

it('admin can remove item from bundle', function () {
    $bundle = makeBundleProduct();
    $c      = makePBundleProduct();
    $item   = ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 1,
    ]);

    $this->delete("/inventory/product-bundles/{$bundle->id}/items/{$item->id}")->assertRedirect();
    expect(ProductBundleItem::find($item->id))->toBeNull();
});

it('bundle items must have at least one item', function () {
    $this->postJson('/inventory/product-bundles', [
        'name'  => 'Empty Bundle',
        'items' => [],
    ])->assertStatus(422);
});

it('staff cannot delete bundle', function () {
    $bundle = makeBundleProduct();
    $this->actingAs($this->staff)
        ->delete("/inventory/product-bundles/{$bundle->id}")
        ->assertStatus(403);
});

it('duplicate component in bundle is rejected', function () {
    $bundle = makeBundleProduct();
    $c      = makePBundleProduct();
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 1,
    ]);

    $this->postJson("/inventory/product-bundles/{$bundle->id}/items", [
        'component_product_id' => $c->id,
        'quantity'             => 2,
    ])->assertStatus(422);
});

it('stock_sufficient_for_bundle returns true when stock available', function () {
    $bundle = makeBundleProduct();
    $c      = Product::create([
        'tenant_id'      => test()->tenant->id,
        'name'           => 'Sufficient Part',
        'is_active'      => true,
        'stock_quantity' => 20,
    ]);
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 5,
    ]);
    $bundle->load('bundleItems.componentProduct');
    expect($bundle->stock_sufficient_for_bundle)->toBeTrue();
});
