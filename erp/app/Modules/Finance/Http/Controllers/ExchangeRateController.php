<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\ExchangeRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExchangeRateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExchangeRate::class);
        $tenantId = $request->user()->tenant_id;
        $rates = ExchangeRate::where('tenant_id', $tenantId)
            ->orderByDesc('date')
            ->orderBy('currency_code')
            ->paginate(50);
        return Inertia::render('Finance/ExchangeRates/Index', ['rates' => $rates]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExchangeRate::class);
        $data = $request->validate([
            'currency_code' => 'required|string|size:3',
            'rate'          => 'required|numeric|min:0.000001',
            'date'          => 'required|date',
        ]);
        $tenantId     = $request->user()->tenant_id;
        $currencyCode = strtoupper($data['currency_code']);
        $date         = $data['date'];

        $existing = ExchangeRate::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('currency_code', $currencyCode)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            $existing->rate = $data['rate'];
            $existing->save();
        } else {
            ExchangeRate::create([
                'tenant_id'     => $tenantId,
                'currency_code' => $currencyCode,
                'date'          => $date,
                'rate'          => $data['rate'],
            ]);
        }

        return back()->with('success', 'Exchange rate saved.');
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $this->authorize('delete', $exchangeRate);
        $exchangeRate->delete();
        return back()->with('success', 'Exchange rate deleted.');
    }
}
