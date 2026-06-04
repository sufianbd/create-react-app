<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\PerformanceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceReviewController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PerformanceReview::class);
        $reviews = PerformanceReview::with(['employee', 'reviewer'])
            ->orderByDesc('period_end')
            ->paginate(25);
        return Inertia::render('HR/PerformanceReviews/Index', compact('reviews'));
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', PerformanceReview::class);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $employeeId = $request->get('employee_id');
        return Inertia::render('HR/PerformanceReviews/Create', compact('employees', 'employeeId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PerformanceReview::class);
        $data = $request->validate([
            'employee_id'               => 'required|exists:employees,id',
            'period_start'              => 'required|date',
            'period_end'                => 'required|date|after_or_equal:period_start',
            'comments'                  => 'nullable|string',
            'goals'                     => 'nullable|array',
            'goals.*.title'             => 'required_with:goals|string',
            'goals.*.description'       => 'nullable|string',
            'competencies'              => 'nullable|array',
            'competencies.*.name'       => 'required_with:competencies|string',
            'competencies.*.rating'     => 'nullable|integer|min:1|max:5',
            'competencies.*.notes'      => 'nullable|string',
        ]);

        $review = PerformanceReview::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'employee_id'  => $data['employee_id'],
            'reviewer_id'  => auth()->id(),
            'period_start' => $data['period_start'],
            'period_end'   => $data['period_end'],
            'status'       => 'draft',
            'comments'     => $data['comments'] ?? null,
        ]);

        foreach ($data['goals'] ?? [] as $goal) {
            $review->goals()->create([
                'title'       => $goal['title'],
                'description' => $goal['description'] ?? null,
            ]);
        }

        foreach ($data['competencies'] ?? [] as $comp) {
            $review->competencies()->create([
                'name'   => $comp['name'],
                'rating' => $comp['rating'] ?? null,
                'notes'  => $comp['notes'] ?? null,
            ]);
        }

        return redirect()->route('hr.performance-reviews.show', $review)->with('success', 'Review created.');
    }

    public function show(PerformanceReview $performanceReview): Response
    {
        $this->authorize('view', $performanceReview);
        $performanceReview->load(['employee', 'reviewer', 'goals', 'competencies']);
        return Inertia::render('HR/PerformanceReviews/Show', compact('performanceReview'));
    }

    public function startReview(PerformanceReview $performanceReview): RedirectResponse
    {
        $this->authorize('update', $performanceReview);
        abort_unless($performanceReview->status === 'draft', 422, 'Only draft reviews can be started.');
        $performanceReview->update(['status' => 'in_review']);
        return back()->with('success', 'Review started.');
    }

    public function complete(PerformanceReview $performanceReview, Request $request): RedirectResponse
    {
        $this->authorize('update', $performanceReview);
        abort_unless($performanceReview->status === 'in_review', 422, 'Only in-review reviews can be completed.');
        $request->validate(['overall_rating' => 'required|integer|min:1|max:5']);
        $performanceReview->update([
            'status'         => 'completed',
            'overall_rating' => $request->overall_rating,
            'completed_at'   => now(),
        ]);
        return back()->with('success', 'Review completed.');
    }

    public function updateGoal(PerformanceReview $performanceReview, Request $request, int $goalId): RedirectResponse
    {
        $this->authorize('update', $performanceReview);
        $goal = $performanceReview->goals()->findOrFail($goalId);
        $request->validate([
            'achieved'          => 'boolean',
            'achievement_notes' => 'nullable|string',
        ]);
        $goal->update($request->only('achieved', 'achievement_notes'));
        return back()->with('success', 'Goal updated.');
    }

    public function destroy(PerformanceReview $performanceReview): RedirectResponse
    {
        $this->authorize('delete', $performanceReview);
        abort_unless($performanceReview->status === 'draft', 422, 'Only draft reviews can be deleted.');
        $performanceReview->delete();
        return redirect()->route('hr.performance-reviews.index')->with('success', 'Review deleted.');
    }
}
