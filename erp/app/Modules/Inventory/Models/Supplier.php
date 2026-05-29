<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'contact_person',
        'email', 'phone', 'address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
