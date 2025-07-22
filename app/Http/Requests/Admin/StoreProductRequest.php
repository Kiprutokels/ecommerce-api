<?php
// app/Http/Requests/Admin/StoreProductRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products,sku',
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
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'attributes' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ];
    }
}