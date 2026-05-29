<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $tenantId  = auth()->user()?->tenant_id;
        $productId = $this->route('product');

        return [
            'sku'           => ['required', 'string', 'max:100',
                Rule::unique('products')->where('tenant_id', $tenantId)->ignore($productId),
            ],
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'category_id'   => ['nullable', 'integer', 'exists:categories,id'],
            'uom_id'        => ['nullable', 'integer', 'exists:units_of_measure,id'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'sale_price'    => ['required', 'numeric', 'min:0'],
            'reorder_point' => ['integer', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }
}
