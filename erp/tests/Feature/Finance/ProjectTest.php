<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\Project;
use App\Modules\Finance\Models\ProjectTimeEntry;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Proj Co', 'slug' => 'proj-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
    $this->actingAs($this->admin);
    app()->instance('tenant', $this->tenant);
});

test('projects index renders', function () {
    $this->get('/finance/projects')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/Projects/Index'));
});

test('can create a project', function () {
    $this->post('/finance/projects', [
        'name'   => 'Test Project',
        'status' => 'active',
    ])->assertSessionHasNoErrors()
      ->assertRedirect();

    expect(Project::where('name', 'Test Project')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('project show renders', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Show Project',
        'status'    => 'active',
    ]);

    $this->get("/finance/projects/{$project->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/Projects/Show'));
});

test('can update a project', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Old Name',
        'status'    => 'draft',
    ]);

    $this->patch("/finance/projects/{$project->id}", [
        'name'   => 'New Name',
        'status' => 'active',
    ])->assertSessionHasNoErrors()
      ->assertRedirect();

    expect(Project::find($project->id)->name)->toBe('New Name');
});

test('can delete a project', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Delete Me',
        'status'    => 'active',
    ]);

    $this->delete("/finance/projects/{$project->id}")
        ->assertRedirect();

    expect(Project::withTrashed()->find($project->id)->deleted_at)->not->toBeNull();
});

test('can log time entry', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Time Project',
        'status'    => 'active',
    ]);

    $this->post("/finance/projects/{$project->id}/time-entries", [
        'description' => 'Development work',
        'hours'       => 2.5,
        'billable'    => true,
        'entry_date'  => '2026-06-01',
    ])->assertSessionHasNoErrors();

    expect(ProjectTimeEntry::where('project_id', $project->id)->where('description', 'Development work')->exists())->toBeTrue();
});

test('time entry requires hours', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Hours Project',
        'status'    => 'active',
    ]);

    $this->post("/finance/projects/{$project->id}/time-entries", [
        'description' => 'No hours',
        'entry_date'  => '2026-06-01',
    ])->assertSessionHasErrors('hours');
});

test('can mark entries as billed', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Billing Project',
        'status'    => 'active',
    ]);

    $entry = ProjectTimeEntry::create([
        'project_id'  => $project->id,
        'user_id'     => $this->admin->id,
        'description' => 'Billable work',
        'hours'       => 3,
        'billable'    => true,
        'billed'      => false,
        'entry_date'  => '2026-06-01',
    ]);

    $this->post("/finance/projects/{$project->id}/mark-billed", [
        'entry_ids' => [$entry->id],
    ])->assertSessionHasNoErrors();

    expect(ProjectTimeEntry::find($entry->id)->billed)->toBeTrue();
});

test('staff cannot delete project', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Protected Project',
        'status'    => 'active',
    ]);

    $this->actingAs($this->staff)
        ->delete("/finance/projects/{$project->id}")
        ->assertStatus(403);
});

test('project total_hours accessor', function () {
    $project = Project::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Hours Accessor Project',
        'status'    => 'active',
    ]);

    ProjectTimeEntry::create([
        'project_id'  => $project->id,
        'user_id'     => $this->admin->id,
        'description' => 'First entry',
        'hours'       => 2.5,
        'billable'    => true,
        'billed'      => false,
        'entry_date'  => '2026-06-01',
    ]);

    ProjectTimeEntry::create([
        'project_id'  => $project->id,
        'user_id'     => $this->admin->id,
        'description' => 'Second entry',
        'hours'       => 1.5,
        'billable'    => false,
        'billed'      => false,
        'entry_date'  => '2026-06-02',
    ]);

    expect($project->total_hours)->toBe(4.0);
});
