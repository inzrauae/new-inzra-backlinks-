<x-app-layout :seo="$seo">

<section class="section">
  <div class="container">
    @include('partials.admin.subnav', ['adminActive' => 'seo-reports'])

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Admin</p>
      <h2 class="section__title">SEO reports</h2>
    </header>

    <div class="auth-card glass reveal" style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Order</th>
            <th style="padding:10px 12px;">Customer</th>
            <th style="padding:10px 12px;">Service</th>
            <th style="padding:10px 12px;">Records</th>
            <th style="padding:10px 12px;">Status</th>
            <th style="padding:10px 12px;">Generated</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($reports as $report)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">{{ $report->order->order_number }}</td>
              <td style="padding:10px 12px;">{{ $report->order->user?->name }}</td>
              <td style="padding:10px 12px;">{{ $report->order->service_name }}</td>
              <td style="padding:10px 12px;">{{ $report->publication_count }}</td>
              <td style="padding:10px 12px;">{{ ucfirst($report->status) }}</td>
              <td style="padding:10px 12px;">{{ optional($report->generated_at)->format('j M Y, g:ia') ?? '—' }}</td>
              <td style="padding:10px 12px;"><a href="{{ route('admin.seo-orders.show', $report->order) }}" class="auth-link">View order</a></td>
            </tr>
          @empty
            <tr><td colspan="7" style="padding:20px 12px; color:var(--text-2);">No reports generated yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section__more reveal">
      {{ $reports->links() }}
    </div>
  </div>
</section>

</x-app-layout>
