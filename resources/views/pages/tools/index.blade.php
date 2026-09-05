<x-app-layout :seo="$seo" active="tools">

<section class="section" id="tools">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Free tools</p>
      <h1 class="section__title">Free tools from INZRA</h1>
      <p class="section__sub">Browser-based utilities, free to use, with no sign-up and nothing sent to a server.</p>
    </header>

    <div class="cat-grid">
      @foreach ($tools as $tool)
        <a href="{{ route($tool['route']) }}" class="cat reveal">
          <span class="cat__icon" aria-hidden="true"><i class="{{ $tool['icon'] }}"></i></span>
          <h3>{{ $tool['name'] }}</h3>
          <p>{{ $tool['description'] }}</p>
          <span class="cat__meta">{{ $tool['tagline'] }} <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>

</x-app-layout>
