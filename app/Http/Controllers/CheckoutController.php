<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingOrderFromCart;
use App\Enums\PaymentMethod;
use App\Models\PaymentSetting;
use App\Support\Cart;
use App\Support\SeoData;
use App\Support\WhatsAppMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Cart::isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        return view('checkout.index', [
            'seo' => SeoData::forNoIndex('Checkout | INZRA', route('checkout.index')),
            'lines' => Cart::lines(),
            'total' => Cart::total(),
            'paypal' => PaymentSetting::paypal(),
        ]);
    }

    public function whatsapp(CreatePendingOrderFromCart $createPendingOrderFromCart): RedirectResponse
    {
        if (Cart::isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $order = $createPendingOrderFromCart->handle(
            user: Auth::user(),
            lines: Cart::lines(),
            paymentMethod: PaymentMethod::WhatsApp,
        );

        $order->load('items');
        $message = WhatsAppMessage::forOrder($order);
        $order->update(['whatsapp_message' => $message]);

        Cart::clear();

        return redirect()->away(WhatsAppMessage::url($message));
    }
}
