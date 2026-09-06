<x-app-layout :seo="$seo">

<section class="section">
  <div class="container">
    @include('partials.admin.subnav', ['adminActive' => 'seo-orders'])

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Admin</p>
      <h2 class="section__title">SEO orders</h2>
    </header>

    <form method="GET" action="{{ route('admin.seo-orders.index') }}" class="store-tools reveal">
      <div class="store-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search order #, name or email…" aria-label="Search SEO orders">
      </div>
      <select name="status" class="store-select" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach ($statuses as $status)
          <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
      </select>
      <select name="payment_status" class="store-select" onchange="this.form.submit()">
        <option value="">All payment statuses</option>
        @foreach ($paymentStatuses as $paymentStatus)
          <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
        @endforeach
      </select>
      <select name="service_id" class="store-select" onchange="this.form.submit()">
        <option value="">All services</option>
        @foreach ($services as $service)
          <option value="{{ $service->id }}" @selected((string) request('service_id') === (string) $service->id)>{{ $service->name }}</option>
        @endforeach
      </select>
      <select name="country_id" class="store-select" onchange="this.form.submit()">
        <option value="">All countries</option>
        @foreach ($countries as $country)
          <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>{{ $country->name }}</option>
        @endforeach
      </select>
      <input type="date" name="from" value="{{ request('from') }}" class="store-select" onchange="this.form.submit()">
      <input type="date" name="to" value="{{ request('to') }}" class="store-select" onchange="this.form.submit()">
      <button type="submit" class="btn btn--glass btn--sm">Filter</button>
    </form>

    <div class="auth-card glass reveal" style="overflow-x:auto; margin-top:24px;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Order</th>
            <th style="padding:10px 12px;">Customer</th>
            <th style="padding:10px 12px;">Service</th>
            <th style="padding:10px 12px;">Qty</th>
            <th style="padding:10px 12px;">Completed</th>
            <th style="padding:10px 12px;">Progress</th>
            <th style="padding:10px 12px;">Amount</th>
            <th style="padding:10px 12px;">Payment</th>
            <th style="padding:10px 12px;">Status</th>
            <th style="padding:10px 12px;">Country</th>
            <th style="padding:10px 12px;">Created</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($orders as $order)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">{{ $order->order_number }}</td>
              <td style="padding:10px 12px;">{{ $order->user?->name }}<br><span style="color:var(--text-2); font-size:.82rem;">{{ $order->user?->email }}</span></td>
              <td style="padding:10px 12px;">{{ $order->service_name }}</td>
              <td style="padding:10px 12px;">{{ number_format($order->quantity) }}</td>
              <td style="padding:10px 12px;">{{ $order->completedCount() }}</td>
              <td style="padding:10px 12px;">{{ $order->progressPercent() }}%</td>
              <td style="padding:10px 12px;">${{ number_format((float) $order->total, 2) }}</td>
              <td style="padding:10px 12px;">{{ $order->payment_status->label() }}</td>
              <td style="padding:10px 12px;">{{ $order->order_status->label() }}</td>
              <td style="padding:10px 12px;">{{ $order->country?->name ?? '—' }}</td>
              <td style="padding:10px 12px;">{{ $order->created_at->format('j M Y') }}</td>
              <td style="padding:10px 12px;"><a href="{{ route('admin.seo-orders.show', $order) }}" class="auth-link">Manage</a></td>
            </tr>
          @empty
            <tr><td colspan="12" style="padding:20px 12px; color:var(--text-2);">No SEO orders match these filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section__more reveal">
      {{ $orders->links() }}
    </div>
  </div>
</section>

</x-app-layout>
