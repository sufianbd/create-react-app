<?php

namespace App\Modules\HR\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class PerformanceReview extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'performance_reviews';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'reviewer_id',
        'review_period',
        'review_date',
        'status',
        'overall_rating',
        'strengths',
        'improvements',
        'goals',
        'reviewer_notes',
    ];

    protected $casts = [
        'review_date'    => 'date',
        'overall_rating' => 'decimal:1',
        'status'         => 'string',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(PerformanceKpi::class);
    }

    public function submit(): void
    {
        $this->status = 'submitted';
        $this->save();
    }

    public function acknowledge(): void
    {
        $this->status = 'acknowledged';
        $this->save();
    }

    public function getAverageKpiScoreAttribute(): ?float
    {
        $kpis = $this->kpis->filter(fn ($kpi) => $kpi->target_score > 0);

        if ($kpis->isEmpty()) {
            return null;
        }

        $total = $kpis->sum(fn ($kpi) => ($kpi->actual_score / $kpi->target_score) * 100);

        return round($total / $kpis->count(), 1);
    }
}
