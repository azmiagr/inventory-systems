<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransactionResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'stock_before' => $this->stock_before,
            'stock_after' => $this->stock_after,
            'notes' => $this->notes,
            'transaction_date' => $this->transaction_date,
            'item' => $this->whenLoaded('item', fn() => [
                'id' => $this->item?->id,
                'name' => $this->item?->name,
                'sku' => $this->item?->sku,
                'unit' => $this->item?->unit,
            ]),
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'approved_by' => $this->whenLoaded('approver', fn() => [
                'id' => $this->approver?->id,
                'name' => $this->approver?->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
