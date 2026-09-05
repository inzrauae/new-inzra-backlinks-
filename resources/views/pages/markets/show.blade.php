<x-app-layout :seo="$seo">

<section class="section" id="market">
  <div class="container container--narrow">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <article class="reveal">
      <p class="eyebrow"><span class="dot"></span> {{ $market['eyebrow'] }}</p>
      <h1 class="article__title">{{ $market['h1'] }}</h1>
      <p class="section__sub">{{ $market['intro'] }}</p>

      <ul class="tag-list" aria-label="Relevant backlink types">
        @foreach ($market['link_types'] as $type)
          <li>{{ $type }}</li>
        @endforeach
      </ul>

      <div class="pdp__body reveal" style="margin-top:32px;">
        <h2>{{ $market['landscape_heading'] }}</h2>
        <p>{{ $market['landscape'] }}</p>
      </div>

      <div class="pdp__body reveal">
        <h2>{{ $market['approach_heading'] }}</h2>
        <p>{{ $market['approach'] }}</p>
      </div>
    </article>

    @if ($relatedProducts->isNotEmpty())
      <div class="pdp__related reveal">
        <h2>Real inventory relevant to this market</h2>
        <div class="listing-grid">
          @foreach ($relatedProducts as $product)
            @include('partials.products.card', ['product' => $product])
          @endforeach
        </div>
      </div>
    @endif

    @if (!empty($market['faqs']))
      <div class="pdp__related reveal">
        <h2>Questions about this market</h2>
        <div class="faq">
          @foreach ($market['faqs'] as $faq)
            <div class="faq__item reveal">
              <button class="faq__q" type="button" aria-expanded="false">
                <span>{{ $faq['question'] }}</span>
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
              </button>
              <div class="faq__a"><p>{{ $faq['answer'] }}</p></div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="section__more reveal" style="margin-top:32px;">
      <a href="{{ route('contact') }}" class="btn btn--primary btn--lg ripple">Talk to us about this market <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <a href="{{ route('markets.index') }}" class="pdp__back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All markets</a>
    </div>
  </div>
</section>

</x-app-layout>
