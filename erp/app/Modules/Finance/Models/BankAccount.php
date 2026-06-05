<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'account_number', 'bank_name',
        'currency_code', 'opening_balance',
        'currency', 'current_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'current_balance' => 'float',
        'is_active'       => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function getBalanceAttribute(): float
    {
        return $this->opening_balance + $this->transactions()->sum('amount');
    }

    public function getUnreconciledCountAttribute(): int
    {
        return $this->transactions()->where('reconciled', false)->count();
    }

    public function updateBalance(): void
    {
        $this->current_balance = $this->opening_balance + $this->transactions()->sum('amount');
        $this->save();
    }
}
