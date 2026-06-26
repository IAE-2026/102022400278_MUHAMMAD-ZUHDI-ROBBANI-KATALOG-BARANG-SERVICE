<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => (float) $this->base_price,
            'current_price' => (float) $this->current_price,
            'auction_start_at' => $this->auction_start_at?->toISOString(),
            'auction_end_at' => $this->auction_end_at?->toISOString(),
            'status' => $this->status->value,
            'receipt_number' => $this->receipt_number,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'meta' => [
                'service_name' => 'Katalog-Service',
                'api_version' => 'v1',
            ]
        ];
    }
}
