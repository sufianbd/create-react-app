<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLine extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'budget_id', 'account_id', 'period', 'amount', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period' => 'integer',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getActualAmountAttribute(): float
    {
        return 0.0;
    }

    public function getVarianceAttribute(): float
    {
        return $this->actual_amount - (float) $this->amount;
    }
}
