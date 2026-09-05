<x-app-layout :seo="$seo">

<section class="hero" id="market-hero">
  <div class="container hero__copy">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <p class="hero__kicker"><span class="dot"></span> <span>{{ $market['eyebrow'] }}</span></p>

    <h1 class="hero__title">{{ $market['h1'] }}</h1>

    <p class="hero__sub">{{ $market['intro'] }}</p>

    <ul class="hero__tactics">
      @foreach ($market['link_types'] as $type)
        <li>{{ $type }}</li>
      @endforeach
    </ul>

    <div class="hero__cta">
      <a href="{{ route('contact') }}" class="btn btn--primary btn--lg ripple">
        <i class="fa-regular fa-paper-plane" aria-hidden="true"></i> Talk to us about this market
      </a>
      <a href="{{ route('marketplace') }}" class="btn btn--glass btn--lg ripple">
        <i class="fa-solid fa-store" aria-hidden="true"></i> Browse marketplace
      </a>
    </div>

    <ul class="hero__assurances">
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> No subscription required</li>
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Live URL or full refund</li>
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Ahrefs + SEMrush verified</li>
    </ul>
  </div>
</section>

<section class="section" id="market-marketplace">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Marketplace</p>
      <h2 class="section__title">Browse the marketplace</h2>
      <p class="section__sub">Every active listing, open to buyers anywhere.</p>
    </header>

    <div class="listing-grid">
      @foreach ($allProducts as $product)
        @include('partials.products.card', ['product' => $product])
      @endforeach
    </div>
  </div>
</section>

@if (!empty($market['facts']))
<section class="section section--tint" id="market-facts">
  <div class="container container--narrow">
    <div class="pdp__specs reveal">
      <h2>{{ $market['name'] }} at a glance</h2>
      <div class="pdp__specs-table">
        @foreach ($market['facts'] as $label => $value)
          <div class="pdp__spec"><span>{{ $label }}</span><b>{{ $value }}</b></div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif

<section class="section" id="market-why">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> {{ $market['eyebrow'] }}</p>
      <h2 class="section__title">{{ $market['why_heading'] }}</h2>
      <p class="section__sub">{{ $market['why_sub'] }}</p>
    </header>

    <div class="feat-grid">
      @foreach ($market['why_points'] as $point)
        <div class="feat reveal"><span class="feat__icon"><i class="{{ $point['icon'] }}"></i></span><h3>{{ $point['title'] }}</h3><p>{{ $point['text'] }}</p></div>
      @endforeach
    </div>
  </div>
</section>

@include('partials.process-timeline')

@if (!empty($market['faqs']))
<section class="section section--tint" id="market-faq">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> FAQ</p>
      <h2 class="section__title">Questions about {{ $market['name'] }}</h2>
    </header>

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
</section>
@endif

<section class="section">
  <div class="container">
    <div class="section__more reveal">
      <a href="{{ route('contact') }}" class="btn btn--primary btn--lg ripple">Talk to us about {{ $market['name'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <a href="{{ route('markets.index') }}" class="btn btn--glass btn--lg ripple">All markets</a>
    </div>
  </div>
</section>

</x-app-layout>
