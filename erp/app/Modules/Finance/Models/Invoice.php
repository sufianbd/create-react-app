<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'contact_id', 'number',
        'issue_date', 'due_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
    ];

    protected $attributes = ['status' => 'draft'];

    private const TRANSITIONS = [
        'draft'     => ['sent', 'cancelled'],
        'sent'      => ['paid', 'cancelled'],
        'paid'      => [],
        'cancelled' => [],
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
    }

    public function getTaxTotalAttribute(): float
    {
        return $this->items->sum(function ($i) {
            $sub = (float) $i->quantity * (float) $i->unit_price;
            return $sub * ((float) $i->tax_rate / 100);
        });
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->tax_total;
    }

    public function getAmountPaidAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function getAmountDueAttribute(): float
    {
        return $this->total - $this->amount_paid;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && now()->startOfDay()->gt($this->due_date)
            && ! in_array($this->status, ['paid', 'cancelled'], true);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function availableTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function transitionTo(string $status): void
    {
        if (! $this->canTransitionTo($status)) {
            throw new \DomainException(
                "Cannot transition invoice from '{$this->status}' to '{$status}'."
            );
        }

        $this->update(['status' => $status]);
    }
}
