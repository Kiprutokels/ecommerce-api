<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,

            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'current_price' => (float) $this->getCurrentPrice(),

            'is_on_sale' => (bool) $this->isOnSale(),
            'sku' => $this->sku,

            'stock_quantity' => (int) $this->stock_quantity,
            'in_stock' => (bool) $this->in_stock,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,

            'average_rating' => (float) $this->average_rating,
            'review_count' => (int) $this->review_count,

            'images' => $this->images,
            'main_image' => $this->getMainImage(),

            'weight' => $this->weight !== null ? (float) $this->weight : null,
            'dimensions' => $this->dimensions,

            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
