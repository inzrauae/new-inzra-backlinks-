@php($categoryName = $product->category?->name ?? '')
<article class="listing reveal" data-title="{{ Str::lower($product->name) }}" data-category="{{ $categoryName }}">
  <div class="listing__img-wrap">
    <picture>
      @if ($product->image_path)
        <source srcset="{{ asset(Str::replaceLast('.png', '.webp', $product->image_path)) }}" type="image/webp">
      @endif
      <img src="{{ $product->image_path ? asset($product->image_path) : asset('og-cover.svg') }}" alt="{{ $product->name }}" class="listing__img" loading="lazy" width="480" height="360">
    </picture>
  </div>
  <div class="listing__body">
    <div class="listing__top">
      <span class="listing__cat">{{ $categoryName }}</span>
    </div>
    <h3 class="listing__name"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
    <div class="listing__meta">
      <span><i class="fa-solid fa-box" aria-hidden="true"></i> {{ $product->quantity_available }} available</span>
      <span><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> {{ $product->quantity_sold }} sold</span>
    </div>
    <div class="listing__foot">
      <span class="listing__price">${{ $product->formatted_price }}</span>
      <a href="{{ route('products.show', $product) }}" class="btn btn--glass btn--sm" aria-label="View {{ $product->name }}">View product</a>
    </div>
  </div>
</article>
