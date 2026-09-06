<x-app-layout :seo="$seo" active="seo-backlink-services">

<section class="section" id="seo-order">
  <div class="container container--narrow">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> SEO Backlink Services</p>
      <h1 class="section__title">{{ $service->name }}</h1>
      <p class="section__sub">{{ $service->description }}</p>
    </header>

    <div class="auth-card glass reveal" id="seo-order-error" style="display:none; border-color:#EF4444; margin-bottom:20px;"></div>

    <form id="seo-order-form">
      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Target &amp; keywords</h3>

        <div class="auth-group">
          <label for="target_url" class="auth-label">Target website URL <span style="color:#EF4444;">*</span></label>
          <input type="url" id="target_url" name="target_url" class="auth-input" placeholder="https://example.com" required maxlength="500">
        </div>

        <div class="auth-group">
          <label for="keyword_1" class="auth-label">Keyword / Anchor text 1 <span style="color:#EF4444;">*</span></label>
          <input type="text" id="keyword_1" name="keyword_1" class="auth-input" required maxlength="255">
        </div>
        @for ($i = 2; $i <= 5; $i++)
          <div class="auth-group">
            <label for="keyword_{{ $i }}" class="auth-label">Keyword / Anchor text {{ $i }} <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
            <input type="text" id="keyword_{{ $i }}" name="keyword_{{ $i }}" class="auth-input" maxlength="255">
          </div>
        @endfor

        <div class="auth-group">
          <label for="country_id" class="auth-label">Target country <span style="color:#EF4444;">*</span></label>
          <select id="country_id" name="country_id" class="auth-input" required>
            <option value="">Select a country…</option>
            @foreach ($countries as $country)
              <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Content</h3>

        <div class="auth-group">
          <label for="article" class="auth-label">Article / content <span style="font-weight:400; color:var(--text-2);">(optional — plain text, no HTML)</span></label>
          <textarea id="article" name="article" class="auth-input" rows="8" maxlength="20000" placeholder="Paste your article text here, or leave blank and our team will write it."></textarea>
        </div>

        <div class="auth-group">
          <label for="instructions" class="auth-label">Additional instructions <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
          <textarea id="instructions" name="instructions" class="auth-input" rows="3" maxlength="2000"></textarea>
        </div>
      </div>

      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Quantity &amp; price</h3>

        <div class="auth-group">
          <label for="quantity" class="auth-label">Quantity <span style="color:#EF4444;">*</span> <span style="font-weight:400; color:var(--text-2);">(min {{ number_format($service->min_quantity) }}, max {{ number_format($service->max_quantity) }})</span></label>
          <input type="number" id="quantity" name="quantity" class="auth-input" value="{{ $service->min_quantity }}" min="{{ $service->min_quantity }}" max="{{ $service->max_quantity }}" step="1" required>
        </div>

        @if ($quantityPresets->isNotEmpty())
          <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
            @foreach ($quantityPresets as $preset)
              <button type="button" class="btn btn--glass btn--sm seo-qty-preset" data-qty="{{ $preset }}">{{ number_format($preset) }}</button>
            @endforeach
          </div>
        @endif

        <div class="pdp__specs-table">
          <div class="pdp__spec"><span>Unit price</span><b id="seo-unit-price">${{ number_format((float) $service->unit_price, 4) }}</b></div>
          <div class="pdp__spec"><span>Quantity</span><b id="seo-qty-display">{{ number_format($service->min_quantity) }}</b></div>
          <div class="pdp__spec"><span>Subtotal</span><b id="seo-subtotal">$0.00</b></div>
          @if ((float) config('seo_backlinks.tax_rate') > 0)
            <div class="pdp__spec"><span>Tax ({{ config('seo_backlinks.tax_rate') }}%)</span><b id="seo-tax">$0.00</b></div>
          @endif
          <div class="pdp__spec"><span>Total</span><b id="seo-total" style="font-size:1.2rem;">$0.00</b></div>
        </div>
      </div>

      <div class="auth-card glass reveal" style="margin-bottom:24px;">
        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:12px;">Terms</h3>
        <p style="font-size:.85rem; color:var(--text-2); margin-bottom:12px;">Publication availability depends on publisher/editorial requirements. Third-party metrics such as DA do not guarantee SEO performance. Publication and indexing are not guaranteed, and this service does not guarantee Google rankings.</p>
        <label class="auth-check">
          <input type="checkbox" id="terms_accepted" name="terms_accepted" required>
          I agree to the <a href="{{ route('pricing') }}" class="auth-link" target="_blank" rel="noopener">Terms of Service</a>, Privacy Policy and Refund Policy.
        </label>
      </div>

      @auth
        @if ($paypal->enabled && $paypal->client_id)
          <div id="paypal-button-container" data-create-url="{{ route('seo-paypal.orders.create', $service) }}"></div>
          <p class="pdp__note">You'll be redirected to PayPal to complete your payment securely.</p>
        @else
          <div class="auth-card glass" style="text-align:center;">Online payment isn't configured yet. Please contact support to place this order.</div>
        @endif
      @else
        <a href="{{ route('login') }}" class="btn btn--primary btn--lg btn--block ripple">Log in to place this order</a>
        <p class="pdp__note">You'll need to log in or create a free account before ordering. Your details on this page aren't saved — re-enter them after logging in.</p>
      @endauth
    </form>
  </div>
