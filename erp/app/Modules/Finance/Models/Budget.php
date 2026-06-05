<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'fiscal_year', 'year', 'period_type', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'year'        => 'integer',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function close(): void
    {
        $this->status = 'closed';
        $this->save();
    }

    public function getTotalBudgetedAttribute(): float
    {
        return (float) $this->lines->sum('budgeted_amount');
    }

    public function getTotalActualAttribute(): float
    {
        return (float) $this->lines->sum('actual_amount');
    }

    public function getTotalVarianceAttribute(): float
    {
        return $this->total_actual - $this->total_budgeted;
    }

    public function getVariancePercentAttribute(): float
    {
        $budgeted = $this->total_budgeted;
        if ($budgeted == 0) {
            return 0.0;
        }
        return round(($this->total_variance / abs($budgeted)) * 100, 1);
    }
}
