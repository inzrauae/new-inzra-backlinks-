<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingOrder;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Support\SeoData;
use App\Support\WhatsAppMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('orders.index', [
            'seo' => SeoData::forNoIndex('My Orders | INZRA', route('orders.index')),
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load('items.product');

        return view('orders.show', [
            'seo' => SeoData::forNoIndex("Order {$order->order_number} | INZRA", route('orders.show', $order)),
            'order' => $order,
        ]);
    }

    public function store(StoreOrderRequest $request, Product $product, CreatePendingOrder $createPendingOrder): RedirectResponse
    {
        $order = $createPendingOrder->handle(
            user: Auth::user(),
            product: $product,
            paymentMethod: PaymentMethod::WhatsApp,
            targetUrl: $request->validated('target_url'),
            anchorText: $request->validated('anchor_text'),
        );

        $order->load('items');
        $message = WhatsAppMessage::forOrder($order);
        $order->update(['whatsapp_message' => $message]);

        return redirect()->away(WhatsAppMessage::url($message));
    }
}
