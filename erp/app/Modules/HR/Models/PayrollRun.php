<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRun extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'period_start', 'period_end', 'status', 'notes', 'created_by',
        'period_label', 'total_gross', 'total_deductions', 'total_net',
        'employee_count', 'processed_at',
    ];

    protected $casts = [
        'period_start'  => 'date',
        'period_end'    => 'date',
        'processed_at'  => 'datetime',
        'total_gross'   => 'decimal:2',
        'total_net'     => 'decimal:2',
        'total_deductions' => 'decimal:2',
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
        // If items are loaded, compute from items; otherwise use stored value
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (float) $this->items->sum('gross_salary');
        }
        return (float) ($this->attributes['total_gross'] ?? 0);
    }

    public function getTotalNetAttribute(): float
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (float) $this->items->sum('net_salary');
        }
        return (float) ($this->attributes['total_net'] ?? 0);
    }

    /**
     * Process the payroll run.
     * Computes totals from active employees' salary_amount for this tenant.
     */
    public function process(): void
    {
        if ($this->status !== 'draft') {
            throw new \DomainException('Payroll run is already processed.');
        }

        // Compute totals from active employees for this tenant
        $employees = Employee::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant_id)
            ->where('status', 'active')
            ->get();

        $totalGross = $employees->sum('salary_amount');
        $totalDeductions = 0;
        $totalNet = $totalGross - $totalDeductions;
        $employeeCount = $employees->count();

        $this->update([
            'status'         => 'processed',
            'total_gross'    => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'      => $totalNet,
            'employee_count' => $employeeCount,
            'processed_at'   => now(),
        ]);
    }
}
