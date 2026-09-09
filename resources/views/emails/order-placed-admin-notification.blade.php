<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#F8FAFC; padding:32px; color:#0F172A;">
  <div style="max-width:560px; margin:0 auto; background:#FFFFFF; border-radius:12px; padding:32px; border:1px solid #E2E8F0;">
    <h1 style="font-size:20px; margin:0 0 8px;">New paid order</h1>
    <p style="margin:0 0 24px; color:#64748B;">Order {{ $order->order_number }} — payment confirmed via PayPal.</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
      <tr>
        <td style="padding:8px 0; color:#64748B;">Customer</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->customer_name }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">Email</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->customer_email }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">Phone</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->customer_phone ?: '—' }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">Total paid</td>
        <td style="padding:8px 0; font-weight:bold;">${{ number_format((float) $order->total, 2) }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">PayPal order ID</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->paypal_order_id }}</td>
      </tr>
    </table>

    <h2 style="font-size:15px; margin:0 0 12px;">Items</h2>
    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
      <thead>
        <tr style="text-align:left; border-bottom:1px solid #E2E8F0;">
          <th style="padding:8px 0; color:#64748B; font-weight:normal;">Product</th>
          <th style="padding:8px 0; color:#64748B; font-weight:normal;">Qty</th>
          <th style="padding:8px 0; color:#64748B; font-weight:normal;">Target details</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->items as $item)
          <tr style="border-bottom:1px solid #F1F5F9;">
            <td style="padding:8px 0; font-weight:bold; vertical-align:top;">{{ $item->product_name }}</td>
            <td style="padding:8px 0; vertical-align:top;">{{ $item->quantity }}</td>
            <td style="padding:8px 0; font-size:.85rem; vertical-align:top;">
              @if ($item->target_url || $item->anchor_text || $item->target_country)
                @if ($item->target_url)<div>Target: {{ $item->target_url }}</div>@endif
                @if ($item->anchor_text)<div>Anchor: {{ $item->anchor_text }}</div>@endif
                @if ($item->target_country)<div>Country: {{ $item->target_country }}</div>@endif
              @else
                <span style="color:#B45309;">Not provided yet — customer adds this from their order page</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block; background:#2563EB; color:#fff; padding:12px 20px; border-radius:999px; text-decoration:none; font-weight:600;">Open in admin</a>
  </div>
</body>
</html>
