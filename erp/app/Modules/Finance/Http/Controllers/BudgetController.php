<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\Account;
use App\Modules\Finance\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Budget::class);

        $budgets = Budget::withCount('lines')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($b) => [
                'id'          => $b->id,
                'name'        => $b->name,
                'year'        => $b->year,
                'period_type' => $b->period_type,
                'status'      => $b->status,
                'lines_count' => $b->lines_count,
            ]);

        return Inertia::render('Finance/Budgets/Index', [
            'budgets' => $budgets,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Budgets'],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Budget::class);

        $accounts = Account::whereIn('type', ['income', 'expense'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Finance/Budgets/Create', [
            'accounts' => $accounts,
            'breadcrumbs' => [
                ['label' => 'Finance'],
                ['label' => 'Budgets', 'href' => '/finance/budgets'],
                ['label' => 'New Budget'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Budget::class);

        $validated = $request->validate([
            'name'               => 'required|string|max:191',
            'year'               => 'required|integer|min:2000|max:2100',
            'period_type'        => 'required|in:annual,monthly,quarterly',
            'notes'              => 'nullable|string',
            'lines'              => 'required|array|min:1',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.period'     => 'required|integer|min:0|max:12',
            'lines.*.amount'     => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $budget = Budget::create([
                'tenant_id'   => $request->user()->tenant_id,
                'name'        => $validated['name'],
                'year'        => $validated['year'],
                'period_type' => $validated['period_type'],
                'notes'       => $validated['notes'] ?? null,
                'status'      => 'draft',
                'created_by'  => $request->user()->id,
            ]);

            foreach ($validated['lines'] as $line) {
                $budget->lines()->create([
                    'account_id' => $line['account_id'],
                    'period'     => $line['period'],
                    'amount'     => $line['amount'],
                    'notes'      => $line['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('finance.budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function show(Budget $budget): Response
    {
        $this->authorize('view', $budget);
        $budget->load(['lines.account']);

        $tenantId = request()->user()->tenant_id;
        $year     = $budget->year;

        // Compute actuals from posted journal entries for this year
        $actuals = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.tenant_id', $tenantId)
            ->where('journal_entries.status', 'posted')
            ->whereYear('journal_entries.date', $year)
            ->whereIn('accounts.type', ['income', 'expense'])
            ->select(
                'journal_lines.account_id',
                'accounts.type',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit'),
            )
            ->groupBy('journal_lines.account_id', 'accounts.type')
            ->get()
            ->keyBy('account_id');

        $lines = $budget->lines->map(function ($line) use ($actuals) {
            $actual = $actuals->get($line->account_id);
            $actualAmount = 0;
            if ($actual) {
                $actualAmount = $actual->type === 'income'
                    ? (float)$actual->total_credit - (float)$actual->total_debit
                    : (float)$actual->total_debit - (float)$actual->total_credit;
            }
            $variance    = $actualAmount - (float)$line->amount;
            $variancePct = $line->amount != 0 ? round($variance / $line->amount * 100, 1) : null;

            return [
                'id'           => $line->id,
                'account_id'   => $line->account_id,
                'account_code' => $line->account->code,
                'account_name' => $line->account->name,
                'account_type' => $line->account->type,
                'period'       => $line->period,
                'budget'       => round((float)$line->amount, 2),
                'actual'       => round($actualAmount, 2),
                'variance'     => round($variance, 2),
                'variance_pct' => $variancePct,
            ];
        });

        return Inertia::render('Finance/Budgets/Show', [
            'budget' => [
                'id'          => $budget->id,
                'name'        => $budget->name,
                'year'        => $budget->year,
                'period_type' => $budget->period_type,
                'status'      => $budget->status,
                'notes'       => $budget->notes,
            ],
            'lines'          => $lines->values(),
            'total_budget'   => $lines->sum('budget'),
            'total_actual'   => $lines->sum('actual'),
            'total_variance' => round($lines->sum('variance'), 2),
            'breadcrumbs'    => [
                ['label' => 'Finance'],
                ['label' => 'Budgets', 'href' => '/finance/budgets'],
                ['label' => $budget->name],
            ],
        ]);
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('finance.budgets.index')
            ->with('success', 'Budget deleted.');
    }
}
