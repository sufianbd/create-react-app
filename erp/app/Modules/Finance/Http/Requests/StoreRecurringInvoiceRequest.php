<?php

namespace App\Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_id'          => ['nullable', Rule::exists('contacts', 'id')],
            'frequency'           => ['required', Rule::in(['weekly', 'monthly', 'quarterly', 'yearly'])],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'due_days'            => ['required', 'integer', 'min:0', 'max:365'],
            'auto_send'           => ['boolean'],
            'notes'               => ['nullable', 'string'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
