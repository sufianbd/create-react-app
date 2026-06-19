<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Record an audit log entry.
     *
     * @param  string       $action
     * @param  Model|null   $model
     * @param  array        $oldValues
     * @param  array        $newValues
     * @param  mixed        $moduleOrTenantId  ignored (kept for backward compat)
     * @return static
     */
    public static function record(
        string $action,
        $model = null,
        array $oldValues = [],
        array $newValues = [],
        $moduleOrTenantId = null
    ): static {
        $tenantId = null;

        if (is_int($moduleOrTenantId)) {
            $tenantId = $moduleOrTenantId;
        }

        if ($tenantId === null) {
            $tenantId = $model->tenant_id ?? auth()->user()?->tenant_id ?? 0;
        }

        return static::create([
            'tenant_id'      => $tenantId,
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->getKey(),
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
        ]);
    }

    /**
     * Get a human-readable summary of changes.
     */
    public function getChangeSummaryAttribute(): string
    {
        if ($this->old_values && $this->new_values) {
            $changedKeys = array_keys(array_diff_assoc(
                (array) $this->new_values,
                (array) $this->old_values
            ));

            if (empty($changedKeys)) {
                $changedKeys = array_keys((array) $this->new_values);
            }

            return implode(', ', $changedKeys) . ' changed';
        }

        return $this->action ?? '';
    }
}
