<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'reference', 'contact_id', 'original_invoice_id', 'original_bill_id',
        'type', 'status', 'issue_date', 'currency_code', 'exchange_rate',
        'subtotal', 'tax_total', 'total', 'amount_applied', 'notes',
    ];

    protected $casts = ['issue_date' => 'date'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'original_bill_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function getAmountRemainingAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_applied);
    }

    protected static function booted(): void
    {
        static::saving(function (self $cn) {
            if ($cn->relationLoaded('items') && $cn->items->count() > 0) {
                $cn->subtotal  = $cn->items->sum('line_total');
                $cn->tax_total = $cn->items->sum(fn ($i) => $i->line_total * $i->tax_rate / 100);
                $cn->total     = $cn->subtotal + $cn->tax_total;
            }
        });
    }
}
