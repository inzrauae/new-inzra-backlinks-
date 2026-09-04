<x-app-layout :seo="$seo" active="marketplace">

<section class="section" id="marketplace">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Marketplace</p>
      <h2 class="section__title">Every backlink placement we currently have live</h2>
      <p class="section__sub">{{ $products->count() }} backlink listings currently in stock — guest posts, PBN links, niche edits, contextual links and more. Click any listing for full details, then order directly via WhatsApp.</p>
    </header>

    <div class="store-tools reveal">
      <div class="store-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" id="productSearch" placeholder="Search {{ $products->count() }} products…" aria-label="Search products">
      </div>
      <select id="productCategory" class="store-select" aria-label="Filter by category">
        <option value="">All categories</option>
        @foreach ($categories as $category)
          <option value="{{ $category->name }}">{{ $category->name }}</option>
        @endforeach
      </select>
    </div>

    <p class="store-count" id="storeCount">Showing {{ $products->count() }} of {{ $products->count() }} products</p>

    <div class="listing-grid" id="listingGrid">
      @foreach ($products as $product)
        @include('partials.products.card', ['product' => $product])
      @endforeach
    </div>
  </div>
</section>

</x-app-layout>
