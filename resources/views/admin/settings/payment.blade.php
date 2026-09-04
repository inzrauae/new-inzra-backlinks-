<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('admin.dashboard') }}">Admin</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> Payment settings</p>

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Admin</p>
      <h2 class="section__title">Payment gateway</h2>
      <p class="section__sub">Configure PayPal so customers can pay online instead of (or alongside) ordering via WhatsApp.</p>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    <div class="auth-card glass reveal">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <i class="fa-brands fa-paypal" style="font-size:1.6rem; color:#003087;"></i>
        <h3 style="font-family:var(--font-display); font-size:1.2rem;">PayPal</h3>
        @if ($paypal->enabled && $paypal->client_id)
          <span class="listing__cat" style="background:rgba(34,197,94,.14); color:#15803D;">Active — {{ ucfirst($paypal->mode) }}</span>
        @else
          <span class="listing__cat">Not active</span>
        @endif
      </div>

      <form method="POST" action="{{ route('admin.settings.payment.update') }}">
        @csrf
        @method('patch')

        <label class="auth-check" style="margin-bottom:20px;">
          <input type="checkbox" name="enabled" value="1" @checked($paypal->enabled)>
          Enable PayPal checkout on product pages
        </label>

        <div class="auth-group">
          <label class="auth-label">Mode</label>
          <label class="auth-check" style="display:inline-flex; margin-right:24px;">
            <input type="radio" name="mode" value="sandbox" @checked($paypal->mode === 'sandbox')>
            Sandbox (testing)
          </label>
          <label class="auth-check" style="display:inline-flex;">
            <input type="radio" name="mode" value="live" @checked($paypal->mode === 'live')>
            Live (real payments)
          </label>
        </div>

        <div class="auth-group">
          <label for="client_id" class="auth-label">Client ID</label>
          <input id="client_id" name="client_id" class="auth-input @error('client_id') has-error @enderror" type="text" value="{{ old('client_id', $paypal->client_id) }}" autocomplete="off">
          @error('client_id')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div class="auth-group">
          <label for="client_secret" class="auth-label">Client secret</label>
          <input id="client_secret" name="client_secret" class="auth-input @error('client_secret') has-error @enderror" type="password" placeholder="{{ $paypal->client_secret ? '••••••••••••••••  (saved — leave blank to keep it)' : '' }}" autocomplete="off">
          @error('client_secret')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <div class="auth-group">
          <label for="webhook_id" class="auth-label">Webhook ID <span style="font-weight:400; color:var(--text-2);">(optional — from your PayPal app's Webhooks tab)</span></label>
          <input id="webhook_id" name="webhook_id" class="auth-input @error('webhook_id') has-error @enderror" type="text" value="{{ old('webhook_id', $paypal->webhook_id) }}" autocomplete="off">
          @error('webhook_id')<p class="auth-error">{{ $message }}</p>@enderror
          <p style="font-size:.8rem; color:var(--text-2); margin-top:6px;">Webhook URL to register with PayPal: <code>{{ route('webhooks.paypal') }}</code></p>
        </div>

        <button type="submit" class="btn btn--primary ripple">Save settings</button>
      </form>
    </div>

    <div class="auth-card glass reveal" style="margin-top:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.05rem; margin-bottom:12px;">Where to get these</h3>
      <p style="color:var(--text-2); font-size:.9rem;">Create (or open) an app at <a href="https://developer.paypal.com/dashboard/applications" class="auth-link" target="_blank" rel="noopener">developer.paypal.com/dashboard/applications</a> — use the Sandbox app for testing and a Live app once you're ready to accept real payments. Copy its Client ID and Secret in here, and add a webhook pointing at the URL above subscribed to the <code>PAYMENT.CAPTURE.COMPLETED</code>, <code>PAYMENT.CAPTURE.DENIED</code> and <code>PAYMENT.CAPTURE.REFUNDED</code> events.</p>
    </div>
  </div>
</section>

</x-app-layout>
