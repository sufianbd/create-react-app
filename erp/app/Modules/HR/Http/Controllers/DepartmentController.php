<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', \App\Modules\HR\Models\Employee::class);

        $departments = Department::withCount('employees')
            ->orderBy('name')
            ->get();

        return Inertia::render('HR/Departments/Index', [
            'departments' => $departments->map(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'description'     => $d->description,
                'is_active'       => $d->is_active,
                'employees_count' => $d->employees_count,
            ]),
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Departments', 'href' => route('hr.departments.index')],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', \App\Modules\HR\Models\Employee::class);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        Department::create([...$data, 'tenant_id' => auth()->user()->tenant_id]);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', \App\Modules\HR\Models\Employee::class);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', \App\Modules\HR\Models\Employee::class);

        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
