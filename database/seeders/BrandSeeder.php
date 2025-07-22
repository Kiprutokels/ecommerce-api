<?php
// database/seeders/BrandSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple', 'description' => 'Technology company', 'is_active' => true],
            ['name' => 'Samsung', 'slug' => 'samsung', 'description' => 'Electronics manufacturer', 'is_active' => true],
            ['name' => 'Nike', 'slug' => 'nike', 'description' => 'Sports apparel', 'is_active' => true],
            ['name' => 'Adidas', 'slug' => 'adidas', 'description' => 'Sports brand', 'is_active' => true],
            ['name' => 'Sony', 'slug' => 'sony', 'description' => 'Electronics company', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
