<?php
// database/migrations/2024_01_01_000005_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped', 
                'delivered', 'cancelled', 'refunded', 'returned'
            ])->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 4)->default(0); // Tax percentage
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_status', [
                'pending', 'paid', 'failed', 'refunded', 'partially_refunded'
            ])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable(); // Stripe, PayPal, etc.
            $table->json('payment_details')->nullable(); // Additional payment info
            $table->json('billing_address');
            $table->json('shipping_address');
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('coupon_code')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['order_number']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};