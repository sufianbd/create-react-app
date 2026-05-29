<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use BelongsToTenant;
    use HasAuditLog;

    protected $table = 'units_of_measure';

    protected $fillable = ['tenant_id', 'name', 'abbreviation'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'uom_id');
    }
}
