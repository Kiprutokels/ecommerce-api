<?php
// database/migrations/2024_01_01_000007_create_shopping_carts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable(); // For guest users
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->json('product_options')->nullable(); // Size, color, etc.
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['session_id']);
            $table->index(['product_id']);
            $table->unique(['user_id', 'product_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_carts');
    }
};