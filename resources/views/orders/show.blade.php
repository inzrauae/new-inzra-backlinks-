<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('orders.index') }}">Your orders</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $order->order_number }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Order {{ $order->order_number }}</h2>
      <p class="section__sub">Placed {{ $order->created_at->format('j F Y, g:ia') }}</p>
    </header>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <div class="pdp__specs-table">
        <div class="pdp__spec"><span>Order status</span><b>{{ $order->status->label() }}</b></div>
        <div class="pdp__spec"><span>Payment method</span><b>{{ $order->payment_method->label() }}</b></div>
        <div class="pdp__spec"><span>Payment status</span><b>{{ $order->payment_status->label() }}</b></div>
        <div class="pdp__spec"><span>Total</span><b>${{ number_format((float) $order->total, 2) }}</b></div>
        <div class="pdp__spec"><span>Customer</span><b>{{ $order->customer_name }}</b></div>
        <div class="pdp__spec"><span>Email</span><b>{{ $order->customer_email }}</b></div>
        <div class="pdp__spec"><span>Phone</span><b>{{ $order->customer_phone ?: '—' }}</b></div>
      </div>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Items</h3>
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Product</th>
            <th style="padding:10px 12px;">SKU</th>
            <th style="padding:10px 12px;">Qty</th>
            <th style="padding:10px 12px;">Price</th>
            <th style="padding:10px 12px;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->items as $item)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">
                @if ($item->product)
                  <a href="{{ route('products.show', $item->product) }}" class="auth-link">{{ $item->product_name }}</a>
                @else
                  {{ $item->product_name }}
                @endif
                @if ($item->target_url)
                  <div style="font-size:.82rem; color:var(--text-2);">Target: {{ $item->target_url }}</div>
                @endif
                @if ($item->anchor_text)
                  <div style="font-size:.82rem; color:var(--text-2);">Anchor: {{ $item->anchor_text }}</div>
                @endif
              </td>
              <td style="padding:10px 12px;">{{ $item->sku }}</td>
              <td style="padding:10px 12px;">{{ $item->quantity }}</td>
              <td style="padding:10px 12px;">${{ number_format((float) $item->price, 2) }}</td>
              <td style="padding:10px 12px;">${{ number_format((float) $item->subtotal, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if ($order->delivery_url || $order->delivery_file_path)
      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Delivery</h3>
        <div style="display:flex; flex-wrap:wrap; gap:12px;">
          @if ($order->delivery_url)
            <a href="{{ $order->delivery_url }}" target="_blank" rel="noopener" class="btn btn--primary ripple">
              <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View delivery
            </a>
          @endif
          @if ($order->delivery_file_path)
            <a href="{{ route('orders.delivery', $order) }}" class="btn btn--glass ripple">
              <i class="fa-solid fa-download" aria-hidden="true"></i> Download {{ $order->delivery_file_name }}
            </a>
          @endif
        </div>
      </div>
    @endif

    @if ($order->whatsapp_message)
      <div class="auth-card glass reveal">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:12px;">WhatsApp message sent</h3>
        <p style="white-space:pre-line; color:var(--text-2); font-size:.9rem;">{{ $order->whatsapp_message }}</p>
      </div>
    @endif
  </div>
</section>

</x-app-layout>
