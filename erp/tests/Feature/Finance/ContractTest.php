<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Contract;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Contract Co', 'slug' => 'contract-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makeContractContact(): Contact
{
    return Contact::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Contract Client',
        'type'      => 'customer',
    ]);
}

it('admin can list contracts', function () {
    $this->get('/finance/contracts')->assertStatus(200);
});

it('admin can create a contract', function () {
    $contact = makeContractContact();
    $this->post('/finance/contracts', [
        'title'      => 'Service Agreement',
        'type'       => 'client',
        'contact_id' => $contact->id,
        'start_date' => '2025-01-01',
        'end_date'   => '2025-12-31',
    ])->assertRedirect();
    expect(Contract::where('title', 'Service Agreement')->exists())->toBeTrue();
});

it('admin can view a contract', function () {
    $contract = Contract::create([
        'tenant_id' => test()->tenant->id,
        'title'     => 'View Test',
        'type'      => 'nda',
        'status'    => 'draft',
    ]);
    $this->get("/finance/contracts/{$contract->id}")->assertStatus(200);
});

it('admin can update a contract', function () {
    $contract = Contract::create([
        'tenant_id' => test()->tenant->id,
        'title'     => 'Old Title',
        'type'      => 'client',
        'status'    => 'draft',
    ]);
    $this->patch("/finance/contracts/{$contract->id}", [
        'title' => 'New Title',
        'type'  => 'client',
    ])->assertRedirect();
    expect($contract->fresh()->title)->toBe('New Title');
});

it('admin can activate contract', function () {
    $contract = Contract::create([
        'tenant_id' => test()->tenant->id,
        'title'     => 'Activate Test',
        'type'      => 'client',
        'status'    => 'draft',
    ]);
    $this->post("/finance/contracts/{$contract->id}/activate");
    expect($contract->fresh()->status)->toBe('active');
});

it('admin can terminate contract', function () {
    $contract = Contract::create([
        'tenant_id' => test()->tenant->id,
        'title'     => 'Terminate Test',
        'type'      => 'client',
        'status'    => 'active',
    ]);
    $this->post("/finance/contracts/{$contract->id}/terminate");
    expect($contract->fresh()->status)->toBe('terminated');
});

it('is_expiring returns true when within notice period', function () {
    $contract = Contract::create([
        'tenant_id'           => test()->tenant->id,
        'title'               => 'Expiring Soon',
        'type'                => 'client',
        'status'              => 'active',
        'end_date'            => now()->addDays(15)->toDateString(),
        'renewal_notice_days' => 30,
    ]);
    expect($contract->is_expiring)->toBeTrue();
});

it('is_expiring returns false when outside notice period', function () {
    $contract = Contract::create([
        'tenant_id'           => test()->tenant->id,
        'title'               => 'Not Expiring',
        'type'                => 'client',
        'status'              => 'active',
        'end_date'            => now()->addDays(90)->toDateString(),
        'renewal_notice_days' => 30,
    ]);
    expect($contract->is_expiring)->toBeFalse();
});

it('end_date must be after start_date', function () {
    $this->postJson('/finance/contracts', [
        'title'      => 'Bad Dates',
        'type'       => 'client',
        'start_date' => '2025-12-31',
        'end_date'   => '2025-01-01',
    ])->assertStatus(422);
});

it('staff cannot delete contract', function () {
    $contract = Contract::create([
        'tenant_id' => test()->tenant->id,
        'title'     => 'Staff Test',
        'type'      => 'other',
        'status'    => 'draft',
    ]);
    $this->actingAs($this->staff)
        ->delete("/finance/contracts/{$contract->id}")
        ->assertStatus(403);
});
