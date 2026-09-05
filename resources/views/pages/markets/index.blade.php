<x-app-layout :seo="$seo" active="markets">

<section class="section" id="markets">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Markets</p>
      <h1 class="section__title">Link building, market by market</h1>
      <p class="section__sub">Backlink vendors tend to sell one catalog worldwide. Language, publisher trust, and search behaviour don't work that way — here's an honest look at what's realistic in each of these markets today, and where we're still building.</p>
    </header>

    <div class="cat-grid">
      @foreach ($markets as $slug => $market)
        <a href="{{ route('markets.show', $slug) }}" class="cat reveal">
          <span class="cat__icon" aria-hidden="true">{{ $market['flag'] }}</span>
          <h3>{{ $market['name'] }}</h3>
          <p>{{ $market['intro'] }}</p>
          <span class="cat__meta">Read more <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>

</x-app-layout>
