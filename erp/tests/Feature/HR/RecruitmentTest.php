<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Models\JobPosition;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Recruit Co', 'slug' => 'recruit-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

function makePosition(): JobPosition
{
    return JobPosition::create([
        'tenant_id'       => test()->tenant->id,
        'title'           => 'Software Engineer',
        'employment_type' => 'full_time',
        'openings'        => 2,
        'status'          => 'draft',
    ]);
}

it('admin can list job positions', function () {
    $this->get('/hr/job-positions')->assertStatus(200);
});

it('admin can create a job position', function () {
    $this->post('/hr/job-positions', [
        'title'           => 'Backend Developer',
        'employment_type' => 'full_time',
        'openings'        => 1,
    ])->assertRedirect();
    expect(JobPosition::where('title', 'Backend Developer')->exists())->toBeTrue();
});

it('admin can publish a job position', function () {
    $pos = makePosition();
    $this->post("/hr/job-positions/{$pos->id}/publish");
    expect($pos->fresh()->status)->toBe('open');
    expect($pos->fresh()->posted_at)->not->toBeNull();
});

it('admin can close a job position', function () {
    $pos = makePosition();
    $pos->update(['status' => 'open']);
    $this->post("/hr/job-positions/{$pos->id}/close");
    expect($pos->fresh()->status)->toBe('closed');
    expect($pos->fresh()->closed_at)->not->toBeNull();
});

it('admin can view a job position', function () {
    $pos = makePosition();
    $this->get("/hr/job-positions/{$pos->id}")->assertStatus(200);
});

it('admin can create a job application', function () {
    $pos = makePosition();
    $this->post('/hr/job-applications', [
        'job_position_id' => $pos->id,
        'applicant_name'  => 'John Doe',
        'applicant_email' => 'john@example.com',
    ])->assertRedirect();
    expect(JobApplication::where('applicant_email', 'john@example.com')->exists())->toBeTrue();
});

it('admin can advance application stage', function () {
    $pos = makePosition();
    $app = JobApplication::create([
        'tenant_id'       => test()->tenant->id,
        'job_position_id' => $pos->id,
        'applicant_name'  => 'Jane Doe',
        'applicant_email' => 'jane@example.com',
        'stage'           => 'applied',
    ]);
    $this->patch("/hr/job-applications/{$app->id}/advance", ['stage' => 'screening']);
    expect($app->fresh()->stage)->toBe('screening');
});

it('hired_at is set when advanced to hired', function () {
    $pos = makePosition();
    $app = JobApplication::create([
        'tenant_id'       => test()->tenant->id,
        'job_position_id' => $pos->id,
        'applicant_name'  => 'Jane Doe',
        'applicant_email' => 'jane@example.com',
        'stage'           => 'offer',
    ]);
    $this->patch("/hr/job-applications/{$app->id}/advance", ['stage' => 'hired']);
    expect($app->fresh()->hired_at)->not->toBeNull();
});

it('admin can reject application', function () {
    $pos = makePosition();
    $app = JobApplication::create([
        'tenant_id'       => test()->tenant->id,
        'job_position_id' => $pos->id,
        'applicant_name'  => 'Jane Doe',
        'applicant_email' => 'jane@example.com',
        'stage'           => 'screening',
    ]);
    $this->post("/hr/job-applications/{$app->id}/reject", ['reason' => 'Not a fit']);
    expect($app->fresh()->stage)->toBe('rejected');
    expect($app->fresh()->rejected_at)->not->toBeNull();
});

it('staff cannot delete job position', function () {
    $pos = makePosition();
    $this->actingAs($this->staff)
        ->delete("/hr/job-positions/{$pos->id}")
        ->assertStatus(403);
});
