<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::with('contact')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type,   fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Finance/Contracts/Index', [
            'contracts'  => $contracts,
            'filters'    => $request->only(['status', 'type']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Contracts', 'href' => route('finance.contracts.index')],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Contract::class);

        return Inertia::render('Finance/Contracts/Create', [
            'contacts'   => Contact::orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Contracts', 'href' => route('finance.contracts.index')],
                ['label' => 'New Contract'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'reference'           => ['nullable', 'string', 'max:100'],
            'contact_id'          => ['nullable', Rule::exists('contacts', 'id')],
            'type'                => ['required', Rule::in(['client', 'vendor', 'employment', 'nda', 'other'])],
            'status'              => ['nullable', Rule::in(['draft', 'active', 'expired', 'terminated'])],
            'value'               => ['nullable', 'numeric', 'min:0'],
            'currency_code'       => ['nullable', 'string', 'size:3'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'auto_renew'          => ['boolean'],
            'renewal_notice_days' => ['nullable', 'integer', 'min:0'],
            'description'         => ['nullable', 'string'],
            'terms'               => ['nullable', 'string'],
        ]);

        $contract = Contract::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$data,
        ]);

        return redirect()->route('finance.contracts.show', $contract)
            ->with('success', 'Contract created.');
    }

    public function show(Contract $contract): Response
    {
        $this->authorize('view', $contract);

        $contract->load('contact');

        return Inertia::render('Finance/Contracts/Show', [
            'contract'   => $contract,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Contracts', 'href' => route('finance.contracts.index')],
                ['label' => $contract->title],
            ],
        ]);
    }

    public function edit(Contract $contract): Response
    {
        $this->authorize('create', Contract::class);

        return Inertia::render('Finance/Contracts/Edit', [
            'contract'   => $contract,
            'contacts'   => Contact::orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Contracts', 'href' => route('finance.contracts.index')],
                ['label' => $contract->title, 'href' => route('finance.contracts.show', $contract)],
                ['label' => 'Edit'],
            ],
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'reference'           => ['nullable', 'string', 'max:100'],
            'contact_id'          => ['nullable', Rule::exists('contacts', 'id')],
            'type'                => ['required', Rule::in(['client', 'vendor', 'employment', 'nda', 'other'])],
            'status'              => ['nullable', Rule::in(['draft', 'active', 'expired', 'terminated'])],
            'value'               => ['nullable', 'numeric', 'min:0'],
            'currency_code'       => ['nullable', 'string', 'size:3'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'auto_renew'          => ['boolean'],
            'renewal_notice_days' => ['nullable', 'integer', 'min:0'],
            'description'         => ['nullable', 'string'],
            'terms'               => ['nullable', 'string'],
        ]);

        $contract->update($data);

        return redirect()->route('finance.contracts.show', $contract)
            ->with('success', 'Contract updated.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $this->authorize('delete', $contract);

        $contract->delete();

        return redirect()->route('finance.contracts.index')
            ->with('success', 'Contract deleted.');
    }

    public function activate(Contract $contract): RedirectResponse
    {
        $this->authorize('create', Contract::class);

        $contract->activate();

        return back()->with('success', 'Contract activated.');
    }

    public function terminate(Contract $contract): RedirectResponse
    {
        $this->authorize('create', Contract::class);

        $contract->terminate();

        return back()->with('success', 'Contract terminated.');
    }
}
