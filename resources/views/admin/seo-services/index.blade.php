<x-app-layout :seo="$seo">

<section class="section">
  <div class="container">
    @include('partials.admin.subnav', ['adminActive' => 'seo-services'])

    <header class="section__head reveal" style="display:flex; align-items:flex-end; justify-content:space-between; gap:24px; flex-wrap:wrap;">
      <div>
        <p class="eyebrow"><span class="dot"></span> Admin</p>
        <h2 class="section__title">SEO services</h2>
      </div>
      <a href="{{ route('admin.seo-services.create') }}" class="btn btn--primary btn--sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> New service</a>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    <div class="auth-card glass reveal" style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Name</th>
            <th style="padding:10px 12px;">Unit price</th>
            <th style="padding:10px 12px;">Min</th>
            <th style="padding:10px 12px;">Max</th>
            <th style="padding:10px 12px;">Orders</th>
            <th style="padding:10px 12px;">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($services as $service)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">{{ $service->name }}</td>
              <td style="padding:10px 12px;">${{ rtrim(rtrim(number_format((float) $service->unit_price, 4), '0'), '.') }}</td>
              <td style="padding:10px 12px;">{{ number_format($service->min_quantity) }}</td>
              <td style="padding:10px 12px;">{{ number_format($service->max_quantity) }}</td>
              <td style="padding:10px 12px;">{{ $service->orders_count }}</td>
              <td style="padding:10px 12px;">{{ $service->is_active ? 'Active' : 'Inactive' }}</td>
              <td style="padding:10px 12px;"><a href="{{ route('admin.seo-services.edit', $service) }}" class="auth-link">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

</x-app-layout>
