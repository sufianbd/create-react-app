<?php

namespace App\Modules\HR\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'department_id',
        'location',
        'employment_type',
        'description',
        'requirements',
        'openings',
        'status',
        'posted_at',
        'closed_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getOpenApplicationsCountAttribute(): int
    {
        return $this->applications()->whereNotIn('stage', ['hired', 'rejected'])->count();
    }

    public function publish(): void
    {
        $this->status = 'open';
        $this->posted_at = now();
        $this->save();
    }

    public function close(): void
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->save();
    }
}
