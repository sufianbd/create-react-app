<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\TrainingCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingCourseController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', TrainingCourse::class);

        $courses = TrainingCourse::withCount('trainingRecords')
            ->orderBy('title')
            ->paginate(25);

        return Inertia::render('HR/TrainingCourses/Index', compact('courses'));
    }

    public function create(): Response
    {
        $this->authorize('create', TrainingCourse::class);

        return Inertia::render('HR/TrainingCourses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TrainingCourse::class);

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'provider'       => 'nullable|string|max:255',
            'type'           => 'required|in:internal,external,online,certification',
            'duration_hours' => 'nullable|numeric|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $course = TrainingCourse::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
        ]);

        return redirect()->route('hr.training-courses.show', $course)
            ->with('success', 'Training course created.');
    }

    public function show(TrainingCourse $trainingCourse): Response
    {
        $this->authorize('view', $trainingCourse);

        $trainingCourse->load([
            'trainingRecords' => fn ($q) => $q->with('employee')->latest()->limit(20),
        ]);

        return Inertia::render('HR/TrainingCourses/Show', [
            'course' => $trainingCourse,
        ]);
    }

    public function destroy(TrainingCourse $trainingCourse): RedirectResponse
    {
        $this->authorize('delete', $trainingCourse);

        $trainingCourse->delete();

        return redirect()->route('hr.training-courses.index')
            ->with('success', 'Training course deleted.');
    }
}
