<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBundleItem;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Bundle Co', 'slug' => 'bundle-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makeComponent(string $name, float $stock = 10): Product
{
    return Product::create([
        'tenant_id'      => test()->tenant->id,
        'name'           => $name,
        'sku'            => 'SKU-' . $name,
        'stock_quantity' => $stock,
        'is_active'      => true,
    ]);
}

it('admin can list product bundles', function () {
    $this->get('/inventory/product-bundles')->assertStatus(200);
});

it('admin can create a bundle with components', function () {
    $c1 = makeComponent('Widget A');
    $c2 = makeComponent('Widget B');

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
    $c1 = makeComponent('Component X');
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Test Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c1->id,
        'quantity'             => 1,
    ]);
    $this->get("/inventory/product-bundles/{$bundle->id}")->assertStatus(200);
});

it('admin can add item to bundle', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Add Item Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $c = makeComponent('Added Component');
    $this->post("/inventory/product-bundles/{$bundle->id}/items", [
        'component_product_id' => $c->id,
        'quantity'             => 3,
    ])->assertRedirect();
    expect($bundle->bundleItems()->count())->toBe(1);
});

it('admin can remove item from bundle', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Remove Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $c = makeComponent('Removed Component');
    $item = ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 1,
    ]);
    $this->delete("/inventory/product-bundles/{$bundle->id}/items/{$item->id}")->assertRedirect();
    expect(ProductBundleItem::find($item->id))->toBeNull();
});

it('stock_sufficient_for_bundle returns true when all stock available', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Sufficient Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $c = makeComponent('Sufficient Part', 20);
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 5,
    ]);
    $bundle->load('bundleItems.componentProduct');
    expect($bundle->stock_sufficient_for_bundle)->toBeTrue();
});

it('stock_sufficient_for_bundle returns false when insufficient stock', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Insufficient Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $c = makeComponent('Scarce Part', 2);
    ProductBundleItem::create([
        'tenant_id'            => test()->tenant->id,
        'bundle_product_id'    => $bundle->id,
        'component_product_id' => $c->id,
        'quantity'             => 10,
    ]);
    $bundle->load('bundleItems.componentProduct');
    expect($bundle->stock_sufficient_for_bundle)->toBeFalse();
});

it('bundle items must have at least one item', function () {
    $this->postJson('/inventory/product-bundles', [
        'name'  => 'Empty Bundle',
        'items' => [],
    ])->assertStatus(422);
});

it('staff cannot delete bundle', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Staff Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $this->actingAs($this->staff)
        ->delete("/inventory/product-bundles/{$bundle->id}")
        ->assertStatus(403);
});

it('duplicate component in bundle is rejected', function () {
    $bundle = Product::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Dup Bundle',
        'is_bundle' => true,
        'is_active' => true,
    ]);
    $c = makeComponent('Dup Component');
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
