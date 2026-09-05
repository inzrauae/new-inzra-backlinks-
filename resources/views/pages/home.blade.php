<x-app-layout :seo="$seo" active="home">

<section class="hero" id="home">
  <div class="container hero__copy">
    <p class="hero__kicker"><span class="dot"></span> <span>8,412 vetted publishers</span></p>

    <h1 class="hero__title">
      Buy high-authority backlinks
      <span class="hero__typed-wrap">that <span class="hero__typed" id="typed" aria-live="polite"></span><span class="caret" aria-hidden="true"></span></span>
    </h1>

    <p class="hero__sub">
      INZRA is a curated marketplace for off-page SEO — guest posts, niche edits and outreach
      links from real sites with real readers. You see the traffic, the spam score and the live URL
      before you spend a cent, and we replace any link that drops inside 12 months.
    </p>

    <ul class="hero__tactics">
      <li>Guest posts</li>
      <li>Niche edits</li>
      <li>PBN links</li>
      <li>Digital PR</li>
      <li>Local citations</li>
    </ul>

    <div class="hero__cta">
      <a href="{{ route('marketplace') }}" class="btn btn--primary btn--lg ripple">
        <i class="fa-solid fa-store" aria-hidden="true"></i> Shop backlinks
      </a>
      <a href="{{ route('pricing') }}" class="btn btn--glass btn--lg ripple">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i> View packages
      </a>
    </div>

    <ul class="hero__assurances">
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> No subscription required</li>
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Live URL or full refund</li>
      <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Ahrefs + SEMrush verified</li>
    </ul>
  </div>

  <div class="container">
    <div class="stats glass reveal">
      <div class="stat">
        <span class="stat__num" data-count="50000" data-suffix="+">0</span>
        <span class="stat__label">Backlinks delivered</span>
      </div>
      <div class="stat">
        <span class="stat__num" data-count="10000" data-suffix="+">0</span>
        <span class="stat__label">Happy customers</span>
      </div>
      <div class="stat">
        <span class="stat__num" data-count="120" data-suffix="+">0</span>
        <span class="stat__label">Countries served</span>
      </div>
      <div class="stat">
        <span class="stat__num" data-count="99" data-suffix="%">0</span>
        <span class="stat__label">Placement success</span>
      </div>
    </div>
  </div>
</section>

<section class="section" id="featured">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Marketplace</p>
      <h2 class="section__title">Popular backlink placements right now</h2>
      <p class="section__sub">A snapshot of what's live in the marketplace today. Every listing ships with verified metrics and a 12-month replacement guarantee.</p>
    </header>

    <div class="listing-grid">
      @foreach ($products as $product)
        @include('partials.products.card', ['product' => $product])
      @endforeach
    </div>

    <div class="section__more reveal">
      <a href="{{ route('marketplace') }}" class="btn btn--glass btn--lg ripple">View more products <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>

<div class="ticker" aria-label="Recent placements">
  <div class="ticker__track">
    <span><b>DR 74</b> tech blog · guest post live · Berlin</span>
    <span><b>DA 61</b> finance site · niche edit · Toronto</span>
    <span><b>DR 68</b> health magazine · guest post live · London</span>
    <span><b>DA 55</b> travel blog · contextual link · Sydney</span>
    <span><b>DR 81</b> news outlet · press release · New York</span>
    <span><b>DA 49</b> local pack · 60 citations · Austin</span>
    <span><b>DR 74</b> tech blog · guest post live · Berlin</span>
    <span><b>DA 61</b> finance site · niche edit · Toronto</span>
    <span><b>DR 68</b> health magazine · guest post live · London</span>
    <span><b>DA 55</b> travel blog · contextual link · Sydney</span>
    <span><b>DR 81</b> news outlet · press release · New York</span>
    <span><b>DA 49</b> local pack · 60 citations · Austin</span>
  </div>
</div>

