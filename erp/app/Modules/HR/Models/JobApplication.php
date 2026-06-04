<?php

namespace App\Modules\HR\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'job_position_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'resume_path',
        'cover_letter',
        'source',
        'stage',
        'notes',
        'rating',
        'rejected_at',
        'hired_at',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
        'hired_at'    => 'datetime',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function advance(string $stage): void
    {
        $this->stage = $stage;
        if ($stage === 'hired') {
            $this->hired_at = now();
        }
        if ($stage === 'rejected') {
            $this->rejected_at = now();
        }
        $this->save();
    }

    public function reject(?string $reason = null): void
    {
        $this->advance('rejected');
        if ($reason !== null) {
            $this->notes = trim(($this->notes ? $this->notes . "\n" : '') . $reason);
            $this->save();
        }
    }
}
