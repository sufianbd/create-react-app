<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id',
        'quantity', 'unit_cost', 'received_quantity',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'unit_cost'         => 'decimal:2',
        'received_quantity' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_cost;
    }
}
