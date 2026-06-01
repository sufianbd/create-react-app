<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'year', 'period_type', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
