<?php

namespace App\Modules\HR\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'period_start' => $this->period_start?->toDateString(),
            'period_end'   => $this->period_end?->toDateString(),
            'status'       => $this->status,
            'notes'        => $this->notes,
            'total_gross'  => $this->whenLoaded('items', fn () => $this->total_gross),
            'total_net'    => $this->whenLoaded('items', fn () => $this->total_net),
            'items_count'  => $this->whenLoaded('items', fn () => $this->items->count()),
            'items'        => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id'           => $item->id,
                'employee'     => $item->relationLoaded('employee') ? [
                    'id'        => $item->employee->id,
                    'full_name' => $item->employee->full_name,
                    'position'  => $item->employee->position,
                ] : null,
                'gross_salary' => $item->gross_salary,
                'deductions'   => $item->deductions,
                'net_salary'   => $item->net_salary,
                'notes'        => $item->notes,
            ])),
            'creator'      => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'created_at'   => $this->created_at?->toDateString(),
        ];
    }
}
