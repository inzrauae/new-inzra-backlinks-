<x-app-layout :seo="$seo" active="pricing">

<section class="section" id="pricing">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Pricing</p>
      <h1 class="section__title">SEO and GEO, packaged for steady growth</h1>
      <p class="section__sub">Every package covers on-page, off-page, technical SEO and Generative Engine Optimization for stronger rankings and visibility in AI search.</p>
    </header>

    <div class="plan-grid">

      <article class="plan reveal">
        <h3 class="plan__name">Backlinks Only</h3>
        <p class="plan__for">Pure off-page link-building volume for sites that just need more authority pointed at them, fast.</p>
        <div class="plan__price"><span class="plan__cur">$</span><span class="plan__amt">100</span><span class="plan__per">/month</span></div>
        <a href="{{ route('contact') }}" class="btn btn--glass btn--block ripple">Choose Backlinks Only</a>
        <ul class="plan__list">
          <li><i class="fa-solid fa-check"></i> <strong>Off-page:</strong> 1,000 backlinks delivered every month</li>
          <li><i class="fa-solid fa-check"></i> Mix of dofollow contextual, web 2.0 and niche edit links</li>
          <li><i class="fa-solid fa-check"></i> Real, indexed publisher sites — no PBN spam</li>
          <li><i class="fa-solid fa-check"></i> Anchor text ratio managed for you</li>
          <li><i class="fa-solid fa-check"></i> Monthly delivery report with live URLs</li>
        </ul>
      </article>

      <article class="plan plan--featured reveal">
        <span class="plan__badge">Most popular</span>
        <h3 class="plan__name">10 Keyword Ranking</h3>
        <p class="plan__for">A focused ranking campaign built around the 10 keywords that actually move your revenue.</p>
        <div class="plan__price"><span class="plan__cur">$</span><span class="plan__amt">200</span><span class="plan__per">/month</span></div>
        <a href="{{ route('contact') }}" class="btn btn--primary btn--block ripple">Choose this package</a>
        <ul class="plan__list">
          <li><i class="fa-solid fa-check"></i> <strong>On-page:</strong> Optimization mapped to 10 target keywords</li>
          <li><i class="fa-solid fa-check"></i> <strong>Off-page:</strong> Backlinks weighted toward your priority pages</li>
          <li><i class="fa-solid fa-check"></i> Monthly rank tracking for all 10 keywords</li>
          <li><i class="fa-solid fa-check"></i> Technical fixes blocking those pages from ranking</li>
          <li><i class="fa-solid fa-check"></i> Competitor gap check every month</li>
          <li><i class="fa-solid fa-check"></i> Monthly progress report</li>
        </ul>
      </article>

      <article class="plan reveal">
        <h3 class="plan__name">20 Keyword Ranking + GEO</h3>
        <p class="plan__for">Rank 20 keywords in Google while building the entity signals AI answer engines cite.</p>
        <div class="plan__price"><span class="plan__cur">$</span><span class="plan__amt">280</span><span class="plan__per">/month</span></div>
        <a href="{{ route('contact') }}" class="btn btn--glass btn--block ripple">Choose this package</a>
        <ul class="plan__list">
          <li><i class="fa-solid fa-check"></i> <strong>On-page:</strong> Optimization mapped to 20 target keywords</li>
          <li><i class="fa-solid fa-check"></i> <strong>GEO:</strong> AI-search entity, FAQ and citation content</li>
          <li><i class="fa-solid fa-check"></i> <strong>Off-page:</strong> Backlinks and digital outreach across your keyword set</li>
          <li><i class="fa-solid fa-check"></i> Monthly rank tracking for all 20 keywords</li>
          <li><i class="fa-solid fa-check"></i> AI citation monitoring across ChatGPT and AI Overviews</li>
          <li><i class="fa-solid fa-check"></i> Monthly progress report</li>
        </ul>
      </article>

    </div>
  </div>
</section>

</x-app-layout>
