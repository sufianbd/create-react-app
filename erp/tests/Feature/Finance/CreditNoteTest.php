<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\CreditNote;
use App\Modules\Finance\Models\CreditNoteItem;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceItem;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant   = Tenant::create(['name' => 'Credit Co', 'slug' => 'credit-co']);
    $this->admin    = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->customer = Contact::create(['tenant_id' => $this->tenant->id, 'name' => 'Test Customer', 'type' => 'customer']);
    $this->staff    = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
});

test('credit notes index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/credit-notes')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/CreditNotes/Index'));
});

test('staff cannot access credit notes index', function () {
    $this->actingAs($this->staff)
        ->get('/finance/credit-notes')
        ->assertStatus(403);
});

test('credit note can be created', function () {
    $this->actingAs($this->admin)
        ->post('/finance/credit-notes', [
            'contact_id' => $this->customer->id,
            'issue_date' => '2026-01-01',
            'items'      => [
                ['description' => 'Refund', 'quantity' => 1, 'unit_price' => 500, 'tax_rate' => 0],
            ],
        ])
        ->assertRedirect();

    expect(CreditNote::where('contact_id', $this->customer->id)->exists())->toBeTrue();
});

test('credit note number is generated on creation', function () {
    $this->actingAs($this->admin)
        ->post('/finance/credit-notes', [
            'issue_date' => '2026-01-01',
            'items'      => [
                ['description' => 'Return', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0],
            ],
        ])
        ->assertRedirect();

    $creditNote = CreditNote::where('tenant_id', $this->tenant->id)->latest()->first();
    expect($creditNote->number)->toStartWith('CN-');
});

test('credit note starts in draft status', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    expect($creditNote->status)->toBe('draft');
});

test('credit note total is calculated correctly', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    CreditNoteItem::create([
        'credit_note_id' => $creditNote->id,
        'description'    => 'Item A',
        'quantity'       => 2,
        'unit_price'     => 100,
        'tax_rate'       => 10,
    ]);

    $creditNote->load(['items']);

    expect($creditNote->subtotal)->toBe(200.0);
    expect($creditNote->tax_total)->toBe(20.0);
    expect($creditNote->total)->toBe(220.0);
});

test('credit note can be created from an invoice', function () {
    $invoice = Invoice::create([
        'tenant_id'  => $this->tenant->id,
        'contact_id' => $this->customer->id,
        'issue_date' => '2026-01-01',
        'status'     => 'sent',
    ]);
    InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Service', 'quantity' => 2, 'unit_price' => 150, 'tax_rate' => 10]);

    $this->actingAs($this->admin)
        ->get("/finance/credit-notes/create?invoice_id={$invoice->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/CreditNotes/Create')->has('sourceInvoice'));

    $this->actingAs($this->admin)
        ->post('/finance/credit-notes', [
            'contact_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
            'issue_date' => '2026-01-02',
            'items'      => [
                ['description' => 'Service', 'quantity' => 2, 'unit_price' => 150, 'tax_rate' => 10],
            ],
        ])
        ->assertRedirect();

    $creditNote = CreditNote::where('tenant_id', $this->tenant->id)->latest()->first();
    expect($creditNote->invoice_id)->toBe($invoice->id);
});

test('credit note transitions to issued via http', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)
        ->patch("/finance/credit-notes/{$creditNote->id}/issue")
        ->assertRedirect();

    expect($creditNote->fresh()->status)->toBe('issued');
});

test('issued credit note can be applied', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
        'status'     => 'issued',
    ]);

    $this->actingAs($this->admin)
        ->patch("/finance/credit-notes/{$creditNote->id}/apply")
        ->assertRedirect();

    expect($creditNote->fresh()->status)->toBe('applied');
});

test('credit note can be cancelled', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)
        ->patch("/finance/credit-notes/{$creditNote->id}/cancel")
        ->assertRedirect();

    expect($creditNote->fresh()->status)->toBe('cancelled');
});

test('draft credit note cannot skip to applied', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    expect(fn () => $creditNote->transitionTo('applied'))->toThrow(\DomainException::class);
});

test('draft credit note can be deleted', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)
        ->delete("/finance/credit-notes/{$creditNote->id}")
        ->assertRedirect('/finance/credit-notes');

    expect(CreditNote::withTrashed()->find($creditNote->id)->deleted_at)->not->toBeNull();
});

test('issued credit note cannot be deleted', function () {
    $creditNote = CreditNote::create([
        'tenant_id'  => $this->tenant->id,
        'issue_date' => now()->toDateString(),
        'status'     => 'issued',
    ]);

    $this->actingAs($this->admin)
        ->delete("/finance/credit-notes/{$creditNote->id}")
        ->assertStatus(403);
});

test('staff cannot create a credit note', function () {
    $this->actingAs($this->staff)
        ->post('/finance/credit-notes', [
            'issue_date' => '2026-01-01',
            'items'      => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10, 'tax_rate' => 0]],
        ])
        ->assertStatus(403);
});

test('guest cannot access credit notes', function () {
    $this->get('/finance/credit-notes')->assertRedirect();
});
