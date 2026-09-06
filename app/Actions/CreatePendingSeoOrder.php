<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Models\User;
use App\Support\UrlNormalizer;
use Illuminate\Support\Facades\DB;

class CreatePendingSeoOrder
{
    /**
     * @param  array<int, string>  $keywords  Up to 5 keywords, in order.
     */
    public function handle(
        User $user,
        SeoService $service,
        string $targetUrl,
        int $countryId,
        array $keywords,
        ?string $article,
        ?string $instructions,
        int $quantity,
    ): SeoOrder {
        return DB::transaction(function () use ($user, $service, $targetUrl, $countryId, $keywords, $article, $instructions, $quantity) {
            $unitPrice = (float) $service->unit_price;
            $subtotal = round($unitPrice * $quantity, 2);
            $taxRate = (float) config('seo_backlinks.tax_rate', 0);
            $tax = $taxRate > 0 ? round($subtotal * $taxRate / 100, 2) : 0;
            $total = round($subtotal + $tax, 2);

            $order = SeoOrder::create([
                'order_number' => SeoOrder::generateOrderNumber(),
                'user_id' => $user->id,
                'seo_service_id' => $service->id,
                'service_name' => $service->name,
                'target_url' => UrlNormalizer::normalize($targetUrl),
                'country_id' => $countryId,
                'article' => $article,
                'instructions' => $instructions,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'currency' => 'USD',
                'payment_method' => 'paypal',
                'payment_status' => PaymentStatus::Unpaid,
                'order_status' => SeoOrderStatus::PendingPayment,
                'terms_accepted_at' => now(),
                'terms_version' => config('seo_backlinks.terms_version'),
            ]);

            foreach (array_values($keywords) as $index => $keyword) {
                if ($keyword === '') {
                    continue;
                }

                $order->keywords()->create([
                    'position' => $index + 1,
                    'keyword' => $keyword,
                ]);
            }

            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => SeoOrderStatus::PendingPayment->value,
                'changed_by' => null,
                'created_at' => now(),
            ]);

            return $order;
        });
    }
}
