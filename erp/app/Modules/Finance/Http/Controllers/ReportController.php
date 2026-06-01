<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\Bill;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\CreditNote;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function trialBalance(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);

        $totals = JournalLine::select('account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = Account::with('parent')
            ->orderBy('code')
            ->get()
            ->map(function (Account $account) use ($totals) {
                $row = $totals->get($account->id);
                return [
                    'id'           => $account->id,
                    'code'         => $account->code,
                    'name'         => $account->name,
                    'type'         => $account->type,
                    'parent_name'  => $account->parent?->name,
                    'total_debit'  => (float) ($row?->total_debit ?? 0),
                    'total_credit' => (float) ($row?->total_credit ?? 0),
                ];
            });

        return Inertia::render('Finance/Reports/TrialBalance', [
            'accounts'    => $accounts,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Reports'],
                ['label' => 'Trial Balance'],
            ],
        ]);
    }

    public function profitAndLoss(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);

        $from = $request->from ?? now()->startOfYear()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $totals = $this->aggregateJournalLines($from, $to);

        $accounts = Account::whereIn('type', ['income', 'expense'])
            ->orderBy('code')
            ->get();

        $revenue  = [];
        $expenses = [];

        foreach ($accounts as $account) {
            $row   = $totals->get($account->id);
            $debit  = (float) ($row?->total_debit  ?? 0);
            $credit = (float) ($row?->total_credit ?? 0);

            $net = $account->type === 'income'
                ? $credit - $debit
                : $debit - $credit;

            $entry = [
                'id'   => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'net'  => $net,
            ];

            if ($account->type === 'income') {
                $revenue[] = $entry;
            } else {
                $expenses[] = $entry;
            }
        }

        $totalRevenue  = (float) array_sum(array_column($revenue, 'net'));
        $totalExpenses = (float) array_sum(array_column($expenses, 'net'));

        return Inertia::render('Finance/Reports/ProfitLoss', [
            'revenue'        => $revenue,
            'expenses'       => $expenses,
            'total_revenue'  => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net'            => $totalRevenue - $totalExpenses,
            'from'           => $from,
            'to'             => $to,
            'breadcrumbs'    => [
                ['label' => 'Finance'],
                ['label' => 'Reports'],
                ['label' => 'Profit & Loss'],
            ],
        ]);
    }

    public function balanceSheet(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);

        $asOf = $request->as_of ?? now()->toDateString();

        $totals = $this->aggregateJournalLines(null, $asOf);

        $accounts = Account::whereIn('type', ['asset', 'liability', 'equity'])
            ->orderBy('code')
            ->get();

        $assets      = [];
        $liabilities = [];
        $equity      = [];

        foreach ($accounts as $account) {
            $row    = $totals->get($account->id);
            $debit  = (float) ($row?->total_debit  ?? 0);
            $credit = (float) ($row?->total_credit ?? 0);

            $net = $account->type === 'asset'
                ? $debit - $credit
                : $credit - $debit;

            $entry = [
                'id'   => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'net'  => $net,
            ];

            if ($account->type === 'asset') {
                $assets[] = $entry;
            } elseif ($account->type === 'liability') {
                $liabilities[] = $entry;
            } else {
                $equity[] = $entry;
            }
        }

        $totalAssets      = (float) array_sum(array_column($assets,      'net'));
        $totalLiabilities = (float) array_sum(array_column($liabilities, 'net'));
        $totalEquity      = (float) array_sum(array_column($equity,      'net'));

        return Inertia::render('Finance/Reports/BalanceSheet', [
            'assets'            => $assets,
            'liabilities'       => $liabilities,
            'equity'            => $equity,
            'total_assets'      => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity'      => $totalEquity,
            'as_of'             => $asOf,
            'breadcrumbs'       => [
                ['label' => 'Finance'],
                ['label' => 'Reports'],
                ['label' => 'Balance Sheet'],
            ],
        ]);
    }

    public function agedReceivables(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);
        $asOf = $request->as_of ?? now()->toDateString();

        $invoices = Invoice::with(['contact', 'items', 'payments'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get()
            ->map(function ($inv) use ($asOf) {
                $daysOverdue = 0;
                if ($inv->due_date) {
                    $diff = \Carbon\Carbon::parse($asOf)->diffInDays($inv->due_date, false);
                    $daysOverdue = (int) max(0, $diff * -1);
                }
                $bucket = match(true) {
                    $daysOverdue === 0   => 'current',
                    $daysOverdue <= 30   => '1-30',
                    $daysOverdue <= 60   => '31-60',
                    $daysOverdue <= 90   => '61-90',
                    default              => '90+',
                };
                return [
                    'id'          => $inv->id,
                    'number'      => $inv->number,
                    'contact'     => $inv->contact?->name ?? '—',
                    'due_date'    => $inv->due_date?->toDateString(),
                    'amount_due'  => (float) $inv->amount_due,
                    'days_overdue'=> $daysOverdue,
                    'bucket'      => $bucket,
                ];
            });

        $bucketKeys = ['current', '1-30', '31-60', '61-90', '90+'];
        $totals = collect($bucketKeys)->mapWithKeys(fn ($k) =>
            [$k => (float) $invoices->where('bucket', $k)->sum('amount_due')]
        )->all();

        return Inertia::render('Finance/Reports/AgedReceivables', [
            'rows'        => $invoices->values(),
            'totals'      => $totals,
            'grand_total' => (float) $invoices->sum('amount_due'),
            'as_of'       => $asOf,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Reports'],
                ['label' => 'Aged Receivables'],
            ],
        ]);
    }

    public function agedPayables(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);
        $asOf = $request->as_of ?? now()->toDateString();

        $bills = Bill::with(['contact', 'items', 'payments'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get()
            ->map(function ($bill) use ($asOf) {
                $daysOverdue = 0;
                if ($bill->due_date) {
                    $diff = \Carbon\Carbon::parse($asOf)->diffInDays($bill->due_date, false);
                    $daysOverdue = (int) max(0, $diff * -1);
                }
                $bucket = match(true) {
                    $daysOverdue === 0   => 'current',
                    $daysOverdue <= 30   => '1-30',
                    $daysOverdue <= 60   => '31-60',
                    $daysOverdue <= 90   => '61-90',
                    default              => '90+',
                };
                return [
                    'id'          => $bill->id,
                    'number'      => $bill->number,
                    'contact'     => $bill->contact?->name ?? '—',
                    'due_date'    => $bill->due_date?->toDateString(),
                    'amount_due'  => (float) $bill->amount_due,
                    'days_overdue'=> $daysOverdue,
                    'bucket'      => $bucket,
                ];
            });

        $bucketKeys = ['current', '1-30', '31-60', '61-90', '90+'];
        $totals = collect($bucketKeys)->mapWithKeys(fn ($k) =>
            [$k => (float) $bills->where('bucket', $k)->sum('amount_due')]
        )->all();

        return Inertia::render('Finance/Reports/AgedPayables', [
            'rows'        => $bills->values(),
            'totals'      => $totals,
            'grand_total' => (float) $bills->sum('amount_due'),
            'as_of'       => $asOf,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Reports'],
                ['label' => 'Aged Payables'],
            ],
        ]);
    }

    public function accountLedgerIndex(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);
        $accounts = Account::orderBy('code')->get(['id', 'code', 'name', 'type']);
        return Inertia::render('Finance/Reports/AccountLedger', [
            'accounts'    => $accounts,
            'account'     => null,
            'rows'        => [],
            'from'        => now()->startOfYear()->toDateString(),
            'to'          => now()->toDateString(),
            'breadcrumbs' => [['label' => 'Finance'], ['label' => 'Reports'], ['label' => 'Account Ledger']],
        ]);
    }

    public function accountLedger(Request $request, Account $account): Response
    {
        $this->authorize('viewAny', Account::class);
        $from = $request->from ?? now()->startOfYear()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        // Use JOIN (not whereHas) so ordering by journal_entries.date works correctly
        $lines = JournalLine::join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_entries.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('journal_entries.date', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('journal_entries.date', '<=', $to))
            ->orderBy('journal_entries.date')
            ->orderBy('journal_lines.id')
            ->select('journal_lines.*', 'journal_entries.date as entry_date',
                     'journal_entries.reference as entry_reference',
                     'journal_entries.description as entry_description')
            ->get();

        $isDebitNormal = in_array($account->type, ['asset', 'expense'], true);
        $runningBalance = 0.0;
        $rows = [];
        foreach ($lines as $line) {
            $debit  = (float) $line->debit;
            $credit = (float) $line->credit;
            $runningBalance += $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
            $rows[] = [
                'id'          => $line->id,
                'date'        => $line->entry_date instanceof \Carbon\Carbon
                                  ? $line->entry_date->toDateString()
                                  : (string) $line->entry_date,
                'reference'   => $line->entry_reference,
                'description' => $line->description ?? $line->entry_description,
                'debit'       => $debit,
                'credit'      => $credit,
                'balance'     => $runningBalance,
            ];
        }

        $accounts = Account::orderBy('code')->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Finance/Reports/AccountLedger', [
            'accounts'    => $accounts,
            'account'     => ['id' => $account->id, 'code' => $account->code, 'name' => $account->name, 'type' => $account->type],
            'rows'        => $rows,
            'from'        => $from,
            'to'          => $to,
            'breadcrumbs' => [
                ['label' => 'Finance'], ['label' => 'Reports'],
                ['label' => "Ledger: {$account->name}"],
            ],
        ]);
    }

    public function customerStatementIndex(Request $request): Response
    {
        $this->authorize('viewAny', Account::class);

        return Inertia::render('Finance/Reports/CustomerStatement', [
            'contacts'    => Contact::customers()->orderBy('name')->get(['id', 'name']),
            'contact'     => null,
            'rows'        => [],
            'from'        => now()->startOfYear()->toDateString(),
            'to'          => now()->toDateString(),
            'breadcrumbs' => [['label' => 'Finance'], ['label' => 'Reports'], ['label' => 'Customer Statement']],
        ]);
    }

    public function customerStatement(Request $request, Contact $contact): Response
    {
        $this->authorize('viewAny', Account::class);

        $from = $request->from ?? now()->startOfYear()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $transactions = [];

        $invoices = Invoice::with(['items', 'payments'])
            ->where('contact_id', $contact->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->get();

        foreach ($invoices as $invoice) {
            $transactions[] = [
                'date'      => $invoice->issue_date?->toDateString(),
                'type'      => 'Invoice',
                'reference' => $invoice->number,
                'debit'     => (float) $invoice->total,
                'credit'    => 0.0,
            ];
        }

        $paymentInvoices = Invoice::with('payments')
            ->where('contact_id', $contact->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($paymentInvoices as $invoice) {
            foreach ($invoice->payments as $payment) {
                $paymentDate = $payment->payment_date instanceof \Carbon\Carbon
                    ? $payment->payment_date->toDateString()
                    : (string) $payment->payment_date;

                if ($paymentDate < $from || $paymentDate > $to) {
                    continue;
                }

                $transactions[] = [
                    'date'      => $paymentDate,
                    'type'      => 'Payment',
                    'reference' => $invoice->number,
                    'debit'     => 0.0,
                    'credit'    => (float) $payment->amount,
                ];
            }
        }

        $creditNotes = CreditNote::with('items')
            ->where('contact_id', $contact->id)
            ->whereIn('status', ['issued', 'applied'])
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->get();

        foreach ($creditNotes as $creditNote) {
            $transactions[] = [
                'date'      => $creditNote->issue_date?->toDateString(),
                'type'      => 'Credit Note',
                'reference' => $creditNote->number,
                'debit'     => 0.0,
                'credit'    => (float) $creditNote->total,
            ];
        }

        usort($transactions, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        $balance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $rows = [];
        foreach ($transactions as $txn) {
            $balance += $txn['debit'] - $txn['credit'];
            $totalDebit += $txn['debit'];
            $totalCredit += $txn['credit'];
            $rows[] = array_merge($txn, ['balance' => $balance]);
        }

        return Inertia::render('Finance/Reports/CustomerStatement', [
            'contacts'        => Contact::customers()->orderBy('name')->get(['id', 'name']),
            'contact'         => ['id' => $contact->id, 'name' => $contact->name],
            'rows'            => $rows,
            'from'            => $from,
            'to'              => $to,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'closing_balance' => $balance,
            'breadcrumbs'     => [
                ['label' => 'Finance'], ['label' => 'Reports'],
                ['label' => "Statement: {$contact->name}"],
            ],
        ]);
    }

    public function vatReport(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $tenantId = $request->user()->tenant_id;
        $from     = $request->query('from', now()->startOfQuarter()->toDateString());
        $to       = $request->query('to',   now()->endOfQuarter()->toDateString());

        // Output VAT: tax collected on invoices (not cancelled) within the period
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('issue_date', [$from, $to])
            ->with('items')
            ->get();

        $outputLines = $invoices->map(function ($invoice) {
            $net = $invoice->subtotal;
            $tax = $invoice->tax_total;
            return [
                'id'      => $invoice->id,
                'number'  => $invoice->number,
                'date'    => $invoice->issue_date,
                'contact' => $invoice->contact?->name,
                'net'     => round($net, 2),
                'tax'     => round($tax, 2),
                'type'    => 'invoice',
            ];
        })->filter(fn ($line) => $line['tax'] != 0)->values();

        // Input VAT: tax paid on bills (not cancelled) within the period
        $bills = Bill::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('issue_date', [$from, $to])
            ->with('items')
            ->get();

        $inputLines = $bills->map(function ($bill) {
            $net = $bill->subtotal;
            $tax = $bill->tax_total;
            return [
                'id'      => $bill->id,
                'number'  => $bill->number,
                'date'    => $bill->issue_date,
                'contact' => $bill->contact?->name,
                'net'     => round($net, 2),
                'tax'     => round($tax, 2),
                'type'    => 'bill',
            ];
        })->filter(fn ($line) => $line['tax'] != 0)->values();

        $totalOutputVat = round($outputLines->sum('tax'), 2);
        $totalInputVat  = round($inputLines->sum('tax'), 2);
        $netVat         = round($totalOutputVat - $totalInputVat, 2);

        return Inertia::render('Finance/Reports/VatReport', [
            'output_lines'     => $outputLines,
            'input_lines'      => $inputLines,
            'total_output_vat' => $totalOutputVat,
            'total_input_vat'  => $totalInputVat,
            'net_vat'          => $netVat,
            'from'             => $from,
            'to'               => $to,
        ]);
    }

    private function aggregateJournalLines(?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        return JournalLine::select('account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->whereHas('journalEntry', function ($q) use ($from, $to) {
                $q->where('status', 'posted');
                if ($from) $q->whereDate('date', '>=', $from);
                if ($to)   $q->whereDate('date', '<=', $to);
            })
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');
    }
}
