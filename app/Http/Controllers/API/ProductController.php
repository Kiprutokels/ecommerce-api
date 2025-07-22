<?php
// app/Http/Controllers/API/ProductController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand'])->where('is_active', true);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Search by name or description
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort products
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSorts = ['created_at', 'price', 'name', 'average_rating', 'sales_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate($request->get('per_page', 12));

        return $this->success(ProductResource::collection($products)->response()->getData(true));
    }

    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return $this->error('Product not found', 404);
        }

        $product->load(['category', 'brand']);
        
        // Increment view count
        $product->increment('view_count');

        return $this->success(new ProductResource($product));
    }

    public function featured(): JsonResponse
    {
        $products = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return $this->success(ProductResource::collection($products));
    }
}
