<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\TenantSetting;
use App\Modules\Finance\Http\Requests\StoreInvoiceRequest;
use App\Modules\Finance\Http\Requests\StorePaymentRequest;
use App\Modules\Finance\Http\Resources\InvoiceResource;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceItem;
use App\Modules\Finance\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::with('contact')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->contact_id, fn ($q) => $q->where('contact_id', $request->contact_id))
            ->when($request->search, fn ($q) => $q->where('number', 'like', "%{$request->search}%"))
            ->latest('issue_date')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Finance/Invoices/Index', [
            'invoices'    => InvoiceResource::collection($invoices),
            'contacts'    => Contact::customers()->active()->orderBy('name')->get(['id', 'name']),
            'filters'     => $request->only(['status', 'contact_id', 'search']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Invoices', 'href' => route('finance.invoices.index')],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Invoice::class);

        return Inertia::render('Finance/Invoices/Create', [
            'contacts'    => Contact::customers()->active()->orderBy('name')->get(['id', 'name']),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Invoices', 'href' => route('finance.invoices.index')],
                ['label' => 'New Invoice'],
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $request->validated();

        $invoice = DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'contact_id' => $data['contact_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date'   => $data['due_date'] ?? null,
                'notes'      => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $invoice->update([
                'number' => 'INV-' . now()->format('Y') . '-' . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'],
                ]);
            }

            return $invoice;
        });

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['contact', 'items', 'payments', 'creator']);

        return Inertia::render('Finance/Invoices/Show', [
            'invoice'     => new InvoiceResource($invoice),
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Invoices', 'href' => route('finance.invoices.index')],
                ['label' => $invoice->number ?? "Invoice #{$invoice->id}"],
            ],
        ]);
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        try {
            $invoice->transitionTo('sent');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Invoice marked as sent.');
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        try {
            $invoice->transitionTo('cancelled');
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Invoice cancelled.');
    }

    public function recordPayment(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $data = $request->validated();

        DB::transaction(function () use ($data, $invoice) {
            Payment::create([
                'tenant_id'    => auth()->user()->tenant_id,
                'invoice_id'   => $invoice->id,
                'amount'       => $data['amount'],
                'payment_date' => $data['payment_date'],
                'method'       => $data['method'],
                'reference'    => $data['reference'] ?? null,
                'notes'        => $data['notes'] ?? null,
            ]);

            $invoice->load(['items', 'payments']);

            if ($invoice->amount_due <= 0 && $invoice->canTransitionTo('paid')) {
                $invoice->transitionTo('paid');
            }
        });

        return back()->with('success', 'Payment recorded.');
    }

    public function print(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['contact', 'items', 'payments', 'creator']);

        $tenantId = auth()->user()->tenant_id;

        return Inertia::render('Finance/Invoices/Print', [
            'invoice'  => new InvoiceResource($invoice),
            'company'  => TenantSetting::getValue($tenantId, 'company_name', 'My Company'),
            'currency' => TenantSetting::getValue($tenantId, 'currency', 'USD'),
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()->route('finance.invoices.index')
            ->with('success', 'Invoice deleted.');
    }
}
