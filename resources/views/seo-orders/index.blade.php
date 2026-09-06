<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Account</p>
      <h2 class="section__title">My SEO orders</h2>
    </header>

    @if ($orders->isEmpty())
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
              <th style="padding:10px 12px;">Quantity</th>
              <th style="padding:10px 12px;">Amount</th>
              <th style="padding:10px 12px;">Payment</th>
              <th style="padding:10px 12px;">Status</th>
              <th style="padding:10px 12px;">Progress</th>
              <th style="padding:10px 12px;">Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($orders as $order)
              <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:10px 12px;">{{ $order->order_number }}</td>
                <td style="padding:10px 12px;">{{ $order->service_name }}</td>
                <td style="padding:10px 12px;">{{ number_format($order->quantity) }}</td>
                <td style="padding:10px 12px;">${{ number_format((float) $order->total, 2) }}</td>
                <td style="padding:10px 12px;">{{ $order->payment_status->label() }}</td>
                <td style="padding:10px 12px;"><span class="listing__cat">{{ $order->order_status->label() }}</span></td>
                <td style="padding:10px 12px;">{{ $order->completedCount() }} / {{ $order->quantity }} ({{ $order->progressPercent() }}%)</td>
                <td style="padding:10px 12px;">{{ $order->created_at->format('j M Y') }}</td>
                <td style="padding:10px 12px;"><a href="{{ route('seo-orders.show', $order) }}" class="auth-link">View</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="section__more reveal">
        {{ $orders->links() }}
      </div>
    @endif
  </div>
</section>

</x-app-layout>
