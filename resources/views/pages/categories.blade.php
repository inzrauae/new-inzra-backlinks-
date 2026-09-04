<x-app-layout :seo="$seo" active="categories">

<section class="section" id="categories">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Categories</p>
      <h2 class="section__title">Pick the link type your campaign needs</h2>
      <p class="section__sub">Eight ways to build authority. Filter any category by DA, traffic, language or niche once you're inside the marketplace.</p>
    </header>

    <div class="cat-grid">
      @foreach ($categories as $category)
        <a href="{{ route('marketplace') }}" class="cat reveal">
          <span class="cat__icon"><i class="{{ $category->icon }}"></i></span>
          <h3>{{ $category->name }}</h3>
          <p>{{ $category->description }}</p>
          <span class="cat__meta">{{ $category->stat_label }} <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>

</x-app-layout>
