<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'contact_id', 'title', 'reference', 'type', 'status',
        'value', 'currency_code', 'start_date', 'end_date', 'auto_renew',
        'renewal_notice_days', 'description', 'terms', 'signed_at',
    ];

    protected $casts = [
        'value'      => 'decimal:2',
        'start_date' => 'date',
        'end_date'   => 'date',
        'signed_at'  => 'date',
        'auto_renew' => 'boolean',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->status === 'active'
            && $this->end_date !== null
            && $this->end_date->diffInDays(now(), false) >= -$this->renewal_notice_days
            && $this->end_date->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }

    public function activate(): void
    {
        $this->status    = 'active';
        $this->signed_at = $this->signed_at ?? today();
        $this->save();
    }

    public function terminate(): void
    {
        $this->status = 'terminated';
        $this->save();
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
