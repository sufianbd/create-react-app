<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Project;
use App\Modules\Finance\Models\ProjectTimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::withCount(['timeEntries'])
            ->with('contact')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'description'         => $p->description,
                'status'              => $p->status,
                'budget'              => $p->budget,
                'contact_id'          => $p->contact_id,
                'invoice_id'          => $p->invoice_id,
                'starts_on'           => $p->starts_on?->toDateString(),
                'ends_on'             => $p->ends_on?->toDateString(),
                'contact'             => $p->contact ? ['id' => $p->contact->id, 'name' => $p->contact->name] : null,
                'time_entries_count'  => $p->time_entries_count,
            ]);

        return Inertia::render('Finance/Projects/Index', [
            'projects' => $projects,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Projects'],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        $contacts = Contact::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Finance/Projects/Create', [
            'contacts' => $contacts,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Projects', 'href' => '/finance/projects'],
                ['label' => 'New Project'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:draft,active,completed,cancelled'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
            'contact_id'  => ['nullable', 'exists:contacts,id'],
            'invoice_id'  => ['nullable', 'exists:invoices,id'],
            'starts_on'   => ['nullable', 'date'],
            'ends_on'     => ['nullable', 'date'],
        ]);

        $project = Project::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
        ]));

        return redirect()->route('finance.projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['timeEntries.user', 'contact', 'invoice', 'attachments']);

        return Inertia::render('Finance/Projects/Show', [
            'project' => [
                'id'           => $project->id,
                'name'         => $project->name,
                'description'  => $project->description,
                'status'       => $project->status,
                'budget'       => $project->budget,
                'contact_id'   => $project->contact_id,
                'invoice_id'   => $project->invoice_id,
                'starts_on'    => $project->starts_on?->toDateString(),
                'ends_on'      => $project->ends_on?->toDateString(),
                'contact'      => $project->contact ? ['id' => $project->contact->id, 'name' => $project->contact->name] : null,
                'invoice'      => $project->invoice ? ['id' => $project->invoice->id, 'reference' => $project->invoice->number ?? '#' . $project->invoice->id] : null,
                'total_hours'  => $project->total_hours,
                'billable_hours' => $project->billable_hours,
                'attachments'  => $project->attachments->map(fn ($a) => [
                    'id' => $a->id, 'filename' => $a->filename, 'disk' => $a->disk,
                    'path' => $a->path, 'mime_type' => $a->mime_type, 'size' => $a->size,
                    'uploaded_by' => $a->uploaded_by, 'created_at' => $a->created_at?->toIso8601String(),
                ]),
                'time_entries' => $project->timeEntries->map(fn ($e) => [
                    'id'          => $e->id,
                    'project_id'  => $e->project_id,
                    'user_id'     => $e->user_id,
                    'description' => $e->description,
                    'hours'       => $e->hours,
                    'billable'    => $e->billable,
                    'billed'      => $e->billed,
                    'entry_date'  => $e->entry_date->toDateString(),
                    'user'        => $e->user ? ['id' => $e->user->id, 'name' => $e->user->name] : null,
                ]),
            ],
            'contacts' => Contact::orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Projects', 'href' => '/finance/projects'],
                ['label' => $project->name],
            ],
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:draft,active,completed,cancelled'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
            'contact_id'  => ['nullable', 'exists:contacts,id'],
            'invoice_id'  => ['nullable', 'exists:invoices,id'],
            'starts_on'   => ['nullable', 'date'],
            'ends_on'     => ['nullable', 'date'],
        ]);

        $project->update($validated);

        return redirect()->route('finance.projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('finance.projects.index')
            ->with('success', 'Project deleted.');
    }

    public function storeTimeEntry(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'description' => ['required', 'string'],
            'hours'       => ['required', 'numeric', 'min:0.1'],
            'billable'    => ['boolean'],
            'entry_date'  => ['required', 'date'],
        ]);

        $project->timeEntries()->create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return back()->with('success', 'Time entry logged.');
    }

    public function markBilled(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'entry_ids'   => ['array'],
            'entry_ids.*' => ['exists:project_time_entries,id'],
        ]);

        ProjectTimeEntry::whereIn('id', $validated['entry_ids'] ?? [])
            ->where('project_id', $project->id)
            ->update(['billed' => true]);

        return back()->with('success', 'Entries marked as billed.');
    }
}
