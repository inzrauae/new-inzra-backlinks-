<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Account</p>
      <h2 class="section__title">Welcome, {{ Auth::user()->name }}</h2>
      <p class="section__sub">Here's a snapshot of your INZRA orders.</p>
    </header>

    <div class="stats glass reveal" style="margin-bottom:44px;">
      <div class="stat">
        <span class="stat__num">{{ $counts['total'] }}</span>
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
    </div>

    <div class="reveal" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
      <h3 style="font-family:var(--font-display); font-size:1.3rem;">Recent orders</h3>
      <a href="{{ route('orders.index') }}" class="btn btn--glass btn--sm">View all orders</a>
    </div>

    @if ($recentOrders->isEmpty())
      <div class="auth-card glass reveal">
        <p class="auth-card__sub" style="margin-bottom:16px;">You haven't placed any orders yet.</p>
        <a href="{{ route('marketplace') }}" class="btn btn--primary ripple">Browse the marketplace</a>
      </div>
    @else
      <div class="auth-card glass reveal" style="overflow-x:auto;">
        <table class="orders-table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--line);">
              <th style="padding:10px 12px;">Order</th>
              <th style="padding:10px 12px;">Amount</th>
              <th style="padding:10px 12px;">Status</th>
              <th style="padding:10px 12px;">Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recentOrders as $order)
              <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:10px 12px;">{{ $order->order_number }}</td>
                <td style="padding:10px 12px;">${{ number_format((float) $order->total, 2) }}</td>
                <td style="padding:10px 12px;"><span class="listing__cat">{{ $order->status->label() }}</span></td>
                <td style="padding:10px 12px;">{{ $order->created_at->format('j M Y') }}</td>
                <td style="padding:10px 12px;"><a href="{{ route('orders.show', $order) }}" class="auth-link">View</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    <div class="stats glass reveal" style="margin:44px 0;">
      <div class="stat">
        <span class="stat__num">{{ $seoCounts['total'] }}</span>
        <span class="stat__label">SEO orders</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $seoCounts['active'] }}</span>
        <span class="stat__label">Active</span>
      </div>
      <div class="stat">
        <span class="stat__num">{{ $seoCounts['completed'] }}</span>
        <span class="stat__label">Completed</span>
      </div>
      <div class="stat">
        <span class="stat__num">${{ number_format((float) $seoCounts['spending'], 2) }}</span>
        <span class="stat__label">Total SEO spending</span>
      </div>
    </div>

    <div class="reveal" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
      <h3 style="font-family:var(--font-display); font-size:1.3rem;">My SEO orders</h3>
      <a href="{{ route('seo-orders.index') }}" class="btn btn--glass btn--sm">View all SEO orders</a>
    </div>

    @if ($recentSeoOrders->isEmpty())
      <div class="auth-card glass reveal">
        <p class="auth-card__sub" style="margin-bottom:16px;">You haven't placed any SEO backlink orders yet.</p>
        <a href="{{ route('seo-backlink-services.index') }}" class="btn btn--primary ripple">Browse SEO backlink services</a>
      </div>
    @else
      <div class="auth-card glass reveal" style="overflow-x:auto;">
        <table class="orders-table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--line);">
              <th style="padding:10px 12px;">Order</th>
              <th style="padding:10px 12px;">Service</th>
              <th style="padding:10px 12px;">Progress</th>
              <th style="padding:10px 12px;">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recentSeoOrders as $seoOrder)
              <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:10px 12px;">{{ $seoOrder->order_number }}</td>
                <td style="padding:10px 12px;">{{ $seoOrder->service_name }}</td>
                <td style="padding:10px 12px;">{{ $seoOrder->completedCount() }} / {{ $seoOrder->quantity }} ({{ $seoOrder->progressPercent() }}%)</td>
                <td style="padding:10px 12px;"><span class="listing__cat">{{ $seoOrder->order_status->label() }}</span></td>
                <td style="padding:10px 12px;"><a href="{{ route('seo-orders.show', $seoOrder) }}" class="auth-link">View</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</section>

</x-app-layout>
