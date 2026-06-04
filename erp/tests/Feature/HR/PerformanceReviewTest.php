<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PerformanceReview;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Review Co', 'slug' => 'review-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makeReviewEmployee(): Employee
{
    $dept = Department::create(['tenant_id' => test()->tenant->id, 'name' => 'Engineering']);
    return Employee::create([
        'tenant_id'     => test()->tenant->id,
        'first_name'    => 'Jane',
        'last_name'     => 'Smith',
        'email'         => 'jane@example.com',
        'department_id' => $dept->id,
        'start_date'    => now()->toDateString(),
        'salary_amount' => 60000,
        'status'        => 'active',
    ]);
}

it('admin can list performance reviews', function () {
    $response = $this->get('/hr/performance-reviews');
    $response->assertStatus(200);
});

it('admin can view create form', function () {
    $response = $this->get('/hr/performance-reviews/create');
    $response->assertStatus(200);
});

it('admin can create review with goals and competencies', function () {
    $employee = makeReviewEmployee();

    $response = $this->post('/hr/performance-reviews', [
        'employee_id'  => $employee->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'comments'     => 'Annual review',
        'goals'        => [
            ['title' => 'Improve performance', 'description' => 'Focus on quality'],
        ],
        'competencies' => [
            ['name' => 'Communication', 'rating' => 4, 'notes' => 'Good'],
        ],
    ]);

    $response->assertRedirect();

    $review = PerformanceReview::where('employee_id', $employee->id)->first();
    expect($review)->not->toBeNull();
    expect($review->goals()->count())->toBe(1);
    expect($review->competencies()->count())->toBe(1);
});

it('admin can view review', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'draft',
    ]);

    $response = $this->get("/hr/performance-reviews/{$review->id}");
    $response->assertStatus(200);
});

it('admin can start draft review', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'draft',
    ]);

    $this->post("/hr/performance-reviews/{$review->id}/start");

    expect($review->fresh()->status)->toBe('in_review');
});

it('admin can complete in_review review', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'in_review',
    ]);

    $this->post("/hr/performance-reviews/{$review->id}/complete", ['overall_rating' => 4]);

    $fresh = $review->fresh();
    expect($fresh->status)->toBe('completed');
    expect($fresh->overall_rating)->toBe(4);
});

it('overall_rating must be 1-5', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'in_review',
    ]);

    $this->postJson("/hr/performance-reviews/{$review->id}/complete", ['overall_rating' => 6])
        ->assertStatus(422);
});

it('admin can update goal achieved status', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'in_review',
    ]);
    $goal = $review->goals()->create(['title' => 'Improve quality', 'achieved' => false]);

    $this->patch("/hr/performance-reviews/{$review->id}/goals/{$goal->id}", ['achieved' => true]);

    expect($goal->fresh()->achieved)->toBeTrue();
});

it('average_competency_rating is computed', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'draft',
    ]);
    $review->competencies()->create(['name' => 'Communication', 'rating' => 4]);
    $review->competencies()->create(['name' => 'Teamwork', 'rating' => 2]);

    $review->load('competencies');
    expect($review->average_competency_rating)->toBe(3.0);
});

it('staff cannot delete review', function () {
    $employee = makeReviewEmployee();
    $review = PerformanceReview::create([
        'tenant_id'    => $this->tenant->id,
        'employee_id'  => $employee->id,
        'reviewer_id'  => $this->admin->id,
        'period_start' => '2025-01-01',
        'period_end'   => '2025-12-31',
        'status'       => 'draft',
    ]);

    $this->actingAs($this->staff);
    $response = $this->delete("/hr/performance-reviews/{$review->id}");
    $response->assertStatus(403);
});
