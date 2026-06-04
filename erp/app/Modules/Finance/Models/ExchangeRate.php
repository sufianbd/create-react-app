<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'base_currency',
        'quote_currency',
        'rate',
        'effective_date',
        'source',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'rate'           => 'decimal:6',
    ];

    /**
     * Get the most recent rate on or before $date where base=from AND quote=to.
     * Returns the rate as float, or null if not found.
     */
    public static function getRate(int $tenantId, string $from, string $to, ?string $date = null): ?float
    {
        $date = $date ?? now()->toDateString();

        $value = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('base_currency', $from)
            ->where('quote_currency', $to)
            ->where('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('rate');

        return $value !== null ? (float) $value : null;
    }

    /**
     * Convert an amount from one currency to another.
     * Returns null if no rate found.
     */
    public static function convert(float $amount, string $from, string $to, int $tenantId, ?string $date = null): ?float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = static::getRate($tenantId, $from, $to, $date);

        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }

    /**
     * Legacy helper: get rate for a currency code vs USD.
     * Returns 1.0 if currency is USD or no rate found.
     */
    public static function rateFor(string $currencyCode, string $tenantIdOrDate, ?string $date = null): float
    {
        if ($currencyCode === 'USD') {
            return 1.0;
        }

        if ($date === null) {
            $date     = $tenantIdOrDate;
            $tenantId = app('tenant')->id;
        } else {
            $tenantId = (int) $tenantIdOrDate;
        }

        $record = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($currencyCode) {
                $q->where(function ($q2) use ($currencyCode) {
                    $q2->where('base_currency', 'USD')
                       ->where('quote_currency', $currencyCode);
                })->orWhere(function ($q2) use ($currencyCode) {
                    $q2->where('base_currency', $currencyCode)
                       ->where('quote_currency', 'USD');
                });
            })
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->first();

        if (!$record) {
            return 1.0;
        }

        // If base is USD, quote is the currency: rate = quote per USD
        if ($record->base_currency === 'USD') {
            return (float) $record->rate;
        }

        // If base is currency and quote is USD: inverse
        return $record->rate != 0 ? round(1 / (float) $record->rate, 6) : 1.0;
    }
}
