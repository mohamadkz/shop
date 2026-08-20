<?php

namespace App\Domain\Catalog\Resources;

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
            'id' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'price' => (float) $this->price,
            'formatted_price' => $this->formatted_price,
            'slug' => $this->slug,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'status' => $this->status->value,
            'image' => $this->image,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
