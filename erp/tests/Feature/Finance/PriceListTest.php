<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\PriceList;
use App\Modules\Finance\Models\PriceListItem;
use App\Modules\Inventory\Models\Product;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Price Co', 'slug' => 'price-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
});

test('price lists index renders', function () {
    $this->get('/finance/price-lists')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/PriceLists/Index'));
});

test('can create a price list', function () {
    $this->post('/finance/price-lists', [
        'name'             => 'VIP Pricing',
        'currency_code'    => 'USD',
        'discount_percent' => 10,
        'is_active'        => true,
    ])->assertSessionHasNoErrors();

    expect(PriceList::where('name', 'VIP Pricing')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('can create price list with product-specific overrides', function () {
    $product = Product::create([
        'tenant_id'  => $this->tenant->id,
        'sku'        => 'PL-01',
        'name'       => 'Widget',
        'cost_price' => 5,
        'sale_price' => 20,
    ]);

    $this->post('/finance/price-lists', [
        'name'             => 'Partner Pricing',
        'currency_code'    => 'USD',
        'discount_percent' => 0,
        'is_active'        => true,
        'items'            => [
            ['product_id' => $product->id, 'unit_price' => 15],
        ],
    ])->assertSessionHasNoErrors();

    $list = PriceList::where('name', 'Partner Pricing')->first();
    expect($list->items()->count())->toBe(1);
    expect((float) $list->items()->first()->unit_price)->toBe(15.0);
});

test('priceFor returns product-specific override', function () {
    $product = Product::create([
        'tenant_id'  => $this->tenant->id,
        'sku'        => 'PL-02',
        'name'       => 'Override',
        'cost_price' => 5,
        'sale_price' => 100,
    ]);
    $list = PriceList::create([
        'tenant_id'        => $this->tenant->id,
        'name'             => 'Override List',
        'currency_code'    => 'USD',
        'discount_percent' => 0,
    ]);
    PriceListItem::create([
        'price_list_id' => $list->id,
        'product_id'    => $product->id,
        'unit_price'    => 75,
    ]);

    expect(PriceList::priceFor($list->id, $product->id, 100.0))->toBe(75.0);
});

test('priceFor applies global discount when no override', function () {
    $product = Product::create([
        'tenant_id'  => $this->tenant->id,
        'sku'        => 'PL-03',
        'name'       => 'Discount',
        'cost_price' => 5,
        'sale_price' => 100,
    ]);
    $list = PriceList::create([
        'tenant_id'        => $this->tenant->id,
        'name'             => 'Discount List',
        'currency_code'    => 'USD',
        'discount_percent' => 20,
    ]);

    // 100 * (1 - 0.20) = 80
    expect(PriceList::priceFor($list->id, $product->id, 100.0))->toBe(80.0);
});

test('priceFor returns default price when no list or override', function () {
    $product = Product::create([
        'tenant_id'  => $this->tenant->id,
        'sku'        => 'PL-04',
        'name'       => 'Default',
        'cost_price' => 5,
        'sale_price' => 50,
    ]);
    $list = PriceList::create([
        'tenant_id'        => $this->tenant->id,
        'name'             => 'Empty List',
        'currency_code'    => 'USD',
        'discount_percent' => 0,
    ]);
    expect(PriceList::priceFor($list->id, $product->id, 50.0))->toBe(50.0);
});

test('contact can be assigned a price list', function () {
    $list = PriceList::create([
        'tenant_id'        => $this->tenant->id,
        'name'             => 'Customer List',
        'currency_code'    => 'USD',
        'discount_percent' => 5,
    ]);
    $contact = Contact::create([
        'tenant_id'     => $this->tenant->id,
        'name'          => 'VIP Customer',
        'type'          => 'customer',
        'price_list_id' => $list->id,
    ]);
    expect($contact->price_list_id)->toBe($list->id);
});

test('price for contact endpoint returns correct price', function () {
    $product = Product::create([
        'tenant_id'  => $this->tenant->id,
        'sku'        => 'PL-05',
        'name'       => 'Endpoint',
        'cost_price' => 5,
        'sale_price' => 100,
    ]);
    $list = PriceList::create([
        'tenant_id'        => $this->tenant->id,
        'name'             => 'Endpoint List',
        'currency_code'    => 'USD',
        'discount_percent' => 10,
    ]);
    $contact = Contact::create([
        'tenant_id'     => $this->tenant->id,
        'name'          => 'Endpoint Customer',
        'type'          => 'customer',
        'price_list_id' => $list->id,
    ]);

    $this->getJson("/finance/price-lists/price-for-contact?contact_id={$contact->id}&product_id={$product->id}")
        ->assertJson(['price' => 90.0]);
});

test('staff cannot create price lists', function () {
    $this->actingAs($this->staff)
        ->post('/finance/price-lists', ['name' => 'Staff List', 'currency_code' => 'USD', 'discount_percent' => 0])
        ->assertStatus(403);
});
