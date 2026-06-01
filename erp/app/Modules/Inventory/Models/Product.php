<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'sku', 'name', 'description',
        'category_id', 'uom_id', 'cost_price',
        'sale_price', 'reorder_point', 'reorder_quantity',
        'preferred_supplier_id', 'is_active',
    ];

    protected $casts = [
        'cost_price'           => 'decimal:2',
        'sale_price'           => 'decimal:2',
        'reorder_point'        => 'float',
        'reorder_quantity'     => 'float',
        'is_active'            => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->stockLevels()->sum('quantity');
    }

    public function getTotalAvailableAttribute(): float
    {
        return (float) $this->stockLevels()
            ->selectRaw('SUM(quantity - reserved_quantity) as available')
            ->value('available') ?? 0.0;
    }

    public function getTotalStockAttribute(): float
    {
        return (float) $this->stockLevels->sum('quantity');
    }

    public function needsReorder(): bool
    {
        return $this->reorder_point > 0 && $this->total_stock <= $this->reorder_point;
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
