<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'employee_id', 'leave_type_id',
        'start_date', 'end_date', 'days', 'status', 'notes',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = ['status' => 'pending'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Alias for reviewer (spec: approver()) */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** approved_by accessor -> reviewed_by */
    public function getApprovedByAttribute()
    {
        return $this->reviewed_by;
    }

    /** approved_at accessor -> reviewed_at */
    public function getApprovedAtAttribute()
    {
        return $this->reviewed_at;
    }

    /** days accessor */
    public function getDaysAttribute(): int
    {
        if (isset($this->attributes['days']) && $this->attributes['days'] !== null) {
            return (int) $this->attributes['days'];
        }
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }

    public function approve(User $reviewer): void
    {
        if ($this->status !== 'pending') {
            throw new \DomainException("Leave request is already {$this->status}.");
        }

        $this->update([
            'status'      => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    public function reject(User $reviewer): void
    {
        if ($this->status !== 'pending') {
            throw new \DomainException("Leave request is already {$this->status}.");
        }

        $this->update([
            'status'      => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
