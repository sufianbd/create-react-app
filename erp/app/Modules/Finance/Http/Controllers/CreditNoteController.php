<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Http\Requests\StoreCreditNoteRequest;
use App\Modules\Finance\Http\Resources\CreditNoteResource;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\CreditNote;
use App\Modules\Finance\Models\CreditNoteItem;
use App\Modules\Finance\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CreditNoteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CreditNote::class);

        $creditNotes = CreditNote::with(['contact', 'invoice'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->contact_id, fn ($q) => $q->where('contact_id', $request->contact_id))
            ->when($request->search, fn ($q) => $q->where('number', 'like', "%{$request->search}%"))
            ->latest('issue_date')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Finance/CreditNotes/Index', [
            'creditNotes' => CreditNoteResource::collection($creditNotes),
            'contacts'    => Contact::customers()->active()->orderBy('name')->get(['id', 'name']),
            'filters'     => $request->only(['status', 'contact_id', 'search']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Credit Notes', 'href' => route('finance.credit-notes.index')],
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', CreditNote::class);

        $sourceInvoice = null;
        if ($request->invoice_id) {
            $invoice = Invoice::with(['items', 'contact'])->find($request->invoice_id);
            if ($invoice) {
                $sourceInvoice = [
                    'id'      => $invoice->id,
                    'number'  => $invoice->number,
                    'contact' => $invoice->contact ? [
                        'id' => $invoice->contact->id, 'name' => $invoice->contact->name,
                    ] : null,
                    'items'   => $invoice->items->map(fn ($item) => [
                        'description' => $item->description,
                        'quantity'    => $item->quantity,
                        'unit_price'  => $item->unit_price,
                        'tax_rate'    => $item->tax_rate,
                    ]),
                ];
            }
        }

        $invoices = Invoice::with('contact')
            ->latest('issue_date')
            ->get(['id', 'number', 'contact_id'])
            ->map(fn ($invoice) => [
                'id'           => $invoice->id,
                'number'       => $invoice->number,
                'contact_name' => $invoice->contact?->name,
            ]);

        return Inertia::render('Finance/CreditNotes/Create', [
            'contacts'      => Contact::customers()->active()->orderBy('name')->get(['id', 'name']),
            'invoices'      => $invoices,
            'sourceInvoice' => $sourceInvoice,
            'breadcrumbs'   => [
                ['label' => 'Finance'],
                ['label' => 'Credit Notes', 'href' => route('finance.credit-notes.index')],
                ['label' => 'New Credit Note'],
            ],
        ]);
    }

    public function store(StoreCreditNoteRequest $request): RedirectResponse
    {
        $this->authorize('create', CreditNote::class);

        $data = $request->validated();

        $creditNote = DB::transaction(function () use ($data) {
            $creditNote = CreditNote::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'contact_id' => $data['contact_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'reason'     => $data['reason'] ?? null,
                'notes'      => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $creditNote->update([
                'number' => 'CN-' . now()->format('Y') . '-' . str_pad((string) $creditNote->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['items'] as $item) {
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'description'    => $item['description'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'tax_rate'       => $item['tax_rate'],
                ]);
            }

            return $creditNote;
        });

        return redirect()->route('finance.credit-notes.show', $creditNote)
            ->with('success', 'Credit note created.');
    }

    public function show(CreditNote $creditNote): Response
    {
        $this->authorize('view', $creditNote);

        $creditNote->load(['contact', 'invoice', 'items', 'creator']);

        return Inertia::render('Finance/CreditNotes/Show', [
            'creditNote'  => new CreditNoteResource($creditNote),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Credit Notes', 'href' => route('finance.credit-notes.index')],
                ['label' => $creditNote->number ?? "Credit Note #{$creditNote->id}"],
            ],
        ]);
    }

    public function issue(CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        try {
            $creditNote->transitionTo('issued');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Credit note issued.');
    }

    public function apply(CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        try {
            $creditNote->transitionTo('applied');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Credit note applied.');
    }

    public function cancel(CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        try {
            $creditNote->transitionTo('cancelled');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Credit note cancelled.');
    }

    public function destroy(CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('delete', $creditNote);

        $creditNote->delete();

        return redirect()->route('finance.credit-notes.index')
            ->with('success', 'Credit note deleted.');
    }
}
