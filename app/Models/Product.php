<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'cost_price',
        'sku',
        'barcode',
        'stock_quantity',
        'low_stock_threshold',
        'manage_stock',
        'in_stock',
        'is_active',
        'is_featured',
        'is_digital',
        'images',
        'gallery',
        'weight',
        'dimensions',
        'status',
        'category_id',
        'brand_id',
        'attributes',
        'variations',
        'average_rating',
        'review_count',
        'view_count',
        'sales_count',
        'meta_title',
        'meta_description',
        'seo_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'images' => 'array',
        'gallery' => 'array',
        'dimensions' => 'array',
        'attributes' => 'array',
        'variations' => 'array',
        'seo_keywords' => 'array',
        'manage_stock' => 'boolean',
        'in_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'average_rating' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'review_count' => 'integer',
        'view_count' => 'integer',
        'sales_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getCurrentPrice(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getMainImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }
}