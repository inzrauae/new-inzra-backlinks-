<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('admin.orders.index') }}">Manage orders</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $order->order_number }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Order {{ $order->order_number }}</h2>
      <p class="section__sub">Placed {{ $order->created_at->format('j F Y, g:ia') }} by {{ $order->customer_name }}</p>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <div class="pdp__specs-table">
        <div class="pdp__spec"><span>Customer</span><b>{{ $order->customer_name }}</b></div>
        <div class="pdp__spec"><span>Email</span><b>{{ $order->customer_email }}</b></div>
        <div class="pdp__spec"><span>Phone</span><b>{{ $order->customer_phone ?: '—' }}</b></div>
        <div class="pdp__spec"><span>Payment method</span><b>{{ $order->payment_method->label() }}</b></div>
        <div class="pdp__spec"><span>Total</span><b>${{ number_format((float) $order->total, 2) }}</b></div>
      </div>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Items</h3>
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Product</th>
            <th style="padding:10px 12px;">SKU</th>
            <th style="padding:10px 12px;">Price</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->items as $item)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">
                {{ $item->product_name }}
                @if ($item->target_url)<div style="font-size:.82rem; color:var(--text-2);">Target: {{ $item->target_url }}</div>@endif
                @if ($item->anchor_text)<div style="font-size:.82rem; color:var(--text-2);">Anchor: {{ $item->anchor_text }}</div>@endif
              </td>
              <td style="padding:10px 12px;">{{ $item->sku }}</td>
              <td style="padding:10px 12px;">${{ number_format((float) $item->price, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if ($order->whatsapp_message)
      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:12px;">WhatsApp message sent</h3>
        <p style="white-space:pre-line; color:var(--text-2); font-size:.9rem;">{{ $order->whatsapp_message }}</p>
      </div>
    @endif

    <div class="auth-card glass reveal">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Update order</h3>

      <form method="POST" action="{{ route('admin.orders.update', $order) }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="auth-group">
          <label for="status" class="auth-label">Order status</label>
          <select id="status" name="status" class="auth-input">
            @foreach ($statuses as $status)
              <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
            @endforeach
          </select>
        </div>

        <div class="auth-group">
          <label for="payment_status" class="auth-label">Payment status</label>
          <select id="payment_status" name="payment_status" class="auth-input">
            @foreach ($paymentStatuses as $paymentStatus)
              <option value="{{ $paymentStatus->value }}" @selected($order->payment_status === $paymentStatus)>{{ $paymentStatus->label() }}</option>
            @endforeach
          </select>
        </div>

        <div class="auth-group">
          <label for="admin_note" class="auth-label">Admin note (included in the customer's email)</label>
          <textarea id="admin_note" name="admin_note" class="auth-input" rows="3">{{ old('admin_note', $order->admin_note) }}</textarea>
        </div>

        <div class="auth-group">
          <label for="delivery_url" class="auth-label">Delivery link <span style="font-weight:400; color:var(--text-2);">(optional — the live URL, a report link, etc.)</span></label>
          <input type="url" id="delivery_url" name="delivery_url" class="auth-input @error('delivery_url') has-error @enderror" value="{{ old('delivery_url', $order->delivery_url) }}" placeholder="https://example.com/the-live-post">
          @error('delivery_url')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-group">
          <label for="delivery_file" class="auth-label">Delivery file <span style="font-weight:400; color:var(--text-2);">(optional — screenshot, PDF report, etc. Max 10MB)</span></label>
          @if ($order->delivery_file_path)
            <p style="font-size:.85rem; color:var(--text-2); margin-bottom:8px;">
              Current file: <a href="{{ route('orders.delivery', $order) }}" class="auth-link">{{ $order->delivery_file_name }}</a>
            </p>
            <label style="display:flex; align-items:center; gap:8px; font-size:.85rem; color:var(--text-2); margin-bottom:8px;">
              <input type="checkbox" name="remove_delivery_file" value="1"> Remove this file
            </label>
          @endif
          <input type="file" id="delivery_file" name="delivery_file" class="auth-input @error('delivery_file') has-error @enderror">
          @error('delivery_file')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <button type="submit" class="btn btn--primary ripple">Save changes</button>
      </form>
    </div>
  </div>
</section>

</x-app-layout>