<section class="section" id="why">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Why INZRA</p>
      <h2 class="section__title">What you get that a link broker won't give you</h2>
      <p class="section__sub">We reject roughly 7 in 10 sites that apply to join the marketplace. Here's what survives the filter.</p>
    </header>

    <div class="feat-grid">
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-handshake-angle"></i></span><h3>Manual outreach</h3><p>A person emails a real editor. No automated blasts, no scraped contact lists.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-globe"></i></span><h3>Real websites</h3><p>Every publisher has organic traffic we can screenshot in Ahrefs, not just a domain rating.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-shield-halved"></i></span><h3>White hat first</h3><p>Editorial standards on content, disclosure where required, and no link schemes we can't defend.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-gauge-high"></i></span><h3>Safe velocity</h3><p>Drip-fed placements and mixed anchors so your profile grows the way a real brand's does.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-truck-fast"></i></span><h3>Fast delivery</h3><p>Median time from order to live URL is 6 days. You see the queue status in your dashboard.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-tags"></i></span><h3>Honest pricing</h3><p>Publisher cost and our fee shown separately. No markup that appears at checkout.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-headset"></i></span><h3>Premium support</h3><p>A named strategist on Slack or email, replying within 2 hours on business days.</p></div>
      <div class="feat reveal"><span class="feat__icon"><i class="fa-solid fa-rotate-left"></i></span><h3>Money-back guarantee</h3><p>Link removed or nofollowed within 12 months? We replace it or refund that placement.</p></div>
    </div>
  </div>
</section>

<section class="section section--tint" id="reviews">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Reviews</p>
      <h2 class="section__title">2,841 reviews, 4.9 average</h2>
      <p class="section__sub">Only customers with a completed order can leave one.</p>
    </header>

    <div class="rev-grid">

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--blue);--b:var(--cyan)">MR</span>
          <div>
            <figcaption>Marcus Reinhardt <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇩🇪 Germany · SaaS founder</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
        <blockquote>Six guest posts over three months and our pricing page went from page four to position three. The reporting is the part I didn't expect — I can hand it straight to my board.</blockquote>
      </figure>

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--violet);--b:var(--blue)">PS</span>
          <div>
            <figcaption>Priya Shah <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇮🇳 India · Agency owner</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
        <blockquote>I've used four marketplaces. This is the only one where the traffic figure matched what I saw in my own Ahrefs account. White-label reports save me a full day each month.</blockquote>
      </figure>

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--cyan);--b:var(--green)">TO</span>
          <div>
            <figcaption>Tomás Oliveira <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇧🇷 Brazil · Ecommerce</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i></span>
        <blockquote>One publisher removed my link in month two. Support noticed before I did and had a replacement live in nine days. That's why I renewed.</blockquote>
      </figure>

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--violet);--b:var(--cyan)">AK</span>
          <div>
            <figcaption>Amara Kone <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇫🇷 France · Affiliate publisher</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
        <blockquote>The spam score filter is worth the price alone. I can build a shortlist of clean French-language sites in about ten minutes.</blockquote>
      </figure>

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--blue);--b:var(--violet)">JW</span>
          <div>
            <figcaption>James Whitfield <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇬🇧 United Kingdom · SEO consultant</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
        <blockquote>They talked me out of a package that was wrong for my client's niche and pointed me at a cheaper one. Not what I'm used to from link vendors.</blockquote>
      </figure>

      <figure class="rev reveal">
        <div class="rev__head">
          <span class="avatar" style="--a:var(--green);--b:var(--cyan)">LC</span>
          <div>
            <figcaption>Lena Chen <i class="fa-solid fa-circle-check verified" title="Verified buyer"></i></figcaption>
            <span class="rev__meta">🇸🇬 Singapore · Head of growth</span>
          </div>
        </div>
        <span class="stars stars--sm"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
        <blockquote>Onboarding took twenty minutes and the first placement was live in five days. Our referring domain count is up 63% this quarter.</blockquote>
      </figure>

    </div>
  </div>
</section>

@include('partials.process-timeline')

</x-app-layout>
