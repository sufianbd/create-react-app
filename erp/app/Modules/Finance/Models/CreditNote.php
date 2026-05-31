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

class CreditNote extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;
    use HasLineItemTotals;
    use HasStatusTransitions;

    protected $fillable = [
        'tenant_id', 'contact_id', 'invoice_id', 'number',
        'issue_date', 'status', 'reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    protected $attributes = ['status' => 'draft'];

    protected function getTransitions(): array
    {
        return [
            'draft'     => ['issued', 'cancelled'],
            'issued'    => ['applied', 'cancelled'],
            'applied'   => [],
            'cancelled' => [],
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
