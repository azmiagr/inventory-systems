<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'stock_current' => $this->stock_current,
            'stock_minimum' => $this->stock_minimum,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'stock_status' => [
                'key' => $this->getStockStatusKey(),
                'label' => $this->getStockStatusLabel(),
                'color' => $this->getStockStatusColor(),
            ],
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'slug' => $this->category?->slug,
                ];
            }),
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier?->id,
                    'name' => $this->supplier?->name,
                    'slug' => $this->supplier?->slug,
                ];
            }),
            'recent_transactions' => StockTransactionResource::collection(
                $this->whenLoaded('stockTransactions')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
