<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingOrder;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderItemDetailsRequest;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\SeoData;
use App\Support\WhatsAppMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'countries' => Country::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * The buyer fills in each item's target URL/anchor text/target country
     * from their order page after checkout — it isn't collected at
     * add-to-cart time. Only the order's own owner may do this (not admin,
     * who manages orders from a separate panel).
     */
    public function updateItemDetails(UpdateOrderItemDetailsRequest $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless($item->order_id === $order->id, 404);

        $item->update($request->validated());

        return redirect()->route('orders.show', $order)->with('status', 'Order details updated.');
    }

    public function downloadDelivery(Order $order): StreamedResponse
    {
        Gate::authorize('view', $order);

        abort_unless($order->delivery_file_path && Storage::disk('local')->exists($order->delivery_file_path), 404);

        return Storage::disk('local')->download($order->delivery_file_path, $order->delivery_file_name ?: 'delivery');
    }

    public function store(StoreOrderRequest $request, Product $product, CreatePendingOrder $createPendingOrder): RedirectResponse
    {
        $order = $createPendingOrder->handle(
            user: Auth::user(),
            product: $product,
            paymentMethod: PaymentMethod::WhatsApp,
            targetUrl: $request->validated('target_url'),
            anchorText: $request->validated('anchor_text'),
            targetCountry: $request->validated('target_country'),
        );

        $order->load('items');
        $message = WhatsAppMessage::forOrder($order);
        $order->update(['whatsapp_message' => $message]);

        return redirect()->away(WhatsAppMessage::url($message));
    }
}
