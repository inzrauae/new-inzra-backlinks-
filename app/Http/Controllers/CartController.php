<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Product;
use App\Support\Cart;
use App\Support\SeoData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view('cart.index', [
            'seo' => SeoData::forNoIndex('Your Cart | INZRA', route('cart.index')),
            'lines' => Cart::lines(),
            'total' => Cart::total(),
        ]);
    }

    public function add(StoreCartItemRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        Cart::add(
            product: $product,
            quantity: (int) $request->validated('quantity', 1),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "{$product->name} added to your cart.",
                'count' => Cart::count(),
            ]);
        }

        return redirect()->route('cart.index')->with('status', "{$product->name} added to your cart.");
    }

    public function update(Request $request, string $line): RedirectResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:100']]);

        Cart::updateQuantity($line, (int) $request->input('quantity'));

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function remove(string $line): RedirectResponse
    {
        Cart::remove($line);

        return redirect()->route('cart.index')->with('status', 'Item removed from your cart.');
    }
}
