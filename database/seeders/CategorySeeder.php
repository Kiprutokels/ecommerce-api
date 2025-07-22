<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing', 'slug' => 'clothing'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Books', 'slug' => 'books'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}