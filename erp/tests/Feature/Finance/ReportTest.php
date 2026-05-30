<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalLine;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Report Co', 'slug' => 'report-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
});

test('profit and loss report is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/reports/profit-loss')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p
            ->component('Finance/Reports/ProfitLoss')
            ->has('revenue')
            ->has('expenses')
            ->has('net')
        );
});

test('profit and loss net is zero with no posted entries', function () {
    $this->actingAs($this->admin)
        ->get('/finance/reports/profit-loss')
        ->assertInertia(fn ($p) => $p->where('net', 0));
});

test('balance sheet is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/reports/balance-sheet')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p
            ->component('Finance/Reports/BalanceSheet')
            ->has('assets')
            ->has('liabilities')
            ->has('equity')
        );
});

test('balance sheet total assets equals total liabilities plus equity', function () {
    Carbon::setTestNow('2026-06-01');

    $assetAccount = Account::create([
        'tenant_id'  => $this->tenant->id,
        'code'       => '1000',
        'name'       => 'Cash',
        'type'       => 'asset',
        'is_active'  => true,
    ]);

    $equityAccount = Account::create([
        'tenant_id'  => $this->tenant->id,
        'code'       => '3000',
        'name'       => 'Owner Equity',
        'type'       => 'equity',
        'is_active'  => true,
    ]);

    $entry = JournalEntry::create([
        'tenant_id'   => $this->tenant->id,
        'date'        => '2026-06-01',
        'description' => 'Initial capital',
        'status'      => 'posted',
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $assetAccount->id,
        'debit'            => 1000,
        'credit'           => 0,
    ]);

    JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $equityAccount->id,
        'debit'            => 0,
        'credit'           => 1000,
    ]);

    $this->actingAs($this->admin)
        ->get('/finance/reports/balance-sheet?as_of=2026-06-01')
        ->assertInertia(fn ($p) => $p
            ->where('total_assets', 1000)
            ->where('total_equity', 1000)
        );

    Carbon::setTestNow();
});

test('guest cannot access profit and loss', function () {
    $this->get('/finance/reports/profit-loss')
        ->assertRedirect();
});
