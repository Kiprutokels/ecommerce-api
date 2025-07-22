<?php
// database/migrations/2024_01_01_000009_create_product_reviews_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable(); // Optional: link to purchase
            $table->integer('rating'); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('review');
            $table->json('images')->nullable(); // Review images
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            
            // Indexes
            $table->index(['product_id', 'is_approved']);
            $table->index(['user_id']);
            $table->index(['rating', 'is_approved']);
            $table->unique(['product_id', 'user_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};