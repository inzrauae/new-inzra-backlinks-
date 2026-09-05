<x-app-layout :seo="$seo" active="blog">

<section class="section" id="blog">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head section__head--split reveal">
      <div>
        <p class="eyebrow"><span class="dot"></span> Blog</p>
        <h1 class="section__title">Notes from the outreach desk</h1>
        <p class="section__sub">What we learn from 4,000 publisher conversations a month.</p>
      </div>
    </header>

    <div class="blog-grid">
      @foreach ($posts as $post)
        @include('partials.blog.card', ['post' => $post])
      @endforeach
    </div>

    @if ($posts->hasPages())
      <div class="section__more reveal">
        {{ $posts->links() }}
      </div>
    @endif
  </div>
</section>

</x-app-layout>
