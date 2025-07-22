<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\BrandResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class AdminController extends Controller
{
    use ApiResponse;

    // ====== DASHBOARD ======
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 5)->count(),
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'recent_orders' => Order::with('user')->latest()->take(5)->get(),
            'recent_products' => Product::with(['category', 'brand'])->latest()->take(5)->get(),
        ];

        return $this->success($stats, 'Dashboard data retrieved successfully');
    }

    // ====== CATEGORIES ======
    public function getCategories(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate($request->get('per_page', 15));

        return $this->success(CategoryResource::collection($categories)->response()->getData(true));
    }

    public function storeCategory(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = Category::create($data);

        return $this->success(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function showCategory(Category $category): JsonResponse
    {
        $category->load(['parent', 'children']);
        return $this->success(new CategoryResource($category));
    }

    public function updateCategory(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    public function deleteCategory(Category $category): JsonResponse
    {
        if ($category->products()->count() > 0) {
            return $this->error('Cannot delete category with products', 422);
        }

        $category->delete();

        return $this->success([], 'Category deleted successfully');
    }

    // ====== BRANDS ======
    public function getBrands(Request $request): JsonResponse
    {
        $query = Brand::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->orderBy('sort_order')->orderBy('name')->paginate($request->get('per_page', 15));

        return $this->success(BrandResource::collection($brands)->response()->getData(true));
    }

    public function storeBrand(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $brand = Brand::create($data);

        return $this->success(new BrandResource($brand), 'Brand created successfully', 201);
    }

    // ====== PRODUCTS ======
    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('in_stock', true)->where('stock_quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '<=', 5);
                    break;
                case 'out_of_stock':
                    $query->where('in_stock', false)->orWhere('stock_quantity', 0);
                    break;
            }
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate($request->get('per_page', 15));

        return $this->success(ProductResource::collection($products)->response()->getData(true));
    }

    public function storeProduct(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = 'PRD-' . strtoupper(Str::random(8));
        }

        // Handle stock status
        if ($data['manage_stock'] && $data['stock_quantity'] <= 0) {
            $data['in_stock'] = false;
        }

        $product = Product::create($data);
        $product->load(['category', 'brand']);

        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    public function showProduct(Product $product): JsonResponse
    {
        $product->load(['category', 'brand']);
        return $this->success(new ProductResource($product));
    }

    public function updateProduct(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle stock status
        if ($data['manage_stock'] && $data['stock_quantity'] <= 0) {
            $data['in_stock'] = false;
        }

        $product->update($data);
        $product->load(['category', 'brand']);

        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    public function deleteProduct(Product $product): JsonResponse
    {
        // Check if product has orders
        if ($product->orderItems()->count() > 0) {
            return $this->error('Cannot delete product with existing orders', 422);
        }

        $product->delete();

        return $this->success([], 'Product deleted successfully');
    }

    //prod

    public function bulkUpdateProducts(Request $request): JsonResponse
{
    $request->validate([
        'product_ids' => 'required|array',
        'product_ids.*' => 'exists:products,id',
        'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
        'category_id' => 'nullable|exists:categories,id',
        'brand_id' => 'nullable|exists:brands,id',
    ]);

    $productIds = $request->product_ids;
    $action = $request->action;

    try {
        switch ($action) {
            case 'activate':
                Product::whereIn('id', $productIds)->update(['is_active' => true]);
                break;
            case 'deactivate':
                Product::whereIn('id', $productIds)->update(['is_active' => false]);
                break;
            case 'feature':
                Product::whereIn('id', $productIds)->update(['is_featured' => true]);
                break;
            case 'unfeature':
                Product::whereIn('id', $productIds)->update(['is_featured' => false]);
                break;
            case 'delete':
                // Check if any product has orders
                $hasOrders = Product::whereIn('id', $productIds)
                    ->whereHas('orderItems')
                    ->exists();
                    
                if ($hasOrders) {
                    return $this->error('Some products have existing orders and cannot be deleted', 422);
                }
                
                Product::whereIn('id', $productIds)->delete();
                break;
        }

        return $this->success([], "Products {$action}d successfully");
    } catch (\Exception $e) {
        return $this->error("Failed to {$action} products: " . $e->getMessage(), 500);
    }
}

// ====== PRODUCT STATS ======
public function getProductStats(): JsonResponse
{
    $stats = [
        'total_products' => Product::count(),
        'active_products' => Product::where('is_active', true)->count(),
        'inactive_products' => Product::where('is_active', false)->count(),
        'featured_products' => Product::where('is_featured', true)->count(),
        'low_stock_products' => Product::where('stock_quantity', '<=', 5)->count(),
        'out_of_stock_products' => Product::where('in_stock', false)->count(),
        'total_value' => Product::where('is_active', true)->sum('price'),
        'average_price' => Product::where('is_active', true)->avg('price'),
        'top_categories' => Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get(),
        'recent_products' => Product::with(['category', 'brand'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category->name ?? 'N/A',
                'created_at' => $product->created_at,
            ]),
    ];

    return $this->success($stats);
}

// ====== DUPLICATE PRODUCT ======
public function duplicateProduct(Product $product): JsonResponse
{
    $newProduct = $product->replicate();
    $newProduct->name = $product->name . ' (Copy)';
    $newProduct->slug = $product->slug . '-copy-' . time();
    $newProduct->sku = 'COPY-' . $product->sku . '-' . time();
    $newProduct->is_active = false;
    $newProduct->save();

    $newProduct->load(['category', 'brand']);

    return $this->success(new ProductResource($newProduct), 'Product duplicated successfully', 201);
}

// ====== IMPORT/EXPORT HELPERS ======
public function exportProducts(Request $request): JsonResponse
{
    $query = Product::with(['category', 'brand']);

    // Apply filters same as getProducts method
    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->has('brand_id')) {
        $query->where('brand_id', $request->brand_id);
    }

    $products = $query->get()->map(function ($product) {
        return [
            'ID' => $product->id,
            'Name' => $product->name,
            'SKU' => $product->sku,
            'Price' => $product->price,
            'Sale Price' => $product->sale_price,
            'Stock' => $product->stock_quantity,
            'Category' => $product->category->name ?? '',
            'Brand' => $product->brand->name ?? '',
            'Status' => $product->is_active ? 'Active' : 'Inactive',
            'Created' => $product->created_at->format('Y-m-d H:i:s'),
        ];
    });

    return $this->success([
        'filename' => 'products-export-' . date('Y-m-d-H-i-s') . '.csv',
        'data' => $products,
        'count' => $products->count(),
    ], 'Products exported successfully');
}

//orders
public function getOrders(Request $request): JsonResponse
{
    $query = Order::with(['user', 'items.product']);

    if ($request->has('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    if ($request->has('payment_status') && $request->payment_status !== 'all') {
        $query->where('payment_status', $request->payment_status);
    }

    if ($request->has('search')) {
        $query->where(function($q) use ($request) {
            $q->where('order_number', 'like', '%' . $request->search . '%')
              ->orWhereHas('user', function($subQ) use ($request) {
                  $subQ->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
              });
        });
    }

    $orders = $query->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 15));

    return $this->success(OrderResource::collection($orders)->response()->getData(true));
    
}

public function showOrder(Order $order): JsonResponse
{
    $order->load(['user', 'items.product.category', 'items.product.brand']);
    return $this->success(new OrderResource($order));
}

public function updateOrderStatus(Request $request, Order $order): JsonResponse
{
    $request->validate([
        'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        'tracking_number' => 'nullable|string|max:255',
        'admin_notes' => 'nullable|string|max:1000',
    ]);

    $updateData = [
        'status' => $request->status,
        'admin_notes' => $request->admin_notes,
    ];

    // Set timestamps based on status
    if ($request->status === 'confirmed' && $order->status === 'pending') {
        $updateData['confirmed_at'] = now();
    } elseif ($request->status === 'shipped' && $order->status !== 'shipped') {
        $updateData['shipped_at'] = now();
        if ($request->tracking_number) {
            $updateData['tracking_number'] = $request->tracking_number;
        }
    } elseif ($request->status === 'delivered' && $order->status !== 'delivered') {
        $updateData['delivered_at'] = now();
    } elseif ($request->status === 'cancelled') {
        $updateData['cancelled_at'] = now();
    }

    $order->update($updateData);

    return $this->success(new OrderResource($order), 'Order status updated successfully');
}

// aadding tus 

public function getUsers(Request $request): JsonResponse
{
    $query = User::query();

    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('phone', 'like', '%' . $search . '%');
        });
    }

    if ($request->has('role') && $request->role !== 'all') {
        $isAdmin = $request->role === 'admin';
        $query->where('is_admin', $isAdmin);
    }

    if ($request->has('status') && $request->status !== 'all') {
        $isActive = $request->status === 'active';
        $query->where('is_active', $isActive);
    }

    $users = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

    return $this->success(UserResource::collection($users)->response()->getData(true));
}

public function storeUser(Request $request): JsonResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:8',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'is_admin' => $request->get('is_admin', false),
        'is_active' => $request->get('is_active', true),
    ]);

    return $this->success(new UserResource($user), 'User created successfully', 201);
}

public function updateUser(Request $request, User $user): JsonResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('users')->ignore($user->id),
        ],
        'phone' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:8',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ]);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'is_admin' => $request->get('is_admin', false),
        'is_active' => $request->get('is_active', true),
    ];

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return $this->success(new UserResource($user), 'User updated successfully');
}

public function deleteUser(User $user): JsonResponse
{
    if ($user->orders()->count() > 0) {
        return $this->error('Cannot delete user with existing orders', 422);
    }

    $user->delete();

    return $this->success([], 'User deleted successfully');
}

}