<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Notifications\LeaveRequestActioned;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Test Co', 'slug' => 'test-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
});

test('notifications index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/notifications')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Notifications/Index'));
});

test('notification can be marked as read', function () {
    $leaveType = LeaveType::create(['tenant_id' => $this->tenant->id, 'name' => 'Annual', 'days_per_year' => 20, 'is_paid' => true]);
    $employee  = Employee::create(['tenant_id' => $this->tenant->id, 'first_name' => 'A', 'last_name' => 'B', 'employment_type' => 'full_time', 'status' => 'active', 'salary_type' => 'monthly', 'salary_amount' => 3000, 'start_date' => now()->toDateString()]);
    $req = LeaveRequest::create(['tenant_id' => $this->tenant->id, 'employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'start_date' => now()->addDays(1)->toDateString(), 'end_date' => now()->addDays(2)->toDateString(), 'days' => 2]);

    $this->admin->notify(new LeaveRequestActioned($req, 'approved'));

    $notificationId = $this->admin->unreadNotifications->first()->id;

    $this->actingAs($this->admin)
        ->patch("/notifications/{$notificationId}/read")
        ->assertRedirect();

    expect($this->admin->fresh()->unreadNotifications->count())->toBe(0);
});

test('all notifications can be marked as read', function () {
    $leaveType = LeaveType::create(['tenant_id' => $this->tenant->id, 'name' => 'Annual', 'days_per_year' => 20, 'is_paid' => true]);
    $employee  = Employee::create(['tenant_id' => $this->tenant->id, 'first_name' => 'A', 'last_name' => 'B', 'employment_type' => 'full_time', 'status' => 'active', 'salary_type' => 'monthly', 'salary_amount' => 3000, 'start_date' => now()->toDateString()]);
    $req = LeaveRequest::create(['tenant_id' => $this->tenant->id, 'employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'start_date' => now()->addDays(1)->toDateString(), 'end_date' => now()->addDays(2)->toDateString(), 'days' => 2]);

    $this->admin->notify(new LeaveRequestActioned($req, 'approved'));
    $this->admin->notify(new LeaveRequestActioned($req, 'rejected'));

    expect($this->admin->unreadNotifications->count())->toBe(2);

    $this->actingAs($this->admin)
        ->patch('/notifications/read-all')
        ->assertRedirect();

    expect($this->admin->fresh()->unreadNotifications->count())->toBe(0);
});

test('notification can be deleted', function () {
    $leaveType = LeaveType::create(['tenant_id' => $this->tenant->id, 'name' => 'Annual', 'days_per_year' => 20, 'is_paid' => true]);
    $employee  = Employee::create(['tenant_id' => $this->tenant->id, 'first_name' => 'A', 'last_name' => 'B', 'employment_type' => 'full_time', 'status' => 'active', 'salary_type' => 'monthly', 'salary_amount' => 3000, 'start_date' => now()->toDateString()]);
    $req = LeaveRequest::create(['tenant_id' => $this->tenant->id, 'employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'start_date' => now()->addDays(1)->toDateString(), 'end_date' => now()->addDays(2)->toDateString(), 'days' => 2]);

    $this->admin->notify(new LeaveRequestActioned($req, 'approved'));
    $notificationId = $this->admin->notifications->first()->id;

    $this->actingAs($this->admin)
        ->delete("/notifications/{$notificationId}")
        ->assertRedirect();

    expect($this->admin->fresh()->notifications->count())->toBe(0);
});

test('LeaveRequestActioned notification uses database channel', function () {
    Notification::fake();

    $leaveType = LeaveType::create(['tenant_id' => $this->tenant->id, 'name' => 'Annual', 'days_per_year' => 20, 'is_paid' => true]);
    $employee  = Employee::create(['tenant_id' => $this->tenant->id, 'first_name' => 'A', 'last_name' => 'B', 'employment_type' => 'full_time', 'status' => 'active', 'salary_type' => 'monthly', 'salary_amount' => 3000, 'start_date' => now()->toDateString()]);
    $req = LeaveRequest::create(['tenant_id' => $this->tenant->id, 'employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'start_date' => now()->addDays(1)->toDateString(), 'end_date' => now()->addDays(2)->toDateString(), 'days' => 2]);

    $this->admin->notify(new LeaveRequestActioned($req, 'approved'));

    Notification::assertSentTo($this->admin, LeaveRequestActioned::class);
});
