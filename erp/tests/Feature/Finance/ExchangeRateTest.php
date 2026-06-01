<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\ExchangeRate;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceItem;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'FX Co', 'slug' => 'fx-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
});

test('exchange rates index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/exchange-rates')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/ExchangeRates/Index'));
});

test('can create an exchange rate', function () {
    $this->actingAs($this->admin)
        ->post('/finance/exchange-rates', [
            'currency_code' => 'EUR',
            'rate'          => 1.08,
            'date'          => '2026-06-01',
        ])
        ->assertSessionHasNoErrors();

    expect(ExchangeRate::where('currency_code', 'EUR')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('duplicate date+currency upserts the rate', function () {
    $this->actingAs($this->admin)
        ->post('/finance/exchange-rates', ['currency_code' => 'GBP', 'rate' => 1.25, 'date' => '2026-06-01'])
        ->assertSessionHasNoErrors();
    $this->actingAs($this->admin)
        ->post('/finance/exchange-rates', ['currency_code' => 'GBP', 'rate' => 1.27, 'date' => '2026-06-01'])
        ->assertSessionHasNoErrors();

    expect(ExchangeRate::where('currency_code', 'GBP')->where('tenant_id', $this->tenant->id)->count())->toBe(1);
    expect((float) ExchangeRate::where('currency_code', 'GBP')->where('tenant_id', $this->tenant->id)->first()->rate)->toBe(1.27);
});

test('staff cannot create exchange rate', function () {
    $this->actingAs($this->staff)
        ->post('/finance/exchange-rates', ['currency_code' => 'EUR', 'rate' => 1.08, 'date' => '2026-06-01'])
        ->assertStatus(403);
});

test('invoice stores currency and exchange rate', function () {
    $contact = Contact::create(['tenant_id' => $this->tenant->id, 'name' => 'C', 'type' => 'customer']);

    $this->actingAs($this->admin)
        ->post('/finance/invoices', [
            'contact_id'    => $contact->id,
            'issue_date'    => '2026-06-01',
            'currency_code' => 'EUR',
            'exchange_rate' => 1.08,
            'items'         => [['description' => 'Svc', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]],
        ])
        ->assertSessionHasNoErrors();

    $invoice = Invoice::where('tenant_id', $this->tenant->id)->latest()->first();
    expect($invoice->currency_code)->toBe('EUR');
    expect((float) $invoice->exchange_rate)->toBe(1.08);
});

test('invoice base_total converts correctly', function () {
    $contact = Contact::create(['tenant_id' => $this->tenant->id, 'name' => 'C2', 'type' => 'customer']);
    $invoice = Invoice::create([
        'tenant_id'     => $this->tenant->id,
        'contact_id'    => $contact->id,
        'issue_date'    => '2026-06-01',
        'status'        => 'draft',
        'currency_code' => 'EUR',
        'exchange_rate' => 1.08,
    ]);
    InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'S', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]);
    $invoice->load('items');
    expect($invoice->base_total)->toBe(108.0);
});

test('rateFor returns 1.0 for USD', function () {
    expect(ExchangeRate::rateFor('USD', $this->tenant->id, '2026-06-01'))->toBe(1.0);
});

test('rateFor returns latest rate on or before date', function () {
    ExchangeRate::create(['tenant_id' => $this->tenant->id, 'currency_code' => 'JPY', 'rate' => 0.0068, 'date' => '2026-05-01']);
    ExchangeRate::create(['tenant_id' => $this->tenant->id, 'currency_code' => 'JPY', 'rate' => 0.0069, 'date' => '2026-06-01']);

    expect(ExchangeRate::rateFor('JPY', $this->tenant->id, '2026-05-15'))->toBe(0.0068);
    expect(ExchangeRate::rateFor('JPY', $this->tenant->id, '2026-06-01'))->toBe(0.0069);
});

test('exchange rate can be deleted', function () {
    $rate = ExchangeRate::create(['tenant_id' => $this->tenant->id, 'currency_code' => 'CHF', 'rate' => 1.12, 'date' => '2026-06-01']);

    $this->actingAs($this->admin)
        ->delete("/finance/exchange-rates/{$rate->id}")
        ->assertSessionHasNoErrors();

    expect(ExchangeRate::find($rate->id))->toBeNull();
});

test('guest cannot access exchange rates', function () {
    $this->get('/finance/exchange-rates')->assertRedirect();
});
