<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeEntry extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'description',
        'hours',
        'billable',
        'billed',
        'entry_date',
    ];

    protected $casts = [
        'hours'      => 'float',
        'billable'   => 'boolean',
        'billed'     => 'boolean',
        'entry_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
