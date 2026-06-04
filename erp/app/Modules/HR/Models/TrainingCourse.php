<?php

namespace App\Modules\HR\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCourse extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['tenant_id', 'title', 'provider', 'type', 'duration_hours', 'description', 'is_active'];

    protected $casts = [
        'is_active'      => 'boolean',
        'duration_hours' => 'float',
    ];

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(EmployeeTrainingRecord::class);
    }
}
