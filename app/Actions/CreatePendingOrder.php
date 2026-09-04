<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePendingOrder
{
    public function handle(
        User $user,
        Product $product,
        PaymentMethod $paymentMethod,
        ?string $targetUrl = null,
        ?string $anchorText = null,
    ): Order {
        return DB::transaction(function () use ($user, $product, $paymentMethod, $targetUrl, $anchorText) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $paymentMethod,
                'subtotal' => $product->price,
                'total' => $product->price,
                'currency' => $product->currency,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
                'target_url' => $targetUrl,
                'anchor_text' => $anchorText,
            ]);

            return $order;
        });
    }
}
