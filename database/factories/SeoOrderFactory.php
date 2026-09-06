<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoOrderFactory extends Factory
{
    protected $model = SeoOrder::class;

    public function definition(): array
    {
        $quantity = 100;
        $unitPrice = 0.10;

        return [
            'order_number' => SeoOrder::generateOrderNumber(),
            'user_id' => User::factory(),
            'seo_service_id' => SeoService::factory(),
            'service_name' => 'DA 70+ Publication',
            'target_url' => 'https://example.com',
            'country_id' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'tax' => 0,
            'total' => $quantity * $unitPrice,
            'currency' => 'USD',
            'payment_method' => 'paypal',
            'payment_status' => PaymentStatus::Unpaid,
            'order_status' => SeoOrderStatus::PendingPayment,
            'terms_accepted_at' => now(),
            'terms_version' => '2026-09-06',
        ];
    }
}
