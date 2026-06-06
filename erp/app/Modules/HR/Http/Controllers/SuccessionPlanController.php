<?php

namespace App\Modules\HR\Http\Controllers;

use App\Modules\HR\Models\SuccessionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuccessionPlanController
{
    public function index(): Response
    {
        $plans = SuccessionPlan::with('currentHolder')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('HR/SuccessionPlans/Index', compact('plans'));
    }

    public function create(): Response
    {
        return Inertia::render('HR/SuccessionPlans/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'position_title'    => 'required|string|max:255',
            'department'        => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'is_critical'       => 'nullable|boolean',
            'current_holder_id' => 'nullable|exists:employees,id',
        ]);

        $data['tenant_id']  = app('tenant')->id;
        $data['created_by'] = auth()->id();

        SuccessionPlan::create($data);

        return redirect()->route('hr.succession-plans.index');
    }

    public function show(SuccessionPlan $successionPlan): Response
    {
        $successionPlan->load('candidates.employee', 'currentHolder');
        return Inertia::render('HR/SuccessionPlans/Show', ['plan' => $successionPlan]);
    }

    public function edit(SuccessionPlan $successionPlan): Response
    {
        return Inertia::render('HR/SuccessionPlans/Edit', ['plan' => $successionPlan]);
    }

    public function update(Request $request, SuccessionPlan $successionPlan): RedirectResponse
    {
        $data = $request->validate([
            'position_title'    => 'required|string|max:255',
            'department'        => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'is_critical'       => 'nullable|boolean',
            'current_holder_id' => 'nullable|exists:employees,id',
        ]);

        $successionPlan->update($data);

        return redirect()->route('hr.succession-plans.index');
    }

    public function destroy(SuccessionPlan $successionPlan): RedirectResponse
    {
        $successionPlan->delete();
        return redirect()->route('hr.succession-plans.index');
    }

    public function complete(SuccessionPlan $successionPlan): RedirectResponse
    {
        $successionPlan->complete();
        return redirect()->route('hr.succession-plans.index');
    }

    public function deactivate(SuccessionPlan $successionPlan): RedirectResponse
    {
        $successionPlan->deactivate();
        return redirect()->route('hr.succession-plans.index');
    }
}
