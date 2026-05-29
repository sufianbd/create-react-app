<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PayrollItem;
use App\Modules\HR\Models\PayrollRun;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant   = Tenant::create(['name' => 'Test Co', 'slug' => 'test-co']);
    $this->admin    = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->employee = Employee::create([
        'tenant_id'       => $this->tenant->id,
        'first_name'      => 'Pay',
        'last_name'       => 'Emp',
        'employment_type' => 'full_time',
        'status'          => 'active',
        'salary_type'     => 'monthly',
        'salary_amount'   => 5000,
        'start_date'      => now()->toDateString(),
    ]);
});

test('payroll index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/hr/payroll')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('HR/Payroll/Index'));
});

test('payroll create page renders', function () {
    $this->actingAs($this->admin)
        ->get('/hr/payroll/create')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('HR/Payroll/Create'));
});

test('payroll run can be created with items', function () {
    $this->actingAs($this->admin)
        ->post('/hr/payroll', [
            'period_start' => '2026-05-01',
            'period_end'   => '2026-05-31',
            'items'        => [
                [
                    'employee_id'  => $this->employee->id,
                    'gross_salary' => 5000,
                    'deductions'   => 500,
                ],
            ],
        ])
        ->assertRedirect();

    $run = PayrollRun::latest()->first();
    expect($run)->not->toBeNull();
    expect($run->items)->toHaveCount(1);
    expect((float) $run->items->first()->net_salary)->toBe(4500.0);
});

test('payroll run starts as draft', function () {
    $run = PayrollRun::create([
        'tenant_id'    => $this->tenant->id,
        'period_start' => '2026-05-01',
        'period_end'   => '2026-05-31',
    ]);

    expect($run->status)->toBe('draft');
});

test('payroll run can be processed', function () {
    $run = PayrollRun::create([
        'tenant_id'    => $this->tenant->id,
        'period_start' => '2026-05-01',
        'period_end'   => '2026-05-31',
    ]);

    PayrollItem::create([
        'payroll_run_id' => $run->id,
        'employee_id'    => $this->employee->id,
        'gross_salary'   => 5000,
        'deductions'     => 500,
        'net_salary'     => 4500,
    ]);

    $run->process();

    expect($run->fresh()->status)->toBe('processed');
});

test('cannot process an already processed run', function () {
    $run = PayrollRun::create([
        'tenant_id'    => $this->tenant->id,
        'period_start' => '2026-05-01',
        'period_end'   => '2026-05-31',
        'status'       => 'processed',
    ]);

    expect(fn () => $run->process())->toThrow(\DomainException::class);
});

test('total_gross and total_net are computed correctly', function () {
    $run = PayrollRun::create([
        'tenant_id'    => $this->tenant->id,
        'period_start' => '2026-05-01',
        'period_end'   => '2026-05-31',
    ]);

    $emp2 = Employee::create([
        'tenant_id'       => $this->tenant->id,
        'first_name'      => 'Two',
        'last_name'       => 'Emp',
        'employment_type' => 'full_time',
        'status'          => 'active',
        'salary_type'     => 'monthly',
        'salary_amount'   => 3000,
        'start_date'      => now()->toDateString(),
    ]);

    PayrollItem::create(['payroll_run_id' => $run->id, 'employee_id' => $this->employee->id, 'gross_salary' => 5000, 'deductions' => 500, 'net_salary' => 4500]);
    PayrollItem::create(['payroll_run_id' => $run->id, 'employee_id' => $emp2->id,            'gross_salary' => 3000, 'deductions' => 300, 'net_salary' => 2700]);

    $run->load('items');

    expect($run->total_gross)->toBe(8000.0);
    expect($run->total_net)->toBe(7200.0);
});
