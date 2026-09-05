@php($categoryName = $product->category?->name ?? '')
<x-app-layout :seo="$seo">

<section class="section" id="product">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <div class="pdp-grid reveal">
      <div class="pdp__art pdp__art--1">
        <picture>
          @if ($product->image_path)
            <source srcset="{{ asset(Str::replaceLast('.png', '.webp', $product->image_path)) }}" type="image/webp">
          @endif
          <img src="{{ $product->image_path ? asset($product->image_path) : asset('og-cover.svg') }}" alt="{{ $product->name }}" class="pdp__art-img" fetchpriority="high" width="480" height="360">
        </picture>
        <span class="pdp__art-badge"><i class="fa-solid fa-link" aria-hidden="true"></i> {{ $categoryName }}</span>
      </div>

      <div class="pdp__buy glass">
        <span class="listing__cat">{{ $categoryName }}</span>
        <h1 class="pdp__title">{{ $product->name }}</h1>

        <div class="pdp__rating">
          <span class="pdp__rating-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
          <span class="pdp__rating-count">2k+ reviews</span>
        </div>

        <div class="pdp__rating-row">
          <span class="pdp__sold-badge">{{ $product->quantity_sold }} sold</span>
          <span class="pdp__stock">{{ $product->quantity_available }} available</span>
        </div>

        <p class="pdp__price">${{ $product->formatted_price }}</p>

        <div class="auth-group">
          <label class="auth-label" for="target_url">Target URL <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
          <input type="url" name="target_url" id="target_url" class="auth-input" placeholder="https://yoursite.com/page">
        </div>
        <div class="auth-group">
          <label class="auth-label" for="anchor_text">Anchor text preference <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
          <input type="text" name="anchor_text" id="anchor_text" class="auth-input" placeholder="e.g. best seo backlinks">
        </div>

        @if ($paypal->enabled && $paypal->client_id)
          @auth
            <div id="paypal-button-container" data-create-url="{{ route('paypal.orders.create', $product) }}"></div>
            <p class="pdp__note">You'll be redirected to PayPal to complete your payment securely.</p>
          @else
            <a href="{{ route('login') }}" class="btn btn--primary btn--lg btn--block ripple"><i class="fa-brands fa-paypal" aria-hidden="true"></i> Log in to pay with PayPal</a>
            <p class="pdp__note">You'll be asked to log in first, then redirected to PayPal to pay.</p>
          @endauth
        @else
          <button type="button" class="btn btn--primary btn--lg btn--block" disabled>Ordering unavailable</button>
          <p class="pdp__note">Online payment isn't configured yet. Please check back shortly.</p>
        @endif

        <div class="pdp__cta-row" style="margin-top:8px;">
          <button class="pdp__watch pkg__wish" type="button" aria-label="Add {{ $product->name }} to watchlist" aria-pressed="false"><i class="fa-regular fa-heart" aria-hidden="true"></i> Watchlist</button>
        </div>

        <ul class="pdp__infolist">
          <li><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> <div><b>Delivery</b><span>{{ config('inzra.pdp.delivery') }}</span></div></li>
          <li><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> <div><b>Returns</b><span>{{ config('inzra.pdp.returns') }}</span></div></li>
          <li><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> <div><b>Buyer protection</b><span>{{ config('inzra.pdp.buyer_protection') }}</span></div></li>
        </ul>

        <div class="pdp__seller">
          <div class="pdp__seller-avatar"><i class="fa-solid fa-link" aria-hidden="true"></i></div>
          <div class="pdp__seller-info">
            <b>Sold by INZRA</b>
            <span>40 products listed · <a href="{{ route('marketplace') }}">View all products</a></span>
          </div>
        </div>
      </div>
    </div>

    <div class="pdp__specs reveal">
      <h2>Item specifics</h2>
      <div class="pdp__specs-table">
        <div class="pdp__spec"><span>SKU</span><b>{{ $product->sku }}</b></div>
        <div class="pdp__spec"><span>Category</span><b>{{ $categoryName }}</b></div>
        <div class="pdp__spec"><span>Format</span><b>{{ config('inzra.pdp.format') }}</b></div>
        <div class="pdp__spec"><span>Delivery</span><b>{{ config('inzra.pdp.delivery') }}</b></div>
        <div class="pdp__spec"><span>Quantity available</span><b>{{ $product->quantity_available }}</b></div>
        <div class="pdp__spec"><span>Units sold</span><b>{{ $product->quantity_sold }}</b></div>
      </div>
    </div>

    <div class="pdp__body reveal">
      <h2>About this item</h2>
      {!! $product->body !!}
    </div>

    <div class="pdp__features reveal">
      <h2>Why buy from INZRA</h2>
      <div class="feat-grid">
        @foreach (config('inzra.pdp.features') as $feature)
          <div class="feat"><span class="feat__icon"><i class="{{ $feature['icon'] }}"></i></span><h3>{{ $feature['title'] }}</h3><p>{{ $feature['text'] }}</p></div>
        @endforeach
      </div>
    </div>

    @if ($related->isNotEmpty())
      <div class="pdp__related reveal">
        <h2>You may also like</h2>
        <div class="listing-grid">
          @foreach ($related as $relatedProduct)
            @include('partials.products.card', ['product' => $relatedProduct])
          @endforeach
        </div>
      </div>
    @endif

    <a href="{{ route('marketplace') }}" class="pdp__back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to marketplace</a>
  </div>
</section>

@auth
@if ($paypal->enabled && $paypal->client_id)
  @push('scripts')
  <script src="https://www.paypal.com/sdk/js?client-id={{ $paypal->client_id }}&currency={{ $product->currency }}"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('paypal-button-container');
    if (!container || typeof paypal === 'undefined') return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    paypal.Buttons({
      style: { layout: 'horizontal', color: 'gold', shape: 'pill', label: 'paypal', height: 45 },
      createOrder: function () {
        var targetUrl = document.getElementById('target_url').value;
        var anchorText = document.getElementById('anchor_text').value;

        return fetch(container.dataset.createUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ target_url: targetUrl, anchor_text: anchorText })
        })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (data.error) { throw new Error(data.error); }
            return data.id;
          });
      },
      onApprove: function (data) {
        return fetch('/paypal/orders/' + data.orderID + '/capture', {
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
        alert('Something went wrong starting PayPal checkout. Please try again in a moment.');
      }
    }).render('#paypal-button-container');
  });
  </script>
  @endpush
@endif
@endauth

</x-app-layout>
