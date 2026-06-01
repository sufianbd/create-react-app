<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\ExpenseClaim;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant  = Tenant::create(['name' => 'Expense Co', 'slug' => 'expense-co']);
    $this->admin   = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->manager = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->manager->assignRole('manager');
    $this->staff   = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
});

function makeEmployee(int $tenantId): Employee
{
    return Employee::create([
        'tenant_id'       => $tenantId,
        'first_name'      => 'Test',
        'last_name'       => 'Employee',
        'start_date'      => '2026-01-01',
        'salary_amount'   => 3000,
        'status'          => 'active',
        'employment_type' => 'full_time',
        'salary_type'     => 'monthly',
    ]);
}

test('expense claims index renders', function () {
    $this->get('/hr/expense-claims')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('HR/ExpenseClaims/Index'));
});

test('expense claims create page renders', function () {
    $this->get('/hr/expense-claims/create')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('HR/ExpenseClaims/Create'));
});

test('can create an expense claim', function () {
    $employee = makeEmployee($this->tenant->id);

    $this->post('/hr/expense-claims', [
        'employee_id'   => $employee->id,
        'title'         => 'Flight to London',
        'expense_date'  => '2026-06-01',
        'amount'        => 450.00,
        'currency_code' => 'USD',
        'category'      => 'travel',
    ])->assertSessionHasNoErrors();

    expect(ExpenseClaim::where('title', 'Flight to London')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('claim starts as draft', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Hotel',
        'expense_date' => '2026-06-01',
        'amount'       => 200,
        'category'     => 'accommodation',
    ]);
    expect($claim->status)->toBe('draft');
});

test('can submit a draft claim', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Dinner',
        'expense_date' => '2026-06-01',
        'amount'       => 80,
        'category'     => 'meals',
    ]);

    $this->post("/hr/expense-claims/{$claim->id}/submit")
        ->assertSessionHasNoErrors();

    expect($claim->fresh()->status)->toBe('submitted');
});

test('can approve a submitted claim', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Taxi',
        'expense_date' => '2026-06-01',
        'amount'       => 30,
        'category'     => 'travel',
        'status'       => 'submitted',
    ]);

    $this->post("/hr/expense-claims/{$claim->id}/approve")
        ->assertSessionHasNoErrors();

    expect($claim->fresh()->status)->toBe('approved');
    expect($claim->fresh()->reviewed_by)->toBe($this->admin->id);
});

test('can reject a submitted claim with notes', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Luxury dinner',
        'expense_date' => '2026-06-01',
        'amount'       => 500,
        'category'     => 'meals',
        'status'       => 'submitted',
    ]);

    $this->post("/hr/expense-claims/{$claim->id}/reject", ['notes' => 'Over limit'])
        ->assertSessionHasNoErrors();

    expect($claim->fresh()->status)->toBe('rejected');
    expect($claim->fresh()->review_notes)->toBe('Over limit');
});

test('can mark approved claim as reimbursed', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Office supplies',
        'expense_date' => '2026-06-01',
        'amount'       => 50,
        'category'     => 'supplies',
        'status'       => 'approved',
    ]);

    $this->post("/hr/expense-claims/{$claim->id}/reimburse")
        ->assertSessionHasNoErrors();

    expect($claim->fresh()->status)->toBe('reimbursed');
});

test('cannot submit already submitted claim', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Double submit',
        'expense_date' => '2026-06-01',
        'amount'       => 100,
        'category'     => 'other',
        'status'       => 'submitted',
    ]);

    $this->post("/hr/expense-claims/{$claim->id}/submit")
        ->assertSessionHasErrors();
});

test('staff cannot approve claims', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Staff test',
        'expense_date' => '2026-06-01',
        'amount'       => 50,
        'category'     => 'other',
        'status'       => 'submitted',
    ]);

    $this->actingAs($this->staff)
        ->post("/hr/expense-claims/{$claim->id}/approve")
        ->assertStatus(403);
});

test('show page renders', function () {
    $employee = makeEmployee($this->tenant->id);
    $claim = ExpenseClaim::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'title'        => 'Show test',
        'expense_date' => '2026-06-01',
        'amount'       => 25,
        'category'     => 'other',
    ]);

    $this->get("/hr/expense-claims/{$claim->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('HR/ExpenseClaims/Show'));
});
