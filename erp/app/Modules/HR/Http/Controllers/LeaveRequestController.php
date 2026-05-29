<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreLeaveRequestRequest;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        $requests = LeaveRequest::with(['employee', 'leaveType', 'reviewer'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('HR/Leave/Index', [
            'requests'    => $requests->through(fn ($lr) => [
                'id'          => $lr->id,
                'employee'    => ['id' => $lr->employee->id, 'full_name' => $lr->employee->full_name],
                'leave_type'  => $lr->leaveType?->name,
                'start_date'  => $lr->start_date?->toDateString(),
                'end_date'    => $lr->end_date?->toDateString(),
                'days'        => $lr->days,
                'status'      => $lr->status,
                'notes'       => $lr->notes,
                'reviewed_by' => $lr->reviewer?->name,
                'reviewed_at' => $lr->reviewed_at?->toDateTimeString(),
            ]),
            'employees'   => Employee::active()->orderBy('last_name')->get()->map(fn ($e) => ['id' => $e->id, 'full_name' => $e->full_name]),
            'filters'     => $request->only(['status', 'employee_id']),
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Leave Requests', 'href' => route('hr.leave.index')],
            ],
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validated();

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end   = \Carbon\Carbon::parse($data['end_date']);
        $days  = (int) $start->diffInWeekdays($end) + 1;

        LeaveRequest::create([
            ...$data,
            'tenant_id' => auth()->user()->tenant_id,
            'days'      => max(1, $days),
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', Employee::class);

        try {
            $leaveRequest->approve(auth()->user());
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', Employee::class);

        try {
            $leaveRequest->reject(auth()->user());
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Leave request rejected.');
    }
}
