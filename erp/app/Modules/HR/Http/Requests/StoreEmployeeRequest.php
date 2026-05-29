<?php

namespace App\Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'employee_number' => ['nullable', 'string', 'max:30',
                Rule::unique('employees')->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->route('employee')),
            ],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'position'        => ['nullable', 'string', 'max:150'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract'])],
            'status'          => ['required', Rule::in(['active', 'on_leave', 'terminated'])],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['nullable', 'date', 'after:start_date'],
            'salary_type'     => ['required', Rule::in(['hourly', 'monthly'])],
            'salary_amount'   => ['required', 'numeric', 'min:0'],
        ];
    }
}
