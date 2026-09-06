<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#F8FAFC; padding:32px; color:#0F172A;">
  <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border-radius:12px; padding:32px; border:1px solid #E2E8F0;">
    <h1 style="font-size:20px; margin:0 0 8px;">INZRA</h1>
    <p style="font-size:16px; margin:0 0 24px;">Hi {{ $order->user->name }},</p>

    <p style="margin:0 0 24px;">Your final report for SEO order <strong>{{ $order->order_number }}</strong> is now available in your dashboard — download it as a PDF or CSV any time.</p>

    <a href="{{ route('seo-orders.show', $order) }}" style="display:inline-block; background:#2563EB; color:#fff; padding:12px 20px; border-radius:999px; text-decoration:none; font-weight:600;">View report</a>

    <p style="margin-top:32px; font-size:13px; color:#94A3B8;">— The INZRA team</p>
  </div>
</body>
</html>
