<?php
// database/migrations/2024_01_01_000003_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable(); // For profit calculations
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->json('images')->nullable(); // Array of image URLs
            $table->json('gallery')->nullable(); // Additional images
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height}
            $table->string('status')->default('active'); // active, inactive, draft
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->json('attributes')->nullable(); // Color, size, material, etc.
            $table->json('variations')->nullable(); // For product variants
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            
            // Indexes
            $table->index(['slug', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['price', 'is_active']);
            $table->index(['sku', 'is_active']);
            $table->index(['stock_quantity', 'manage_stock']);
            $table->index(['average_rating', 'is_active']);
            $table->index('created_at');
            $table->fullText(['name', 'description', 'short_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};