<x-app-layout :seo="$seo" active="blog">

<section class="section" id="article">
  <div class="container container--narrow">
    <p class="pdp__crumb reveal"><a href="{{ route('blog.index') }}">Blog</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $post->category }}</p>

    <article class="reveal">
      <p class="eyebrow"><span class="dot"></span> {{ $post->category }}</p>
      <h1 class="article__title">{{ $post->title }}</h1>
      <p class="article__meta">
        <time datetime="{{ optional($post->published_at)->toDateString() }}">{{ optional($post->published_at)->format('j F Y') }}</time>
        <span>·</span>
        <span>{{ $post->reading_minutes }} min read</span>
      </p>

      <div class="article__cover">
        <img src="{{ $post->cover_image_path ? asset($post->cover_image_path) : asset('og-cover.svg') }}" alt="{{ $post->title }}" loading="lazy">
      </div>

      <div class="article__body">
        {!! $post->body !!}
      </div>

      @if ($post->product)
        <div class="article__cta glass">
          <div>
            <h3>{{ $post->product->name }}</h3>
            <p>${{ $post->product->formatted_price }} · <a href="{{ route('products.show', $post->product) }}">View the full listing</a></p>
          </div>
          <a href="{{ route('products.show', $post->product) }}" class="btn btn--primary ripple">Get this listing <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
      @endif
    </article>

    @if ($related->isNotEmpty())
      <div class="pdp__related reveal">
        <h2>More from the blog</h2>
        <div class="blog-grid">
          @foreach ($related as $relatedPost)
            @include('partials.blog.card', ['post' => $relatedPost])
          @endforeach
        </div>
      </div>
    @endif
  </div>
</section>

</x-app-layout>
