<?php

namespace App\Modules\Finance\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'bank_account_id', 'transaction_date', 'description',
        'amount', 'reference', 'reconciled', 'payment_id', 'journal_entry_id',
        'imported_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'reconciled'       => 'boolean',
        'amount'           => 'float',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
