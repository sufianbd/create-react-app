<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\JobPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JobPositionController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', JobPosition::class);

        $positions = JobPosition::with('department')
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        return Inertia::render('HR/JobPositions/Index', compact('positions'));
    }

    public function create(): Response
    {
        $this->authorize('create', JobPosition::class);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('HR/JobPositions/Create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobPosition::class);

        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'department_id'   => ['nullable', Rule::exists('departments', 'id')],
            'location'        => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'internship'])],
            'description'     => ['nullable', 'string'],
            'requirements'    => ['nullable', 'string'],
            'openings'        => ['required', 'integer', 'min:1'],
        ]);

        $position = JobPosition::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
        ]);

        return redirect()->route('hr.job-positions.show', $position)
            ->with('success', 'Job position created.');
    }

    public function show(JobPosition $jobPosition): Response
    {
        $this->authorize('view', $jobPosition);

        $jobPosition->load([
            'department',
            'applications' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('HR/JobPositions/Show', [
            'position' => $jobPosition,
        ]);
    }

    public function destroy(JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('delete', $jobPosition);

        $jobPosition->delete();

        return redirect()->route('hr.job-positions.index')
            ->with('success', 'Job position deleted.');
    }

    public function publish(JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('update', $jobPosition);

        $jobPosition->publish();

        return redirect()->back()->with('success', 'Job position published.');
    }

    public function close(JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('update', $jobPosition);

        $jobPosition->close();

        return redirect()->back()->with('success', 'Job position closed.');
    }
}
