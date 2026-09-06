<x-app-layout :seo="$seo" active="seo-backlink-services">

<section class="section" id="seo-backlink-services">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> SEO Backlink Services</p>
      <h1 class="section__title">Managed publication &amp; backlink orders</h1>
      <p class="section__sub">Pick a service, tell us your target URL, keywords and country, and our team manually places verified publications — with live pricing, order tracking and a final report you can download.</p>
    </header>

    <div class="plan-grid">
      @foreach ($services as $service)
        <article class="plan reveal">
          <h3 class="plan__name">{{ $service->name }}</h3>
          <p class="plan__for">{{ $service->description }}</p>
          <div class="plan__price"><span class="plan__cur">$</span><span class="plan__amt">{{ rtrim(rtrim(number_format((float) $service->unit_price, 4), '0'), '.') }}</span><span class="plan__per">/placement</span></div>
          <a href="{{ route('seo-backlink-services.show', $service) }}" class="btn btn--primary btn--block ripple">Order now</a>
          <ul class="plan__list">
            <li><i class="fa-solid fa-check"></i> Minimum {{ number_format($service->min_quantity) }}, maximum {{ number_format($service->max_quantity) }} per order</li>
            <li><i class="fa-solid fa-check"></i> Manually placed and verified by our team</li>
            <li><i class="fa-solid fa-check"></i> Live progress tracking in your dashboard</li>
            <li><i class="fa-solid fa-check"></i> Downloadable completion report (PDF/CSV)</li>
          </ul>
        </article>
      @endforeach
    </div>

    <div class="auth-card glass reveal" style="margin-top:36px;">
      <h2 style="font-family:var(--font-display); font-size:1.2rem; margin-bottom:12px;">How it works</h2>
      <ol style="padding-left:20px; color:var(--text-2); line-height:1.9;">
        <li>Select a service and enter your target URL, up to 5 keywords, target country and article.</li>
        <li>Choose a quantity — see your live price update instantly.</li>
        <li>Review your order and pay securely online.</li>
        <li>Our team manually places and verifies each publication.</li>
        <li>Track real, verified progress in your dashboard and download your final report once complete.</li>
      </ol>
      <p style="font-size:.85rem; color:var(--text-2); margin-top:16px;">Publication availability depends on publisher/editorial requirements. Third-party metrics such as DA do not guarantee SEO performance, and publication, indexing and Google rankings are never guaranteed.</p>
    </div>
  </div>
</section>

</x-app-layout>
