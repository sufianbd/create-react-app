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
}
