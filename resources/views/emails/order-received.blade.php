<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#F8FAFC; padding:32px; color:#0F172A;">
  <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border-radius:12px; padding:32px; border:1px solid #E2E8F0;">
    <h1 style="font-size:20px; margin:0 0 8px;">INZRA</h1>
    <p style="font-size:16px; margin:0 0 24px;">Hi {{ $order->customer_name }},</p>

    <p style="margin:0 0 16px;">Thank you for your order — payment received and it's now with our team.</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
      <tr>
        <td style="padding:8px 0; color:#64748B;">Order number</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">Total paid</td>
        <td style="padding:8px 0; font-weight:bold;">${{ number_format((float) $order->total, 2) }}</td>
      </tr>
      @foreach ($order->items as $item)
        <tr>
          <td style="padding:8px 0; color:#64748B;">Item</td>
          <td style="padding:8px 0; font-weight:bold;">{{ $item->product_name }} &times;{{ $item->quantity }}</td>
        </tr>
      @endforeach
    </table>

    <p style="margin:0 0 16px; padding:12px 16px; background:#F1F5F9; border-radius:8px; font-size:.9rem;">One last step — go to your order page and add each item's target URL, anchor text and target country so our team can get started.</p>

    <a href="{{ route('orders.show', $order) }}" style="display:inline-block; background:#2563EB; color:#fff; padding:12px 20px; border-radius:999px; text-decoration:none; font-weight:600;">Go to your dashboard</a>

    <p style="margin-top:32px; font-size:13px; color:#94A3B8;">— The INZRA team</p>
  </div>
</body>
</html>
