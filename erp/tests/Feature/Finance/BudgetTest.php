<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\BudgetLine;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Budget Co', 'slug' => 'budget-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makeBudgetAccount(): Account
{
    return Account::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Marketing Expense',
        'code'      => 'EXP-MKT-' . uniqid(),
        'type'      => 'expense',
    ]);
}

it('admin can list budgets', function () {
    $this->get('/finance/budgets')->assertStatus(200);
});

it('admin can create a budget with lines', function () {
    $account = makeBudgetAccount();
    $this->post('/finance/budgets', [
        'name'        => 'FY2025 Budget',
        'fiscal_year' => 2025,
        'period_type' => 'annual',
        'lines'       => [
            ['account_id' => $account->id, 'period' => 1, 'amount' => 10000],
        ],
    ])->assertRedirect();
    $budget = Budget::where('name', 'FY2025 Budget')->first();
    expect($budget)->not->toBeNull();
    expect($budget->lines()->count())->toBe(1);
});

it('admin can view budget', function () {
    $budget = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Test Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $this->get("/finance/budgets/{$budget->id}")->assertStatus(200);
});

it('admin can activate budget', function () {
    $budget = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Draft Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $this->post("/finance/budgets/{$budget->id}/activate");
    expect($budget->fresh()->status)->toBe('active');
});

it('admin can close budget', function () {
    $budget = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Active Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'active',
    ]);
    $this->post("/finance/budgets/{$budget->id}/close");
    expect($budget->fresh()->status)->toBe('closed');
});

it('total_budgeted sums lines', function () {
    $account  = makeBudgetAccount();
    $account2 = Account::create([
        'tenant_id' => test()->tenant->id,
        'name'      => 'Office Expense',
        'code'      => 'EXP-OFF-' . uniqid(),
        'type'      => 'expense',
    ]);
    $budget = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Sum Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $budget->lines()->createMany([
        ['tenant_id' => test()->tenant->id, 'account_id' => $account->id,  'period' => 1, 'amount' => 5000],
        ['tenant_id' => test()->tenant->id, 'account_id' => $account2->id, 'period' => 1, 'amount' => 3000],
    ]);
    $budget->load('lines');
    expect($budget->total_budgeted)->toBe(8000.0);
});

it('admin can update a budget line', function () {
    $account = makeBudgetAccount();
    $budget  = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Update Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $line = $budget->lines()->create([
        'tenant_id'  => test()->tenant->id,
        'account_id' => $account->id,
        'period'     => 1,
        'amount'     => 1000,
    ]);
    $this->patch("/finance/budget-lines/{$line->id}", ['amount' => 2000]);
    expect((float) $line->fresh()->amount)->toBe(2000.0);
});

it('fiscal_year must be valid integer', function () {
    $account = makeBudgetAccount();
    $this->postJson('/finance/budgets', [
        'name'        => 'Bad Budget',
        'fiscal_year' => 1999,
        'period_type' => 'annual',
        'lines'       => [['account_id' => $account->id, 'period' => 1, 'amount' => 100]],
    ])->assertStatus(422);
});

it('staff cannot delete budget', function () {
    $budget = Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Staff Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $this->actingAs($this->staff)
        ->delete("/finance/budgets/{$budget->id}")
        ->assertStatus(403);
});

it('duplicate name+fiscal_year is rejected', function () {
    $account = makeBudgetAccount();
    Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'Dup Budget',
        'fiscal_year' => 2025,
        'year'        => 2025,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);
    $this->postJson('/finance/budgets', [
        'name'        => 'Dup Budget',
        'fiscal_year' => 2025,
        'period_type' => 'annual',
        'lines'       => [['account_id' => $account->id, 'period' => 1, 'amount' => 100]],
    ])->assertStatus(422);
});
