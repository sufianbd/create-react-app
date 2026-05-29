<?php

namespace App\Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'sku'            => $this->sku,
            'name'           => $this->name,
            'description'    => $this->description,
            'category_id'    => $this->category_id,
            'category'       => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'uom_id'         => $this->uom_id,
            'uom'            => $this->whenLoaded('uom', fn () => [
                'id'           => $this->uom->id,
                'name'         => $this->uom->name,
                'abbreviation' => $this->uom->abbreviation,
            ]),
            'cost_price'     => $this->cost_price,
            'sale_price'     => $this->sale_price,
            'reorder_point'  => $this->reorder_point,
            'is_active'      => $this->is_active,
            'stock_levels'   => $this->whenLoaded('stockLevels', fn () =>
                $this->stockLevels->map(fn ($sl) => [
                    'warehouse_id'      => $sl->warehouse_id,
                    'warehouse_name'    => $sl->warehouse?->name,
                    'quantity'          => $sl->quantity,
                    'reserved_quantity' => $sl->reserved_quantity,
                    'available'         => $sl->available,
                ])
            ),
            'total_quantity' => $this->when(
                $this->relationLoaded('stockLevels'),
                fn () => $this->total_quantity
            ),
            'created_at'     => $this->created_at,
        ];
    }
}
