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

    protected $fillable = [
        'tenant_id', 'employee_id', 'reviewer_id', 'period_start', 'period_end',
        'status', 'overall_rating', 'comments', 'completed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(PerformanceReviewGoal::class);
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(PerformanceReviewCompetency::class);
    }

    public function getAverageCompetencyRatingAttribute(): ?float
    {
        $ratings = $this->competencies->whereNotNull('rating')->pluck('rating');
        if ($ratings->isEmpty()) {
            return null;
        }
        return round($ratings->avg(), 1);
    }
}
