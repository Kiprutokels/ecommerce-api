<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.product', 'user'])
            ->where('user_id', $request->user()->id);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->success(OrderResource::collection($orders)->response()->getData(true));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock availability
                if ($product->manage_stock && $product->stock_quantity < $item['quantity']) {
                    return $this->error("Product {$product->name} is out of stock", 422);
                }

                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_details' => [
                        'image' => $product->getMainImage(),
                        'category' => $product->category->name,
                        'brand' => $product->brand ? $product->brand->name : null,
                    ],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                ];

                // Update stock if managed
                if ($product->manage_stock) {
                    $product->decrement('stock_quantity', $item['quantity']);
                    $product->increment('sales_count', $item['quantity']);

                    // Update stock status
                    if ($product->stock_quantity <= 0) {
                        $product->update(['in_stock' => false]);
                    }
                }
            }

            // Calculate tax and shipping
            $taxRate = 0.08; // 8% tax
            $taxAmount = $subtotal * $taxRate;
            $shippingAmount = $subtotal >= 50 ? 0 : 9.99;
            $discountAmount = 0; // TODO: Implement coupon logic
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => 'KSH',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'billing_address' => $request->billing_address,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
            ]);

            // Create order items
            // foreach ($orderItems as &$orderItem) {
            //     $orderItem['order_id'] = $order->id;
            //     $orderItem['created_at'] = now();
            //     $orderItem['updated_at'] = now();
            // }

              // OrderItem::insert($orderItems);

            foreach ($orderItems as &$orderItem) {
                $orderItem['order_id'] = $order->id;
                $orderItem['product_details'] = json_encode($orderItem['product_details']); // Manually convert to JSON
                $orderItem['created_at'] = now();
                $orderItem['updated_at'] = now();
            }
          

            OrderItem::insert($orderItems);

            DB::commit();

            $order->load(['items.product', 'user']);

            return $this->success(new OrderResource($order), 'Order created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    public function show(Order $order, Request $request): JsonResponse
    {
        // Check if user owns this order or is admin
        if ($order->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return $this->error('Unauthorized', 403);
        }

        $order->load(['items.product.category', 'items.product.brand', 'user']);

        return $this->success(new OrderResource($order));
    }

    public function cancel(Order $order, Request $request): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return $this->error('Order cannot be cancelled', 422);
        }

        try {
            DB::beginTransaction();

            // Restore stock quantities
            foreach ($order->items as $item) {
                if ($item->product->manage_stock) {
                    $item->product->increment('stock_quantity', $item->quantity);
                    $item->product->decrement('sales_count', $item->quantity);

                    // Update stock status
                    if ($item->product->stock_quantity > 0) {
                        $item->product->update(['in_stock' => true]);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            DB::commit();

            return $this->success(new OrderResource($order), 'Order cancelled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to cancel order', 500);
        }
    }
}
