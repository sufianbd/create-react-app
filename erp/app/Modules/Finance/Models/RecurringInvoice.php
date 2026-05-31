<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use App\Modules\Core\Traits\HasAuditLog;
use App\Modules\Finance\Traits\HasLineItemTotals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RecurringInvoice extends Model
{
    use BelongsToTenant;
    use HasAuditLog;
    use SoftDeletes;
    use HasLineItemTotals;

    protected $fillable = [
        'tenant_id', 'contact_id', 'frequency', 'start_date', 'next_run_date',
        'end_date', 'due_days', 'status', 'auto_send', 'notes',
        'last_generated_at', 'generated_count', 'created_by',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'next_run_date'     => 'date',
        'end_date'          => 'date',
        'auto_send'         => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    protected $attributes = [
        'status'          => 'active',
        'frequency'       => 'monthly',
        'due_days'        => 30,
        'generated_count' => 0,
        'auto_send'       => false,
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'active')
            ->whereDate('next_run_date', '<=', now()->toDateString());
    }

    protected function intervalAdvance(\Carbon\Carbon $date): \Carbon\Carbon
    {
        return match ($this->frequency) {
            'weekly'    => $date->copy()->addWeek(),
            'quarterly' => $date->copy()->addMonthsNoOverflow(3),
            'yearly'    => $date->copy()->addYear(),
            default     => $date->copy()->addMonthNoOverflow(),
        };
    }

    public function advanceSchedule(): void
    {
        $next = $this->intervalAdvance(\Carbon\Carbon::parse($this->next_run_date));

        if ($this->end_date && $next->gt(\Carbon\Carbon::parse($this->end_date))) {
            $this->status = 'ended';
        } else {
            $this->next_run_date = $next->toDateString();
        }

        $this->save();
    }

    public function generateInvoice(): Invoice
    {
        return DB::transaction(function () {
            if (! $this->relationLoaded('items')) {
                $this->load('items');
            }

            $invoice = Invoice::create([
                'tenant_id'  => $this->tenant_id,
                'contact_id' => $this->contact_id,
                'issue_date' => now()->toDateString(),
                'due_date'   => now()->addDays($this->due_days)->toDateString(),
                'status'     => $this->auto_send ? 'sent' : 'draft',
                'notes'      => $this->notes,
                'created_by' => $this->created_by,
            ]);

            $invoice->update([
                'number' => 'INV-' . now()->format('Y') . '-' . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($this->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'tax_rate'    => $item->tax_rate,
                ]);
            }

            $this->generated_count = $this->generated_count + 1;
            $this->last_generated_at = now();
            $this->save();

            $this->advanceSchedule();

            return $invoice;
        });
    }
}
