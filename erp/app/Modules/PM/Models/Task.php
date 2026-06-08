<?php

namespace App\Modules\PM\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'tenant_id', 'project_id', 'title', 'description',
        'status', 'priority', 'assignee_id', 'due_date',
        'estimated_hours', 'actual_hours', 'sequence', 'created_by',
    ];

    protected $casts = [
        'due_date'        => 'date',
        'estimated_hours' => 'float',
        'actual_hours'    => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function complete(): void
    {
        $this->status = 'done';
        $this->save();
    }

    public function isOverdue(): bool
    {
        if (is_null($this->due_date)) {
            return false;
        }
        return $this->due_date->lt(now()->startOfDay())
            && !in_array($this->status, ['done', 'cancelled']);
    }
}
