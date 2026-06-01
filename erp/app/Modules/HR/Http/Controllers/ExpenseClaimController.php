<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StoreExpenseClaimRequest;
use App\Modules\HR\Http\Resources\ExpenseClaimResource;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\ExpenseClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseClaimController extends Controller
{
    const CATEGORIES = ['travel', 'meals', 'supplies', 'accommodation', 'other'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExpenseClaim::class);

        $claims = ExpenseClaim::with(['employee', 'reviewer'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('HR/ExpenseClaims/Index', [
            'claims'     => ExpenseClaimResource::collection($claims),
            'filters'    => $request->only(['status']),
            'categories' => self::CATEGORIES,
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Expense Claims', 'href' => route('hr.expense-claims.index')],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ExpenseClaim::class);

        return Inertia::render('HR/ExpenseClaims/Create', [
            'employees'   => Employee::active()->orderBy('last_name')->get()->map(fn ($e) => [
                'id' => $e->id, 'full_name' => $e->full_name,
            ]),
            'categories'  => self::CATEGORIES,
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Expense Claims', 'href' => route('hr.expense-claims.index')],
                ['label' => 'New Claim'],
            ],
        ]);
    }

    public function store(StoreExpenseClaimRequest $request): RedirectResponse
    {
        $this->authorize('create', ExpenseClaim::class);

        $data = $request->validated();

        $claim = ExpenseClaim::create([
            ...$data,
            'tenant_id'  => auth()->user()->tenant_id,
            'status'     => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('hr.expense-claims.show', $claim)
            ->with('success', 'Expense claim created.');
    }

    public function show(ExpenseClaim $expenseClaim): Response
    {
        $this->authorize('view', $expenseClaim);

        $expenseClaim->load(['employee', 'submitter', 'reviewer']);

        return Inertia::render('HR/ExpenseClaims/Show', [
            'claim'      => new ExpenseClaimResource($expenseClaim),
            'categories' => self::CATEGORIES,
            'can'        => [
                'update'  => auth()->user()->can('update', $expenseClaim),
                'delete'  => auth()->user()->can('delete', $expenseClaim),
                'approve' => auth()->user()->can('approve', $expenseClaim),
            ],
            'breadcrumbs' => [
                ['label' => 'HR'],
                ['label' => 'Expense Claims', 'href' => route('hr.expense-claims.index')],
                ['label' => $expenseClaim->title],
            ],
        ]);
    }

    public function submit(ExpenseClaim $expenseClaim): RedirectResponse
    {
        $this->authorize('update', $expenseClaim);

        try {
            $expenseClaim->submit();
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim submitted.');
    }

    public function approve(ExpenseClaim $expenseClaim, Request $request): RedirectResponse
    {
        $this->authorize('approve', $expenseClaim);

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            $expenseClaim->approve($request->input('notes', ''));
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim approved.');
    }

    public function reject(ExpenseClaim $expenseClaim, Request $request): RedirectResponse
    {
        $this->authorize('approve', $expenseClaim);

        $request->validate([
            'notes' => 'required|string',
        ]);

        try {
            $expenseClaim->reject($request->input('notes', ''));
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim rejected.');
    }

    public function reimburse(ExpenseClaim $expenseClaim): RedirectResponse
    {
        $this->authorize('approve', $expenseClaim);

        try {
            $expenseClaim->reimburse();
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Expense claim marked as reimbursed.');
    }

    public function destroy(ExpenseClaim $expenseClaim): RedirectResponse
    {
        $this->authorize('delete', $expenseClaim);

        $expenseClaim->delete();

        return redirect()->route('hr.expense-claims.index')
            ->with('success', 'Expense claim deleted.');
    }
}
