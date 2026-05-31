<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use App\Modules\Finance\Traits\HasLineItemTotals;
use App\Modules\Finance\Traits\HasStatusTransitions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;
    use HasLineItemTotals;
    use HasStatusTransitions;

    protected $fillable = [
        'tenant_id', 'contact_id', 'warehouse_id', 'invoice_id', 'number',
        'order_date', 'expected_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
    ];

    protected $attributes = ['status' => 'draft'];

    protected function getTransitions(): array
    {
        return [
            'draft'     => ['confirmed', 'cancelled'],
            'confirmed' => ['fulfilled', 'cancelled'],
            'fulfilled' => [],
            'cancelled' => [],
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fulfill(): void
    {
        if (! $this->canTransitionTo('fulfilled')) {
            throw new \DomainException("Sales order cannot be fulfilled in status '{$this->status}'.");
        }

        if (! $this->warehouse_id) {
            throw new \DomainException('A warehouse is required to fulfill this order.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            foreach ($this->items as $item) {
                if (! $item->product_id) {
                    $item->update(['quantity_fulfilled' => $item->quantity]);
                    continue;
                }

                \App\Modules\Inventory\Models\StockMovement::record([
                    'product_id'   => $item->product_id,
                    'warehouse_id' => $this->warehouse_id,
                    'type'         => 'out',
                    'quantity'     => (float) $item->quantity,
                    'reference'    => $this->number,
                    'notes'        => "Fulfilled SO #{$this->id}",
                ]);

                $item->update(['quantity_fulfilled' => $item->quantity]);
            }

            $this->update(['status' => 'fulfilled']);
        });
    }
}
