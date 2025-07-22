<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronicsCategory = Category::where('slug', 'electronics')->first();
        $clothingCategory = Category::where('slug', 'clothing')->first();
        
        $appleBrand = Brand::where('slug', 'apple')->first();
        $nikeBrand = Brand::where('slug', 'nike')->first();

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'Latest iPhone with advanced features and powerful performance.',
                'short_description' => 'Premium smartphone with Pro camera system',
                'price' => 999.00,
                'sale_price' => 899.00,
                'cost_price' => 600.00,
                'sku' => 'IP15-PRO-001',
                'stock_quantity' => 50,
                'low_stock_threshold' => 5,
                'manage_stock' => true,
                'in_stock' => true,
                'is_active' => true,
                'is_featured' => true,
                'images' => ['https://dummyimage.com/400x400/007bff/ffffff&text=iPhone+15+Pro'],
                'weight' => 0.206,
                'dimensions' => ['length' => 15.95, 'width' => 7.65, 'height' => 0.83],
                'category_id' => $electronicsCategory?->id ?? 1,
                'brand_id' => $appleBrand?->id ?? 1,
                'attributes' => ['color' => 'Space Black', 'storage' => '128GB'],
                'meta_title' => 'iPhone 15 Pro - Premium Smartphone',
                'meta_description' => 'Buy the latest iPhone 15 Pro with advanced camera and performance features.',
            ],
            [
                'name' => 'MacBook Pro 14"',
                'slug' => 'macbook-pro-14',
                'description' => 'Powerful laptop for professionals with M3 chip and Liquid Retina XDR display.',
                'short_description' => 'Professional laptop with M3 chip',
                'price' => 1999.00,
                'sku' => 'MBP-14-001',
                'stock_quantity' => 25,
                'category_id' => $electronicsCategory?->id ?? 1,
                'brand_id' => $appleBrand?->id ?? 1,
                'is_active' => true,
                'is_featured' => true,
                'images' => ['https://picsum.photos/400/400?random=1'],
            ],
            [
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'description' => 'Comfortable running shoes with Air Max technology for all-day comfort.',
                'short_description' => 'Running shoes with Air Max cushioning',
                'price' => 150.00,
                'sale_price' => 120.00,
                'sku' => 'NAM-270-001',
                'stock_quantity' => 100,
                'category_id' => $clothingCategory?->id ?? 2,
                'brand_id' => $nikeBrand?->id ?? 3,
                'is_active' => true,
                'images' => ['https://picsum.photos/400/400?random=3'],
                'attributes' => ['size' => 'Various', 'color' => 'White/Black'],
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
