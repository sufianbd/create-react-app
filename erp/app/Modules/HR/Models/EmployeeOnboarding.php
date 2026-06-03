<?php

namespace App\Modules\HR\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboarding extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['tenant_id', 'employee_id', 'template_id', 'title', 'status', 'started_at', 'completed_at'];

    protected $casts = ['started_at' => 'date', 'completed_at' => 'date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplate::class, 'template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class)->orderBy('sort_order');
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        return (int) round($this->tasks()->whereNotNull('completed_at')->count() / $total * 100);
    }

    /**
     * Instantiate from a template, creating task copies for the employee.
     */
    public static function fromTemplate(Employee $employee, OnboardingTemplate $template): self
    {
        $startDate = $employee->start_date ?? now()->toDateString();

        $onboarding = self::create([
            'tenant_id'   => $employee->tenant_id,
            'employee_id' => $employee->id,
            'template_id' => $template->id,
            'title'       => $template->name,
            'status'      => 'in_progress',
            'started_at'  => $startDate,
        ]);

        foreach ($template->tasks as $task) {
            $dueDate = $task->due_days > 0
                ? \Carbon\Carbon::parse($onboarding->started_at)->addDays($task->due_days)->toDateString()
                : null;

            $onboarding->tasks()->create([
                'title'       => $task->title,
                'description' => $task->description,
                'due_date'    => $dueDate,
                'sort_order'  => $task->sort_order,
            ]);
        }

        return $onboarding;
    }
}
