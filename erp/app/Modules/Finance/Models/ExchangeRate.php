<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'currency_code', 'rate', 'date'];
    protected $casts    = ['date' => 'date', 'rate' => 'float'];

    /**
     * Get the most recent rate for a currency on or before a given date.
     * Returns 1.0 if currency is USD (base) or no rate found.
     */
    public static function rateFor(string $currencyCode, string $tenantIdOrDate, ?string $date = null): float
    {
        if ($currencyCode === 'USD') return 1.0;

        // Support both: rateFor('EUR', $tenantId, $date) and rateFor('EUR', $date) with tenant from app()
        if ($date === null) {
            $date     = $tenantIdOrDate;
            $tenantId = app('tenant')->id;
        } else {
            $tenantId = $tenantIdOrDate;
        }

        $record = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('currency_code', $currencyCode)
            ->whereDate('date', '<=', $date)
            ->orderByDesc('date')
            ->first();

        return $record ? (float) $record->rate : 1.0;
    }
}
