<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'supplier_id', 'warehouse_id',
        'status', 'expected_date', 'notes', 'created_by',
    ];

    protected $attributes = ['status' => 'draft'];

    protected $casts = ['expected_date' => 'date'];

    /** Allowed status transitions */
    private const TRANSITIONS = [
        'draft'     => ['submitted', 'cancelled'],
        'submitted' => ['approved', 'cancelled'],
        'approved'  => ['received', 'cancelled'],
        'received'  => [],
        'cancelled' => [],
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_cost);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** @return string[] */
    public function availableTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function transitionTo(string $status): void
    {
        if (! $this->canTransitionTo($status)) {
            throw new \DomainException(
                "Cannot transition PO from '{$this->status}' to '{$status}'."
            );
        }

        $this->update(['status' => $status]);
    }

    /**
     * Receive items: create StockMovements and mark PO as received.
     *
     * @param array<int, array{id:int, received_quantity:float}> $lines
     */
    public function receive(array $lines): void
    {
        if (! $this->canTransitionTo('received')) {
            throw new \DomainException("PO cannot be received in status '{$this->status}'.");
        }

        DB::transaction(function () use ($lines) {
            foreach ($lines as $line) {
                /** @var PurchaseOrderItem $item */
                $item = $this->items()->findOrFail($line['id']);
                $qty  = (float) $line['received_quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $item->update(['received_quantity' => $item->received_quantity + $qty]);

                StockMovement::record([
                    'product_id'   => $item->product_id,
                    'warehouse_id' => $this->warehouse_id,
                    'type'         => 'in',
                    'quantity'     => $qty,
                    'reference'    => "PO-{$this->id}",
                    'notes'        => "Received from PO #{$this->id}",
                ]);
            }

            $this->update(['status' => 'received']);
        });
    }
}
