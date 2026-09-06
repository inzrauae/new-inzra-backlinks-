<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('seo-orders.index') }}">My SEO orders</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $order->order_number }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Order {{ $order->order_number }}</h2>
      <p class="section__sub">{{ $order->service_name }} — placed {{ $order->created_at->format('j F Y, g:ia') }}</p>
    </header>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <div class="pdp__specs-table">
        <div class="pdp__spec"><span>Service</span><b>{{ $order->service_name }}</b></div>
        <div class="pdp__spec"><span>Payment status</span><b>{{ $order->payment_status->label() }}</b></div>
        <div class="pdp__spec"><span>Order status</span><b>{{ $order->order_status->label() }}</b></div>
        <div class="pdp__spec"><span>Total</span><b>${{ number_format((float) $order->total, 2) }}</b></div>
        <div class="pdp__spec"><span>Target URL</span><b>{{ $order->target_url }}</b></div>
        <div class="pdp__spec"><span>Country</span><b>{{ $order->country?->name ?? '—' }}</b></div>
        @if ($order->estimatedCompletionLabel())
          <div class="pdp__spec"><span>Estimated completion</span><b>{{ $order->estimatedCompletionLabel() }} (estimate only)</b></div>
        @endif
      </div>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Progress</h3>
      <p style="margin-bottom:10px;"><strong>{{ $order->completedCount() }}</strong> / {{ number_format($order->quantity) }} completed ({{ $order->progressPercent() }}%) — {{ number_format($order->remainingCount()) }} remaining</p>
      <div style="background:var(--line); border-radius:99px; height:10px; overflow:hidden;">
        <div style="background:var(--grad-brand, #2563EB); height:100%; width:{{ $order->progressPercent() }}%;"></div>
      </div>

      <ul style="list-style:none; padding:0; margin-top:20px; display:flex; flex-direction:column; gap:8px; font-size:.9rem;">
        <li><i class="fa-solid {{ $order->payment_status->value === 'paid' ? 'fa-circle-check' : 'fa-circle' }}" style="color:{{ $order->payment_status->value === 'paid' ? '#16A34A' : 'var(--text-2)' }};"></i> Payment received</li>
        <li><i class="fa-solid {{ in_array($order->order_status->value, ['order_received','in_progress','partially_completed','completed']) ? 'fa-circle-check' : 'fa-circle' }}" style="color:{{ in_array($order->order_status->value, ['order_received','in_progress','partially_completed','completed']) ? '#16A34A' : 'var(--text-2)' }};"></i> Order received</li>
        <li><i class="fa-solid {{ in_array($order->order_status->value, ['in_progress','partially_completed','completed']) ? 'fa-circle-check' : 'fa-circle' }}" style="color:{{ in_array($order->order_status->value, ['in_progress','partially_completed','completed']) ? '#16A34A' : 'var(--text-2)' }};"></i> Work started</li>
        <li><i class="fa-solid {{ $order->completedCount() > 0 ? 'fa-circle-check' : 'fa-circle' }}" style="color:{{ $order->completedCount() > 0 ? '#16A34A' : 'var(--text-2)' }};"></i> {{ $order->completedCount() }} / {{ $order->quantity }} completed</li>
        <li><i class="fa-solid {{ $order->order_status->value === 'completed' ? 'fa-circle-check' : 'fa-circle' }}" style="color:{{ $order->order_status->value === 'completed' ? '#16A34A' : 'var(--text-2)' }};"></i> Completed</li>
      </ul>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Keywords</h3>
      @if ($order->keywords->isEmpty())
        <p style="color:var(--text-2);">No keywords on file.</p>
      @else
        <ul style="padding-left:20px; color:var(--text-2);">
          @foreach ($order->keywords as $keyword)
            <li>{{ $keyword->keyword }}</li>
          @endforeach
        </ul>
      @endif
    </div>

    @if ($order->article || $order->instructions)
      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        @if ($order->article)
          <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:8px;">Article</h3>
          <p style="white-space:pre-line; color:var(--text-2); font-size:.9rem; margin-bottom:16px;">{{ $order->article }}</p>
        @endif
        @if ($order->instructions)
          <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:8px;">Additional instructions</h3>
          <p style="white-space:pre-line; color:var(--text-2); font-size:.9rem;">{{ $order->instructions }}</p>
        @endif
      </div>
    @endif

    <div class="auth-card glass reveal">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Final report</h3>
      @if ($order->report && $order->report->isReady())
        <p style="margin-bottom:16px; color:var(--text-2);">Report available — {{ $order->report->publication_count }} verified placement(s).</p>
        <div style="display:flex; flex-wrap:wrap; gap:12px;">
          <a href="{{ route('seo-orders.report.pdf', $order) }}" class="btn btn--primary ripple"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Download PDF</a>
          <a href="{{ route('seo-orders.report.csv', $order) }}" class="btn btn--glass ripple"><i class="fa-solid fa-file-csv" aria-hidden="true"></i> Download CSV</a>
        </div>
      @else
        <p style="color:var(--text-2);">Preparing… your report will be available here once the order is completed.</p>
      @endif
    </div>
  </div>
</section>

</x-app-layout>
