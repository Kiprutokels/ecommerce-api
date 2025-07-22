<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public product/category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/brands', [CategoryController::class, 'brands']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Order routes for all authenticated users
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Admin-only routes
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Categories
        Route::get('/categories', [AdminController::class, 'getCategories']);
        Route::post('/categories', [AdminController::class, 'storeCategory']);
        Route::get('/categories/{category}', [AdminController::class, 'showCategory']);
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory']);

        // Brands
        Route::get('/brands', [AdminController::class, 'getBrands']);
        Route::post('/brands', [AdminController::class, 'storeBrand']);

        // Products
        Route::get('/products', [AdminController::class, 'getProducts']);
        Route::post('/products', [AdminController::class, 'storeProduct']);
        Route::get('/products/{product}', [AdminController::class, 'showProduct']);
        Route::put('/products/{product}', [AdminController::class, 'updateProduct']);
        Route::delete('/products/{product}', [AdminController::class, 'deleteProduct']);

        // Order management (admin)
        Route::get('/orders', [AdminController::class, 'getOrders']);
        Route::get('/orders/{order}', [AdminController::class, 'showOrder']);
        Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus']);

        // Users
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
    });
});
