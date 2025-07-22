<?php
// database/migrations/2024_01_01_000006_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name'); // Store name at time of purchase
            $table->string('product_sku'); // Store SKU at time of purchase
            $table->json('product_details')->nullable(); // Store product details snapshot
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Price at time of purchase
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes
            $table->index(['order_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};