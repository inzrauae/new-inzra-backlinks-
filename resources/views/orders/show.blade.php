<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('orders.index') }}">Your orders</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $order->order_number }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Order {{ $order->order_number }}</h2>
      <p class="section__sub">Placed {{ $order->created_at->format('j F Y, g:ia') }}</p>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

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

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:4px;">Order details</h3>
      <p class="pdp__note" style="margin-bottom:16px;">Tell us where each backlink should point so our team can get started.</p>

      @foreach ($order->items as $item)
        <form method="POST" action="{{ route('orders.items.update', [$order, $item]) }}" style="margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--line);">
          @csrf
          @method('patch')

          <p style="font-weight:600; margin-bottom:12px;">{{ $item->product_name }}</p>

          <div class="auth-group">
            <label class="auth-label" for="target_url_{{ $item->id }}">Target URL <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
            <input type="url" name="target_url" id="target_url_{{ $item->id }}" class="auth-input" value="{{ old('target_url', $item->target_url) }}" placeholder="https://yoursite.com/page">
          </div>
          <div class="auth-group">
            <label class="auth-label" for="anchor_text_{{ $item->id }}">Anchor text preference <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
            <input type="text" name="anchor_text" id="anchor_text_{{ $item->id }}" class="auth-input" value="{{ old('anchor_text', $item->anchor_text) }}" placeholder="e.g. best seo backlinks">
          </div>
          <div class="auth-group">
            <label class="auth-label" for="target_country_{{ $item->id }}">Target country <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
            <select name="target_country" id="target_country_{{ $item->id }}" class="auth-input">
              <option value="">Select a country…</option>
              @foreach ($countries as $country)
                <option value="{{ $country->name }}" @selected(old('target_country', $item->target_country) === $country->name)>{{ $country->name }}</option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn--primary ripple">Save details</button>
        </form>
      @endforeach
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
