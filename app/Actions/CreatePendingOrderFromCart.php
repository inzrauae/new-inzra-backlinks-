<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreatePendingOrderFromCart
{
    /**
     * @param  Collection  $lines  Cart::lines() — each entry has 'product', 'quantity', 'subtotal'.
     *                             Target URL/anchor text/target country aren't collected at
     *                             checkout — the buyer fills them in per item from their order
     *                             page afterward (see OrderController::updateItemDetails).
     */
    public function handle(User $user, Collection $lines, PaymentMethod $paymentMethod): Order
    {
        return DB::transaction(function () use ($user, $lines, $paymentMethod) {
            $subtotal = (float) $lines->sum('subtotal');
            $firstProduct = $lines->first()['product'];

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'currency' => $firstProduct->currency,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'price' => $line['product']->price,
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            return $order;
        });
    }
}
