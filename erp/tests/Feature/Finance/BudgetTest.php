<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\BudgetLine;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalLine;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Budget Co', 'slug' => 'budget-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
});

test('budgets index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/budgets')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/Budgets/Index'));
});

test('can create a budget with lines', function () {
    $account = Account::create([
        'tenant_id' => $this->tenant->id,
        'code'      => '4000',
        'name'      => 'Revenue',
        'type'      => 'income',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post('/finance/budgets', [
            'name'        => 'FY2026',
            'year'        => 2026,
            'period_type' => 'annual',
            'lines'       => [
                ['account_id' => $account->id, 'period' => 0, 'amount' => 100000],
            ],
        ])
        ->assertSessionHasNoErrors();

    $budget = Budget::where('name', 'FY2026')->where('tenant_id', $this->tenant->id)->first();
    expect($budget)->not->toBeNull();
    expect($budget->lines()->count())->toBe(1);
});

test('budget show page renders with variance', function () {
    $account = Account::create([
        'tenant_id' => $this->tenant->id,
        'code'      => '5000',
        'name'      => 'Expenses',
        'type'      => 'expense',
        'is_active' => true,
    ]);
    $budget = Budget::create([
        'tenant_id'   => $this->tenant->id,
        'name'        => 'Test Budget',
        'year'        => 2026,
        'period_type' => 'annual',
        'status'      => 'active',
    ]);
    BudgetLine::create([
        'budget_id'  => $budget->id,
        'account_id' => $account->id,
        'period'     => 0,
        'amount'     => 50000,
    ]);

    $this->actingAs($this->admin)
        ->get("/finance/budgets/{$budget->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p
            ->component('Finance/Budgets/Show')
            ->has('lines')
            ->has('total_budget')
            ->has('total_actual')
            ->has('total_variance')
        );
});

test('variance is computed from posted journal entries', function () {
    $account = Account::create([
        'tenant_id' => $this->tenant->id,
        'code'      => '5100',
        'name'      => 'Marketing',
        'type'      => 'expense',
        'is_active' => true,
    ]);
    $otherAccount = Account::create([
        'tenant_id' => $this->tenant->id,
        'code'      => '1000',
        'name'      => 'Cash',
        'type'      => 'asset',
        'is_active' => true,
    ]);

    $budget = Budget::create([
        'tenant_id'   => $this->tenant->id,
        'name'        => 'Variance Test',
        'year'        => 2026,
        'period_type' => 'annual',
        'status'      => 'active',
    ]);
    BudgetLine::create(['budget_id' => $budget->id, 'account_id' => $account->id, 'period' => 0, 'amount' => 10000]);

    // Post a journal entry with 3000 debit to the expense account
    $entry = JournalEntry::create([
        'tenant_id'   => $this->tenant->id,
        'date'        => '2026-03-01',
        'description' => 'Marketing spend',
        'status'      => 'posted',
    ]);
    JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $account->id,      'debit' => 3000, 'credit' => 0]);
    JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $otherAccount->id, 'debit' => 0,    'credit' => 3000]);

    $this->actingAs($this->admin)
        ->get("/finance/budgets/{$budget->id}")
        ->assertInertia(fn ($p) => $p
            ->where('lines.0.actual', 3000)
            ->where('lines.0.variance', -7000)  // actual(3000) - budget(10000) = -7000 (under-spent)
        );
});

test('draft budget can be deleted', function () {
    $budget = Budget::create([
        'tenant_id'   => $this->tenant->id,
        'name'        => 'To Delete',
        'year'        => 2026,
        'period_type' => 'annual',
        'status'      => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->delete("/finance/budgets/{$budget->id}")
        ->assertSessionHasNoErrors();

    expect(Budget::find($budget->id))->toBeNull();
});

test('active budget cannot be deleted', function () {
    $budget = Budget::create([
        'tenant_id'   => $this->tenant->id,
        'name'        => 'Active',
        'year'        => 2026,
        'period_type' => 'annual',
        'status'      => 'active',
    ]);

    $this->actingAs($this->admin)
        ->delete("/finance/budgets/{$budget->id}")
        ->assertStatus(403);
});

test('staff cannot create budgets', function () {
    $this->actingAs($this->staff)
        ->post('/finance/budgets', [
            'name' => 'Staff Budget', 'year' => 2026, 'period_type' => 'annual', 'lines' => [],
        ])
        ->assertStatus(403);
});

test('guest cannot access budgets', function () {
    $this->get('/finance/budgets')->assertRedirect();
});
