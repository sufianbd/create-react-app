<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreLeaveRequestRequest;
use App\Modules\HR\Http\Resources\LeaveRequestResource;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $requests = LeaveRequest::with(['employee', 'leaveType', 'reviewer'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('HR/LeaveRequests/Index', [
            'requests'    => LeaveRequestResource::collection($requests),
            'employees'   => Employee::active()->orderBy('last_name')->get()->map(fn ($e) => [
                'id' => $e->id, 'full_name' => $e->full_name,
            ]),
            'filters'     => $request->only(['status', 'employee_id']),
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Leave Requests', 'href' => route('hr.leave-requests.index')],
            ],
        ]);
    }

    /** Legacy index for /hr/leave (backward compat) */
    public function legacyIndex(Request $request): Response
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
            'employees'   => Employee::active()->orderBy('last_name')->get()->map(fn ($e) => [
                'id' => $e->id, 'full_name' => $e->full_name,
            ]),
            'filters'     => $request->only(['status', 'employee_id']),
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Leave Requests', 'href' => route('hr.leave.index')],
            ],
        ]);
    }

    /** Legacy store for /hr/leave (backward compat) */
    public function legacyStore(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validated();

        LeaveRequest::create([
            ...$data,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function create(): Response
    {
        $this->authorize('create', LeaveRequest::class);

        return Inertia::render('HR/LeaveRequests/Create', [
            'employees'   => Employee::active()->orderBy('last_name')->get()->map(fn ($e) => [
                'id' => $e->id, 'full_name' => $e->full_name,
            ]),
            'leaveTypes'  => LeaveType::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Leave Requests', 'href' => route('hr.leave-requests.index')],
                ['label' => 'New Request'],
            ],
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $data = $request->validated();

        $leaveRequest = LeaveRequest::create([
            ...$data,
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return redirect()->route('hr.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request submitted.');
    }

    public function show(LeaveRequest $leaveRequest): Response
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['employee', 'leaveType', 'reviewer']);

        return Inertia::render('HR/LeaveRequests/Show', [
            'leaveRequest' => new LeaveRequestResource($leaveRequest),
            'can'          => [
                'update' => auth()->user()->can('update', $leaveRequest),
            ],
            'breadcrumbs'  => [
                ['label' => 'HR'],
                ['label' => 'Leave Requests', 'href' => route('hr.leave-requests.index')],
                ['label' => "Leave Request #{$leaveRequest->id}"],
            ],
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $leaveRequest->delete();

        return redirect()->route('hr.leave-requests.index')
            ->with('success', 'Leave request deleted.');
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leave requests can be approved.']);
        }

        $leaveRequest->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leave requests can be rejected.']);
        }

        $leaveRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
