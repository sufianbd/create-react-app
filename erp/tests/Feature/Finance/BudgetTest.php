<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\BudgetLine;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Budget Corp', 'slug' => 'budget-corp']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makeBudget(string $status = 'draft'): Budget {
    return Budget::create([
        'tenant_id'   => test()->tenant->id,
        'name'        => 'FY2026 Budget',
        'fiscal_year' => 2026,
        'year'        => 2026,
        'period_type' => 'annual',
        'status'      => $status,
    ]);
}

function makeBudgetLine(Budget $budget, float $budgeted = 10000, float $actual = 0, string $type = 'expense'): BudgetLine {
    return BudgetLine::create([
        'tenant_id'       => test()->tenant->id,
        'budget_id'       => $budget->id,
        'category'        => 'Salaries',
        'line_type'       => $type,
        'period_number'   => 1,
        'budgeted_amount' => $budgeted,
        'actual_amount'   => $actual,
    ]);
}

it('admin can list budgets', function () {
    $this->get('/finance/budgets')->assertStatus(200);
});

it('admin can create a budget', function () {
    $this->post('/finance/budgets', [
        'name'        => 'Q1 Budget',
        'fiscal_year' => 2026,
        'period_type' => 'quarterly',
    ])->assertRedirect();
    expect(Budget::where('name', 'Q1 Budget')->exists())->toBeTrue();
});

it('budget store validates required fields', function () {
    $this->postJson('/finance/budgets', ['name' => '', 'fiscal_year' => ''])
        ->assertStatus(422)->assertJsonValidationErrors(['name', 'fiscal_year']);
});

it('admin can add a budget line', function () {
    $budget = makeBudget();
    $this->post("/finance/budgets/{$budget->id}/lines", [
        'category'        => 'Marketing',
        'line_type'       => 'expense',
        'period_number'   => 1,
        'budgeted_amount' => 5000,
    ])->assertRedirect();
    expect($budget->lines()->count())->toBe(1);
});

it('admin can update actual amount on a line', function () {
    $budget = makeBudget();
    $line   = makeBudgetLine($budget, 10000);
    $this->patch("/finance/budgets/{$budget->id}/lines/{$line->id}/actual", [
        'actual_amount' => 8500,
    ])->assertRedirect();
    expect($line->fresh()->actual_amount)->toBe(8500.0);
});

it('admin can activate a budget', function () {
    $budget = makeBudget('draft');
    $this->post("/finance/budgets/{$budget->id}/activate")->assertRedirect();
    expect($budget->fresh()->status)->toBe('active');
});

it('admin can close a budget', function () {
    $budget = makeBudget('active');
    $this->post("/finance/budgets/{$budget->id}/close")->assertRedirect();
    expect($budget->fresh()->status)->toBe('closed');
});

it('variance accessor calculates correctly', function () {
    $budget = makeBudget();
    $line   = makeBudgetLine($budget, 10000, 12000, 'expense');
    expect($line->variance)->toBe(2000.0);
    expect($line->is_over_budget)->toBeTrue();
    expect($line->variance_percent)->toBe(20.0);
});

it('budget total accessors sum lines', function () {
    $budget = makeBudget();
    makeBudgetLine($budget, 10000, 9000, 'expense');
    makeBudgetLine($budget, 5000, 6000, 'expense');
    $budget->unsetRelation('lines');
    expect($budget->total_budgeted)->toBe(15000.0);
    expect($budget->total_actual)->toBe(15000.0);
    expect($budget->total_variance)->toBe(0.0);
});

it('staff cannot delete a budget', function () {
    $budget = makeBudget();
    $this->actingAs($this->staff)
        ->delete("/finance/budgets/{$budget->id}")
        ->assertStatus(403);
});
