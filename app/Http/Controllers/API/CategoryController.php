<?php
// app/Http/Controllers/API/CategoryController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\BrandResource;
use App\Models\Category;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);

        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function brands(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(BrandResource::collection($brands));
    }
}
