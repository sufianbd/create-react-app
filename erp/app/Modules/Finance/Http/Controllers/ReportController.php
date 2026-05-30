<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Account;
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
