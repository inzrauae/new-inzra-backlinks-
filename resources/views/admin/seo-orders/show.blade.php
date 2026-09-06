<x-app-layout :seo="$seo">

<section class="section">
  <div class="container">
    @include('partials.admin.subnav', ['adminActive' => 'seo-orders'])
    <p class="pdp__crumb reveal"><a href="{{ route('admin.seo-orders.index') }}">SEO orders</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $order->order_number }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Order {{ $order->order_number }}</h2>
      <p class="section__sub">{{ $order->service_name }} — placed {{ $order->created_at->format('j F Y, g:ia') }} by {{ $order->user?->name }}</p>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <div class="pdp__specs-table">
        <div class="pdp__spec"><span>Customer</span><b>{{ $order->user?->name }}</b></div>
        <div class="pdp__spec"><span>Email</span><b>{{ $order->user?->email }}</b></div>
        <div class="pdp__spec"><span>Target URL</span><b><a href="{{ $order->target_url }}" target="_blank" rel="noopener" class="auth-link">{{ $order->target_url }}</a></b></div>
        <div class="pdp__spec"><span>Country</span><b>{{ $order->country?->name ?? '—' }}</b></div>
        <div class="pdp__spec"><span>Quantity</span><b>{{ number_format($order->quantity) }}</b></div>
        <div class="pdp__spec"><span>Unit price</span><b>${{ rtrim(rtrim(number_format((float) $order->unit_price, 4), '0'), '.') }}</b></div>
        <div class="pdp__spec"><span>Subtotal</span><b>${{ number_format((float) $order->subtotal, 2) }}</b></div>
        @if ((float) $order->tax > 0)
          <div class="pdp__spec"><span>Tax</span><b>${{ number_format((float) $order->tax, 2) }}</b></div>
        @endif
        <div class="pdp__spec"><span>Total</span><b>${{ number_format((float) $order->total, 2) }}</b></div>
        <div class="pdp__spec"><span>Terms accepted</span><b>{{ optional($order->terms_accepted_at)->format('j M Y, g:ia') ?? '—' }} (v{{ $order->terms_version }})</b></div>
      </div>
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

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Progress</h3>
      <p style="margin-bottom:10px;"><strong>{{ $order->completedCount() }}</strong> / {{ number_format($order->quantity) }} verified ({{ $order->progressPercent() }}%) — {{ number_format($order->remainingCount()) }} remaining</p>
      <div style="background:var(--line); border-radius:99px; height:10px; overflow:hidden;">
        <div style="background:var(--grad-brand, #2563EB); height:100%; width:{{ $order->progressPercent() }}%;"></div>
      </div>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Update order</h3>
      <form method="POST" action="{{ route('admin.seo-orders.update', $order) }}">
        @csrf
        @method('patch')

        <div class="auth-group">
          <label for="order_status" class="auth-label">Order status</label>
          <select id="order_status" name="order_status" class="auth-input">
            @foreach ($statuses as $status)
              <option value="{{ $status->value }}" @selected($order->order_status === $status)>{{ $status->label() }}</option>
            @endforeach
          </select>
        </div>

        <div class="auth-group">
          <label for="payment_status" class="auth-label">Payment status</label>
          <select id="payment_status" name="payment_status" class="auth-input">
            @foreach ($paymentStatuses as $paymentStatus)
              <option value="{{ $paymentStatus->value }}" @selected($order->payment_status === $paymentStatus)>{{ $paymentStatus->label() }}</option>
            @endforeach
          </select>
        </div>

        <div class="auth-group">
          <label for="estimated_completion_at" class="auth-label">Estimated completion <span style="font-weight:400; color:var(--text-2);">(shown to the customer as an estimate only)</span></label>
          <input type="datetime-local" id="estimated_completion_at" name="estimated_completion_at" class="auth-input" value="{{ old('estimated_completion_at', optional($order->estimated_completion_at)->format('Y-m-d\TH:i')) }}">
        </div>

        <button type="submit" class="btn btn--primary ripple">Save changes</button>
      </form>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem;">Publications ({{ $publications->total() }})</h3>
        <div style="display:flex; gap:8px;">
          <a href="{{ route('admin.seo-orders.publications.import.create', $order) }}" class="btn btn--glass btn--sm"><i class="fa-solid fa-file-csv" aria-hidden="true"></i> Bulk CSV import</a>
          @if ($order->report)
            <form method="POST" action="{{ route('admin.seo-orders.report.regenerate', $order) }}">
              @csrf
              <button type="submit" class="btn btn--glass btn--sm"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> Regenerate report</button>
            </form>
          @endif
        </div>
      </div>

      <details style="margin-bottom:20px;">
        <summary style="cursor:pointer; font-weight:600; margin-bottom:12px;">+ Add publication record manually</summary>
        <form method="POST" action="{{ route('admin.seo-orders.publications.store', $order) }}" style="margin-top:12px;">
          @csrf
          <div class="auth-group">
            <label class="auth-label">Publisher name</label>
            <input type="text" name="publisher_name" class="auth-input" maxlength="255">
          </div>
          <div class="auth-group">
            <label class="auth-label">Publisher website</label>
            <input type="url" name="publisher_url" class="auth-input" placeholder="https://example.com" maxlength="500">
          </div>
          <div class="auth-group">
            <label class="auth-label">Published URL</label>
            <input type="url" name="published_url" class="auth-input" placeholder="https://example.com/article/example" maxlength="500">
          </div>
          <div class="auth-group">
            <label class="auth-label">Target URL <span style="font-weight:400; color:var(--text-2);">(defaults to the order's target URL if left blank)</span></label>
            <input type="url" name="target_url" class="auth-input" value="{{ $order->target_url }}" maxlength="500">
          </div>
          <div class="auth-group">
            <label class="auth-label">Anchor text / publication text</label>
            <input type="text" name="anchor_text" class="auth-input" maxlength="255">
          </div>
          <div class="auth-group">
            <label class="auth-label">Country</label>
            <input type="text" name="country" class="auth-input" value="{{ $order->country?->name }}" maxlength="100">
          </div>
          <div class="auth-group">
            <label class="auth-label">Publication date</label>
            <input type="date" name="publication_date" class="auth-input">
          </div>
          <div class="auth-group">
            <label class="auth-label">Status</label>
            <select name="status" class="auth-input">
              @foreach ($publicationStatuses as $publicationStatus)
                <option value="{{ $publicationStatus->value }}">{{ $publicationStatus->label() }}</option>
              @endforeach
            </select>
          </div>
          <div class="auth-group">
            <label class="auth-label">Notes</label>
            <textarea name="notes" class="auth-input" rows="2" maxlength="2000"></textarea>
          </div>
          <button type="submit" class="btn btn--primary ripple">Add record</button>
        </form>
      </details>

      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--line);">
              <th style="padding:8px;">Publisher</th>
              <th style="padding:8px;">Published URL</th>
              <th style="padding:8px;">Anchor</th>
              <th style="padding:8px;">Country</th>
              <th style="padding:8px;">Date</th>
              <th style="padding:8px;">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($publications as $publication)
              <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:8px;">{{ $publication->publisher_name }}</td>
                <td style="padding:8px; max-width:220px; overflow-wrap:anywhere;">{{ $publication->published_url }}</td>
                <td style="padding:8px;">{{ $publication->anchor_text }}</td>
                <td style="padding:8px;">{{ $publication->country }}</td>
                <td style="padding:8px;">{{ optional($publication->publication_date)->format('j M Y') }}</td>
                <td style="padding:8px;">
                  <form method="POST" action="{{ route('admin.seo-orders.publications.update', [$order, $publication]) }}" onchange="this.submit()">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="publisher_name" value="{{ $publication->publisher_name }}">
                    <input type="hidden" name="publisher_url" value="{{ $publication->publisher_url }}">
                    <input type="hidden" name="published_url" value="{{ $publication->published_url }}">
                    <input type="hidden" name="target_url" value="{{ $publication->target_url }}">
                    <input type="hidden" name="anchor_text" value="{{ $publication->anchor_text }}">
                    <input type="hidden" name="country" value="{{ $publication->country }}">
                    <input type="hidden" name="publication_date" value="{{ optional($publication->publication_date)->toDateString() }}">
                    <select name="status" class="store-select" style="padding:6px 10px;">
                      @foreach ($publicationStatuses as $publicationStatus)
                        <option value="{{ $publicationStatus->value }}" @selected($publication->status === $publicationStatus)>{{ $publicationStatus->label() }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
                <td style="padding:8px;">
                  <form method="POST" action="{{ route('admin.seo-orders.publications.destroy', [$order, $publication]) }}" onsubmit="return confirm('Remove this publication record?');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn--glass btn--sm">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" style="padding:16px 8px; color:var(--text-2);">No publication records yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="section__more reveal">
        {{ $publications->links() }}
      </div>
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Notes</h3>

      <form method="POST" action="{{ route('admin.seo-orders.notes.store', $order) }}" style="margin-bottom:20px;">
        @csrf
        <div class="auth-group">
          <label class="auth-label">Note type</label>
          <select name="type" class="auth-input">
            <option value="internal">Internal (never shown to the customer)</option>
            <option value="customer">Customer-visible</option>
          </select>
        </div>
        <div class="auth-group">
          <textarea name="body" class="auth-input" rows="3" maxlength="4000" required placeholder="Write a note…"></textarea>
        </div>
        <button type="submit" class="btn btn--glass ripple btn--sm">Add note</button>
      </form>

      @forelse ($order->notes as $note)
        <div style="padding:10px 0; border-top:1px solid var(--line);">
          <p style="font-size:.8rem; color:var(--text-2);">{{ $note->type->value === 'internal' ? 'Internal' : 'Customer-visible' }} — {{ $note->author?->name ?? 'System' }} — {{ $note->created_at->format('j M Y, g:ia') }}</p>
          <p>{{ $note->body }}</p>
        </div>
      @empty
        <p style="color:var(--text-2);">No notes yet.</p>
      @endforelse
    </div>

    <div class="auth-card glass reveal">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Status history</h3>
      @forelse ($order->statusHistory as $entry)
        <div style="padding:8px 0; border-top:1px solid var(--line); font-size:.88rem;">
          <span>{{ $entry->from_status?->label() ?? 'Created' }} → <strong>{{ $entry->to_status->label() }}</strong></span>
          <span style="color:var(--text-2);"> — {{ $entry->changedBy?->name ?? 'System' }}, {{ $entry->created_at->format('j M Y, g:ia') }}</span>
          @if ($entry->note)<div style="color:var(--text-2);">{{ $entry->note }}</div>@endif
        </div>
      @empty
        <p style="color:var(--text-2);">No history yet.</p>
      @endforelse
    </div>
  </div>
</section>

</x-app-layout>
