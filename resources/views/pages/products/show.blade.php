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
          <input type="url" name="target_url" id="target_url" class="auth-input" form="cart-add-form" placeholder="https://yoursite.com/page">
        </div>
        <div class="auth-group">
          <label class="auth-label" for="anchor_text">Anchor text preference <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
          <input type="text" name="anchor_text" id="anchor_text" class="auth-input" form="cart-add-form" placeholder="e.g. best seo backlinks">
        </div>
        <div class="auth-group">
          <label class="auth-label" for="target_country">Target country <span style="font-weight:400; color:var(--text-2);">(optional)</span></label>
          <select name="target_country" id="target_country" class="auth-input" form="cart-add-form">
            <option value="">Select a country…</option>
            @foreach ($countries as $country)
              <option value="{{ $country->name }}">{{ $country->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="auth-group">
          <label class="auth-label" for="quantity">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="auth-input" form="cart-add-form" value="1" min="1" max="100" style="max-width:120px;">
        </div>

        <form id="cart-add-form" method="GET" action="{{ route('cart.add', $product) }}">
          <button type="submit" class="btn btn--primary btn--lg btn--block ripple"><i class="fa-solid fa-cart-plus" aria-hidden="true"></i> Add to Cart</button>
        </form>
        <p class="pdp__note"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure checkout with PayPal</p>
        <p class="pdp__note">Your product, target URL, anchor preference and target country will be attached to this cart item.</p>

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
<div id="cartToast" class="auth-status" role="status" aria-live="polite" hidden style="position:fixed; top:90px; right:20px; z-index:9999; max-width:320px; box-shadow:var(--sh-lg); transition:opacity .2s, transform .2s;"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('cart-add-form');
  var toast = document.getElementById('cartToast');
  if (!form || !toast) return;

  var toastTimer = null;

  function showToast(message) {
    toast.textContent = message;
    toast.hidden = false;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-8px)';
      setTimeout(function () { toast.hidden = true; }, 200);
    }, 3000);
  }

  function updateCartCount(count) {
    var desktopBadge = document.getElementById('navCartCount');
    var mobileBadge = document.getElementById('navCartCountMobile');
    if (desktopBadge) {
      desktopBadge.textContent = count;
      desktopBadge.hidden = count <= 0;
    }
    if (mobileBadge) {
      mobileBadge.textContent = count;
    }
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    var params = new URLSearchParams(new FormData(form));

    fetch(form.action + '?' + params.toString(), {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        showToast(data.message || 'Added to your cart.');
        if (typeof data.count === 'number') { updateCartCount(data.count); }
      })
      .catch(function () {
        showToast('Something went wrong adding this to your cart.');
      });
  });
});
</script>
@endpush
@endauth

</x-app-layout>
