<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    @include('partials.admin.subnav', ['adminActive' => 'seo-orders'])
    <p class="pdp__crumb reveal"><a href="{{ route('admin.seo-orders.show', $order) }}">Order {{ $order->order_number }}</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> Bulk CSV import</p>

    <header class="section__head reveal">
      <h2 class="section__title">Bulk import publication records</h2>
      <p class="section__sub">CSV columns: publisher_name, publisher_url, published_url, target_url, anchor_text, country, publication_date, status.</p>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    @if (! empty($result['error'] ?? null))
      <div class="auth-card glass reveal" style="border-color:#EF4444; margin-bottom:24px;">{{ $result['error'] }}</div>
    @endif

    @if (! empty($result) && empty($result['error']))
      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:12px;">Preview</h3>
        <p style="margin-bottom:6px;">{{ $result['total_rows'] }} row(s) read from the file.</p>
        <p style="margin-bottom:6px; color:#16A34A;">{{ $result['valid_count'] }} valid — ready to import.</p>
        <p style="margin-bottom:6px; color:#CA8A04;">{{ $result['duplicate_count'] }} duplicate — already recorded for this order, skipped.</p>
        <p style="margin-bottom:16px; color:#EF4444;">{{ $result['invalid_count'] }} invalid — skipped.</p>

        @if (! empty($result['invalid_samples']))
          <details style="margin-bottom:16px;">
            <summary style="cursor:pointer; font-weight:600;">Show invalid row errors (first {{ count($result['invalid_samples']) }})</summary>
            <ul style="padding-left:20px; margin-top:10px; color:var(--text-2); font-size:.85rem;">
              @foreach ($result['invalid_samples'] as $sample)
                <li>Line {{ $sample['line'] }}: {{ implode(' ', $sample['errors']) }}</li>
              @endforeach
            </ul>
          </details>
        @endif

        @if ($result['valid_count'] > 0)
          <form method="POST" action="{{ route('admin.seo-orders.publications.import', $order) }}">
            @csrf
            <input type="hidden" name="token" value="{{ $result['token'] }}">
            <button type="submit" class="btn btn--primary ripple">Import {{ $result['valid_count'] }} valid record(s)</button>
          </form>
        @endif
      </div>
    @endif

    <div class="auth-card glass reveal">
      <form method="POST" action="{{ route('admin.seo-orders.publications.import.preview', $order) }}" enctype="multipart/form-data">
        @csrf
        <div class="auth-group">
          <label for="csv_file" class="auth-label">CSV file <span style="font-weight:400; color:var(--text-2);">(max 10MB)</span></label>
          <input type="file" id="csv_file" name="csv_file" class="auth-input @error('csv_file') has-error @enderror" accept=".csv,text/csv" required>
          @error('csv_file')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn--glass ripple">Upload &amp; preview</button>
      </form>
    </div>
  </div>
</section>

</x-app-layout>
