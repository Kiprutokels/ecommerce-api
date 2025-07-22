<?php
// app/Http/Requests/Admin/UpdateProductRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;
        
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($productId),
            ],
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($productId),
            ],
            'barcode' => 'nullable|string|max:255',
            'stock_quantity' => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'attributes' => 'nullable|array',
            'variations' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'seo_keywords' => 'nullable|array',
        ];
    }
}
