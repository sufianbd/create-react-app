<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\CreditNote;
use App\Modules\Finance\Models\Invoice;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Credit Co', 'slug' => 'credit-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

test('admin can list credit notes', function () {
    $this->get('/finance/credit-notes')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/CreditNotes/Index'));
});

test('admin can view create form', function () {
    $this->get('/finance/credit-notes/create')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/CreditNotes/Create'));
});

test('admin can create credit note with items', function () {
    $this->post('/finance/credit-notes', [
        'reference'      => 'CN-2026-001',
        'type'           => 'sale',
        'issue_date'     => '2026-01-01',
        'currency_code'  => 'USD',
        'exchange_rate'  => 1,
        'items'          => [
            ['description' => 'Refund', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 0],
        ],
    ])->assertRedirect();

    expect(CreditNote::where('reference', 'CN-2026-001')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('credit note totals are computed correctly', function () {
    $this->post('/finance/credit-notes', [
        'reference'      => 'CN-TOTALS-001',
        'type'           => 'sale',
        'issue_date'     => '2026-01-01',
        'currency_code'  => 'USD',
        'exchange_rate'  => 1,
        'items'          => [
            ['description' => 'Item A', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 10],
            ['description' => 'Item B', 'quantity' => 1, 'unit_price' => 50,  'tax_rate' => 0],
        ],
    ])->assertRedirect();

    $cn = CreditNote::where('reference', 'CN-TOTALS-001')->first();
    expect((float) $cn->subtotal)->toBe(250.0);
    expect((float) $cn->tax_total)->toBe(20.0);
    expect((float) $cn->total)->toBe(270.0);
});

test('admin can view credit note', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-VIEW-001',
        'type'          => 'sale',
        'status'        => 'draft',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    $this->get("/finance/credit-notes/{$cn->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/CreditNotes/Show'));
});

test('admin can issue credit note', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-ISSUE-001',
        'type'          => 'sale',
        'status'        => 'draft',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    $this->post("/finance/credit-notes/{$cn->id}/issue")
        ->assertRedirect();

    expect($cn->fresh()->status)->toBe('issued');
});

test('admin can void issued credit note', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-VOID-001',
        'type'          => 'sale',
        'status'        => 'issued',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    $this->post("/finance/credit-notes/{$cn->id}/void")
        ->assertRedirect();

    expect($cn->fresh()->status)->toBe('void');
});

test('admin can delete draft credit note', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-DELETE-001',
        'type'          => 'sale',
        'status'        => 'draft',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    $this->delete("/finance/credit-notes/{$cn->id}")
        ->assertRedirect();

    expect(CreditNote::withTrashed()->find($cn->id)->deleted_at)->not->toBeNull();
});

test('staff cannot delete credit note', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-STAFF-001',
        'type'          => 'sale',
        'status'        => 'draft',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    $this->actingAs($this->staff)
        ->delete("/finance/credit-notes/{$cn->id}")
        ->assertStatus(403);
});

test('credit note belongs to correct tenant', function () {
    $cn = CreditNote::create([
        'tenant_id'     => $this->tenant->id,
        'reference'     => 'CN-TENANT-001',
        'type'          => 'sale',
        'status'        => 'draft',
        'issue_date'    => now()->toDateString(),
        'currency_code' => 'USD',
        'exchange_rate' => 1,
    ]);

    expect($cn->tenant_id)->toBe($this->tenant->id);
});
