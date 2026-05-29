<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRun extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'period_start', 'period_end', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    protected $attributes = ['status' => 'draft'];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalGrossAttribute(): float
    {
        return (float) $this->items->sum('gross_salary');
    }

    public function getTotalNetAttribute(): float
    {
        return (float) $this->items->sum('net_salary');
    }

    public function process(): void
    {
        if ($this->status === 'processed') {
            throw new \DomainException('Payroll run is already processed.');
        }

        $this->update(['status' => 'processed']);
    }
}
