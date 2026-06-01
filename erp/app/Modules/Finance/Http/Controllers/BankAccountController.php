<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BankAccount::class);

        $accounts = BankAccount::where('tenant_id', $request->user()->tenant_id)
            ->get()
            ->map(function (BankAccount $account) {
                return array_merge($account->toArray(), [
                    'balance'           => $account->balance,
                    'unreconciled_count' => $account->unreconciledCount,
                ]);
            });

        return Inertia::render('Finance/BankAccounts/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', BankAccount::class);

        return Inertia::render('Finance/BankAccounts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:255',
            'currency_code'   => 'required|string|size:3',
            'opening_balance' => 'required|numeric',
        ]);

        BankAccount::create([
            ...$data,
            'tenant_id' => $request->user()->tenant_id,
        ]);

        return redirect()->route('finance.bank-accounts.index')
            ->with('success', 'Bank account created.');
    }

    public function show(Request $request, BankAccount $bankAccount): Response
    {
        $this->authorize('view', $bankAccount);

        $transactions = BankTransaction::where('bank_account_id', $bankAccount->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(50);

        return Inertia::render('Finance/BankAccounts/Show', [
            'account'      => $bankAccount->load([]),
            'transactions' => $transactions,
            'balance'      => $bankAccount->balance,
            'unreconciled' => $bankAccount->unreconciledCount,
        ]);
    }

    public function edit(BankAccount $bankAccount): Response
    {
        $this->authorize('update', $bankAccount);

        return Inertia::render('Finance/BankAccounts/Edit', [
            'account' => $bankAccount,
        ]);
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $bankAccount);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:255',
            'currency_code'   => 'required|string|size:3',
            'opening_balance' => 'required|numeric',
        ]);

        $bankAccount->update($data);

        return redirect()->route('finance.bank-accounts.index')
            ->with('success', 'Bank account updated.');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('delete', $bankAccount);

        $bankAccount->delete();

        return redirect()->route('finance.bank-accounts.index')
            ->with('success', 'Bank account deleted.');
    }
}
