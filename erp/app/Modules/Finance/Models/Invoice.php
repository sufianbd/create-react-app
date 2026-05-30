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

class Invoice extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;
    use HasLineItemTotals;
    use HasStatusTransitions;

    protected $fillable = [
        'tenant_id', 'contact_id', 'number',
        'issue_date', 'due_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
    ];

    protected $attributes = ['status' => 'draft'];

    protected function getTransitions(): array
    {
        return [
            'draft'     => ['sent', 'cancelled'],
            'sent'      => ['paid', 'cancelled'],
            'paid'      => [],
            'cancelled' => [],
        ];
    }

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
}
