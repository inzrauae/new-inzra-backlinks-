<x-app-layout :seo="$seo">

<section class="section">
  <div class="container">
    <header class="section__head reveal" style="display:flex; align-items:flex-end; justify-content:space-between; gap:24px; flex-wrap:wrap;">
      <div>
        <p class="eyebrow"><span class="dot"></span> Admin</p>
        <h2 class="section__title">Overview</h2>
      </div>
      <a href="{{ route('admin.settings.payment.edit') }}" class="btn btn--glass btn--sm"><i class="fa-solid fa-credit-card" aria-hidden="true"></i> Payment settings</a>
    </header>

    <div class="stats glass reveal" style="margin-bottom:44px;">
      <div class="stat">
        <span class="stat__num">{{ $counts['users'] }}</span>
        <span class="stat__label">Total users</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $counts['orders'] }}</span>
        <span class="stat__label">Total orders</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $counts['pending'] }}</span>
        <span class="stat__label">Pending</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $counts['processing'] }}</span>
        <span class="stat__label">Processing</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $counts['completed'] }}</span>
        <span class="stat__label">Completed</span>
      </div>
      <div class="stat">
        <span class="stat__num">${{ number_format((float) $counts['sales'], 2) }}</span>
        <span class="stat__label">Total sales (paid)</span>
      </div>
    </div>

    <div class="reveal" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
      <h3 style="font-family:var(--font-display); font-size:1.3rem;">Recent orders</h3>
      <a href="{{ route('admin.orders.index') }}" class="btn btn--glass btn--sm">Manage all orders</a>
    </div>

    <div class="auth-card glass reveal" style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Order</th>
            <th style="padding:10px 12px;">Customer</th>
            <th style="padding:10px 12px;">Amount</th>
            <th style="padding:10px 12px;">Status</th>
            <th style="padding:10px 12px;">Payment</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($recentOrders as $order)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">{{ $order->order_number }}</td>
              <td style="padding:10px 12px;">{{ $order->customer_name }}</td>
              <td style="padding:10px 12px;">${{ number_format((float) $order->total, 2) }}</td>
              <td style="padding:10px 12px;">{{ $order->status->label() }}</td>
              <td style="padding:10px 12px;">{{ $order->payment_status->label() }}</td>
              <td style="padding:10px 12px;"><a href="{{ route('admin.orders.show', $order) }}" class="auth-link">Manage</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

</x-app-layout>
