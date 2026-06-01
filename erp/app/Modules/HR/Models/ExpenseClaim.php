<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\Core\Traits\BelongsToTenant;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ExpenseClaim extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'employee_id', 'submitted_by', 'title', 'description',
        'expense_date', 'amount', 'currency_code', 'category', 'receipt_path',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'reviewed_at'  => 'datetime',
        'amount'       => 'float',
    ];

    protected $attributes = ['status' => 'draft'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function submit(): void
    {
        if ($this->status !== 'draft') {
            throw new DomainException("Only draft claims can be submitted. Current status: {$this->status}.");
        }

        $this->update([
            'status'       => 'submitted',
            'submitted_by' => Auth::id(),
        ]);
    }

    public function approve(string $notes = ''): void
    {
        if ($this->status !== 'submitted') {
            throw new DomainException("Only submitted claims can be approved. Current status: {$this->status}.");
        }

        $this->update([
            'status'       => 'approved',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reject(string $notes = ''): void
    {
        if ($this->status !== 'submitted') {
            throw new DomainException("Only submitted claims can be rejected. Current status: {$this->status}.");
        }

        $this->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reimburse(): void
    {
        if ($this->status !== 'approved') {
            throw new DomainException("Only approved claims can be reimbursed. Current status: {$this->status}.");
        }

        $this->update([
            'status' => 'reimbursed',
        ]);
    }
}
