<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class Cart
{
    private const SESSION_KEY = 'cart';

    public static function add(Product $product, int $quantity, ?string $targetUrl, ?string $anchorText, ?string $targetCountry): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        $cart[(string) Str::uuid()] = [
            'product_id' => $product->id,
            'quantity' => max(1, $quantity),
            'target_url' => $targetUrl,
            'anchor_text' => $anchorText,
            'target_country' => $targetCountry,
        ];

        Session::put(self::SESSION_KEY, $cart);
    }

    public static function updateQuantity(string $lineId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (! isset($cart[$lineId])) {
            return;
        }

        $cart[$lineId]['quantity'] = max(1, $quantity);
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function remove(string $lineId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$lineId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Hydrated cart lines, each with its Product loaded. Lines whose
     * product no longer exists (e.g. deactivated/deleted) are dropped.
     */
    public static function lines(): Collection
    {
        $cart = Session::get(self::SESSION_KEY, []);

        $products = Product::whereIn('id', collect($cart)->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->filter(fn (array $line) => $products->has($line['product_id']))
            ->map(function (array $line, string $lineId) use ($products) {
                $product = $products[$line['product_id']];

                return [
                    'line_id' => $lineId,
                    'product' => $product,
                    'quantity' => $line['quantity'],
                    'target_url' => $line['target_url'],
                    'anchor_text' => $line['anchor_text'],
                    'target_country' => $line['target_country'],
                    'subtotal' => (float) $product->price * $line['quantity'],
                ];
            })
            ->values();
    }

    public static function count(): int
    {
        return (int) collect(Session::get(self::SESSION_KEY, []))->sum('quantity');
    }

    public static function total(): float
    {
        return (float) self::lines()->sum('subtotal');
    }

    public static function isEmpty(): bool
    {
        return self::lines()->isEmpty();
    }
}
