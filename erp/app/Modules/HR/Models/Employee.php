<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'user_id', 'department_id', 'employee_number',
        'first_name', 'last_name', 'email', 'phone', 'position',
        'employment_type', 'status', 'start_date', 'end_date',
        'salary_type', 'salary_amount',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'salary_amount' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('employee_number', 'like', "%{$term}%");
        });
    }
}
