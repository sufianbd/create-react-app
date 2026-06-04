<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\WorkSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkScheduleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', WorkSchedule::class);

        $schedules = WorkSchedule::orderBy('name')->get();

        return Inertia::render('HR/WorkSchedules/Index', compact('schedules'));
    }

    public function create(): Response
    {
        $this->authorize('create', WorkSchedule::class);

        return Inertia::render('HR/WorkSchedules/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkSchedule::class);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'is_default'       => ['boolean'],
            'monday_start'     => ['nullable', 'date_format:H:i'],
            'monday_end'       => ['nullable', 'date_format:H:i', 'after:monday_start'],
            'tuesday_start'    => ['nullable', 'date_format:H:i'],
            'tuesday_end'      => ['nullable', 'date_format:H:i', 'after:tuesday_start'],
            'wednesday_start'  => ['nullable', 'date_format:H:i'],
            'wednesday_end'    => ['nullable', 'date_format:H:i', 'after:wednesday_start'],
            'thursday_start'   => ['nullable', 'date_format:H:i'],
            'thursday_end'     => ['nullable', 'date_format:H:i', 'after:thursday_start'],
            'friday_start'     => ['nullable', 'date_format:H:i'],
            'friday_end'       => ['nullable', 'date_format:H:i', 'after:friday_start'],
            'saturday_start'   => ['nullable', 'date_format:H:i'],
            'saturday_end'     => ['nullable', 'date_format:H:i', 'after:saturday_start'],
            'sunday_start'     => ['nullable', 'date_format:H:i'],
            'sunday_end'       => ['nullable', 'date_format:H:i', 'after:sunday_start'],
        ]);

        $schedule = WorkSchedule::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
        ]);

        return redirect()->route('hr.work-schedules.show', $schedule)->with('success', 'Work schedule created.');
    }

    public function show(WorkSchedule $workSchedule): Response
    {
        $this->authorize('view', $workSchedule);

        return Inertia::render('HR/WorkSchedules/Show', compact('workSchedule'));
    }

    public function destroy(WorkSchedule $workSchedule): RedirectResponse
    {
        $this->authorize('delete', $workSchedule);

        $workSchedule->delete();

        return redirect()->route('hr.work-schedules.index')->with('success', 'Work schedule deleted.');
    }
}
