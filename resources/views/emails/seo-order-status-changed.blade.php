<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#F8FAFC; padding:32px; color:#0F172A;">
  <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border-radius:12px; padding:32px; border:1px solid #E2E8F0;">
    <h1 style="font-size:20px; margin:0 0 8px;">INZRA</h1>
    <p style="font-size:16px; margin:0 0 24px;">Hi {{ $order->user->name }},</p>

    <p style="margin:0 0 16px;">Your SEO order <strong>{{ $order->order_number }}</strong> has been updated:</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
      <tr>
        <td style="padding:8px 0; color:#64748B;">Status</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->order_status->label() }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0; color:#64748B;">Completed</td>
        <td style="padding:8px 0; font-weight:bold;">{{ $order->completedCount() }} / {{ $order->quantity }} ({{ $order->progressPercent() }}%)</td>
      </tr>
      @if ($order->order_status->value === 'completed')
      <tr>
        <td style="padding:8px 0; color:#64748B;">Report</td>
        <td style="padding:8px 0; font-weight:bold;">Available in your dashboard</td>
      </tr>
      @endif
    </table>

    <a href="{{ route('seo-orders.show', $order) }}" style="display:inline-block; background:#2563EB; color:#fff; padding:12px 20px; border-radius:999px; text-decoration:none; font-weight:600;">View your order</a>

    <p style="margin-top:32px; font-size:13px; color:#94A3B8;">— The INZRA team</p>
  </div>
</body>
</html>
