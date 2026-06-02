<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    protected $table = 'credit_note_items';

    protected $fillable = ['credit_note_id', 'description', 'quantity', 'unit_price', 'tax_rate', 'line_total'];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->line_total = round((float) $item->quantity * (float) $item->unit_price, 2);
        });
    }
}
