<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'status' => $this->status,

            'subtotal' => (float) $this->subtotal,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,

            'currency' => $this->currency,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'shipping_method' => $this->shipping_method,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'admin_notes' => $this->admin_notes,
            'coupon_code' => $this->coupon_code,
            'confirmed_at' => $this->confirmed_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'cancelled_at' => $this->cancelled_at,

            'total_items' => $this->whenLoaded('items', function () {
                return (int) $this->items->sum('quantity');
            }),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
