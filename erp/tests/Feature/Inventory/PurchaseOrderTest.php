<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\PurchaseOrder;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\Supplier;
use App\Modules\Inventory\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant    = Tenant::create(['name' => 'PO Co', 'slug' => 'po-co']);
    $this->admin     = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);

    $this->supplier  = Supplier::create(['tenant_id' => $this->tenant->id, 'name' => 'ACME Supplies']);
    $this->warehouse = Warehouse::create(['tenant_id' => $this->tenant->id, 'name' => 'Main WH']);
    $this->product   = Product::create(['tenant_id' => $this->tenant->id, 'sku' => 'PO-SKU-01', 'name' => 'PO Product', 'cost_price' => 15, 'sale_price' => 30]);
});

function makePO($test): PurchaseOrder
{
    return PurchaseOrder::create(['tenant_id' => $test->tenant->id, 'supplier_id' => $test->supplier->id, 'warehouse_id' => $test->warehouse->id, 'created_by' => $test->admin->id]);
}

test('purchase order can be created via http', function () {
    $this->post('/inventory/purchase-orders', [
        'supplier_id'  => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'items'        => [['product_id' => $this->product->id, 'quantity' => 10, 'unit_cost' => 15.00]],
    ])->assertRedirect();

    expect(PurchaseOrder::count())->toBe(1);
    expect(PurchaseOrder::first()->items()->count())->toBe(1);
});

test('purchase order starts in draft status', function () {
    $po = makePO($this);
    expect($po->status)->toBe('draft');
});

test('draft transitions to submitted', function () {
    $po = makePO($this);
    $po->transitionTo('submitted');
    expect($po->fresh()->status)->toBe('submitted');
});

test('submitted transitions to approved', function () {
    $po = makePO($this);
    $po->transitionTo('submitted');
    $po->transitionTo('approved');
    expect($po->fresh()->status)->toBe('approved');
});

test('invalid transition throws domain exception', function () {
    $po = makePO($this);
    expect(fn () => $po->transitionTo('received'))->toThrow(\DomainException::class);
});

test('receiving creates stock movements and sets received status', function () {
    $po   = makePO($this);
    $item = $po->items()->create(['product_id' => $this->product->id, 'quantity' => 20, 'unit_cost' => 15]);
    $po->transitionTo('submitted');
    $po->transitionTo('approved');
    $po->receive([['id' => $item->id, 'received_quantity' => 20]]);

    expect($po->fresh()->status)->toBe('received');

    $level = StockLevel::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->first();
    expect((float) $level->quantity)->toBe(20.0);
});

test('po can be cancelled from draft', function () {
    $po = makePO($this);
    $po->transitionTo('cancelled');
    expect($po->fresh()->status)->toBe('cancelled');
});

test('received po cannot be cancelled', function () {
    $po   = makePO($this);
    $item = $po->items()->create(['product_id' => $this->product->id, 'quantity' => 5, 'unit_cost' => 15]);
    $po->transitionTo('submitted');
    $po->transitionTo('approved');
    $po->receive([['id' => $item->id, 'received_quantity' => 5]]);

    expect(fn () => $po->transitionTo('cancelled'))->toThrow(\DomainException::class);
});

test('po total is calculated from items', function () {
    $po = makePO($this);
    $po->items()->create(['product_id' => $this->product->id, 'quantity' => 4, 'unit_cost' => 25]);
    $po->load('items');

    expect($po->total)->toBe(100.0);
});
