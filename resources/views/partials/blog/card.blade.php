<article class="post reveal">
  <div class="post__img">
    <img src="{{ $post->cover_image_path ? asset($post->cover_image_path) : asset('og-cover.svg') }}" alt="{{ $post->title }}" loading="lazy">
    <span class="post__tag">{{ $post->category }}</span>
  </div>
  <div class="post__body">
    <time datetime="{{ optional($post->published_at)->toDateString() }}">{{ optional($post->published_at)->format('j F Y') }}</time>
    <h3>{{ $post->title }}</h3>
    <p>{{ $post->excerpt }}</p>
    <a href="{{ route('blog.show', $post) }}" class="link-arrow">Read article <i class="fa-solid fa-arrow-right"></i></a>
  </div>
</article>
