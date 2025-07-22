<?php
// database/migrations/2024_01_01_000002_create_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable(); // Icon class or path
            $table->unsignedBigInteger('parent_id')->nullable(); // For nested categories
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            
            // Indexes
            $table->index(['slug', 'is_active']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['is_active', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};