</section>

@if (Auth::check() && $paypal->enabled && $paypal->client_id)
  @push('scripts')
  <script src="https://www.paypal.com/sdk/js?client-id={{ $paypal->client_id }}&currency=USD"></script>
  @endpush
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var unitPrice = {{ (float) $service->unit_price }};
  var taxRate = {{ (float) config('seo_backlinks.tax_rate', 0) }};
  var minQty = {{ (int) $service->min_quantity }};
  var maxQty = {{ (int) $service->max_quantity }};
  var qtyInput = document.getElementById('quantity');
  var errorBox = document.getElementById('seo-order-error');

  function money(n) { return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 }); }

  function recalc() {
    var qty = parseInt(qtyInput.value, 10);
    if (isNaN(qty) || qty < 1) qty = 0;
    var subtotal = qty * unitPrice;
    var tax = taxRate > 0 ? subtotal * taxRate / 100 : 0;
    var total = subtotal + tax;

    document.getElementById('seo-qty-display').textContent = qty.toLocaleString('en-US');
    document.getElementById('seo-subtotal').textContent = money(subtotal);
    var taxEl = document.getElementById('seo-tax');
    if (taxEl) taxEl.textContent = money(tax);
    document.getElementById('seo-total').textContent = money(total);
  }

  qtyInput.addEventListener('input', recalc);
  document.querySelectorAll('.seo-qty-preset').forEach(function (btn) {
    btn.addEventListener('click', function () {
      qtyInput.value = btn.dataset.qty;
      recalc();
    });
  });
  recalc();

  function showError(message) {
    errorBox.textContent = message;
    errorBox.style.display = 'block';
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function collectPayload() {
    var qty = parseInt(qtyInput.value, 10);
    var payload = {
      target_url: document.getElementById('target_url').value.trim(),
      country_id: document.getElementById('country_id').value,
      article: document.getElementById('article').value,
      instructions: document.getElementById('instructions').value,
      quantity: qty,
      terms_accepted: document.getElementById('terms_accepted').checked ? 1 : 0,
    };
    for (var i = 1; i <= 5; i++) {
      payload['keyword_' + i] = document.getElementById('keyword_' + i).value.trim();
    }
    return payload;
  }

  function validateClientSide(payload) {
    if (!payload.target_url) return 'Please enter your target website URL.';
    if (!/^https?:\/\//i.test(payload.target_url)) return 'Target URL must start with http:// or https://';
    if (!payload.keyword_1) return 'Please enter at least one keyword.';
    if (!payload.country_id) return 'Please select a target country.';
    if (!payload.quantity || payload.quantity < minQty || payload.quantity > maxQty) return 'Quantity must be between ' + minQty + ' and ' + maxQty + '.';
    if (!payload.terms_accepted) return 'Please accept the terms to continue.';
    return null;
  }

  var container = document.getElementById('paypal-button-container');
  if (!container || typeof paypal === 'undefined') return;
  var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  paypal.Buttons({
    style: { layout: 'horizontal', color: 'gold', shape: 'pill', label: 'paypal', height: 45 },
    createOrder: function () {
      errorBox.style.display = 'none';
      var payload = collectPayload();
      var validationError = validateClientSide(payload);
      if (validationError) {
        showError(validationError);
        return Promise.reject(new Error(validationError));
      }

      return fetch(container.dataset.createUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
          if (!result.ok) {
            var message = result.data.error || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'Could not start checkout.');
            throw new Error(message);
          }
          return result.data.id;
        })
        .catch(function (err) {
          showError(err.message || 'Could not start PayPal checkout. Please try again.');
          throw err;
        });
    },
    onApprove: function (data) {
      return fetch('/seo-paypal/orders/' + data.orderID + '/capture', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
      })
        .then(function (res) { return res.json(); })
        .then(function (result) {
          if (result.redirect) { window.location.href = result.redirect; }
        });
    },
    onError: function (err) {
      console.error('PayPal checkout error', err);
      showError('Something went wrong starting PayPal checkout. Please try again in a moment.');
    }
  }).render('#paypal-button-container');
});
</script>
@endpush

</x-app-layout>
