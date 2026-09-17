<x-app-layout :seo="$seo" active="ai-seo-checker">

<section class="section" id="aiseo">
  <div class="container container--narrow">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal" style="text-align:center;">
      <p class="eyebrow"><span class="dot"></span> 100% free score check</p>
      <h1 class="section__title">Free AI SEO Checker</h1>
      <p class="section__sub">Paste any URL to see how visible it is to ChatGPT, Google AI Overviews, Perplexity, Claude and Copilot — free, no sign-up.</p>
      <ul class="tag-list" aria-label="Key facts" style="justify-content:center;">
        <li><i class="fa-solid fa-bolt" aria-hidden="true"></i> Instant results</li>
        <li><i class="fa-solid fa-tag" aria-hidden="true"></i> Free score check</li>
        <li><i class="fa-solid fa-user-secret" aria-hidden="true"></i> No account needed</li>
      </ul>
    </header>

    <form id="aiseoForm" class="aiseo-form" autocomplete="off">
      <input type="text" id="aiseoUrl" name="url" class="auth-input" placeholder="https://yourwebsite.com" required maxlength="2048" inputmode="url">
      <button type="submit" class="btn btn--primary btn--lg ripple" id="aiseoSubmit">Check my AI SEO score</button>
    </form>

    <p id="aiseoError" class="conv-error" role="alert" hidden></p>

    <div id="aiseoLoading" class="aiseo-loading" hidden>
      <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Analyzing <span id="aiseoLoadingUrl"></span>…
    </div>

    <div id="aiseoResults" class="aiseo-results" hidden>

      <div class="aiseo-overall glass reveal">
        <div class="aiseo-overall__ring" id="aiseoOverallRing" style="--pct:0;">
          <span id="aiseoOverallScore">0</span><small>/100</small>
        </div>
        <div>
          <h2>AI visibility score for <span id="aiseoResultUrl"></span></h2>
          <p class="section__sub" style="margin:0;">Averaged across the 5 engines below. <strong id="aiseoIssueCount">0</strong> issues found — unlock the full report to see every fix.</p>
        </div>
      </div>

      <div class="aiseo-engine-grid" id="aiseoEngineGrid"></div>

      <div class="aiseo-findings glass reveal">
        <h3>What we found</h3>
        <div id="aiseoFindingsList"></div>
      </div>

      <div class="aiseo-paywall glass reveal" id="aiseoPaywall">
        <div class="aiseo-paywall__head">
          <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
          <div>
            <h3>Get your full optimization report</h3>
            <p>Every issue we found, in priority order, each with the exact fix — your roadmap to a 100% AI SEO score across every engine.</p>
          </div>
          <div class="aiseo-paywall__price">${{ number_format($price, 0) }}</div>
        </div>

        <p id="aiseoPaywallError" class="conv-error" role="alert" hidden></p>

        @if ($paypal->enabled && $paypal->client_id)
          <div id="paypal-button-container"></div>
          <p class="pdp__note">One-time payment via PayPal — no account required. You'll get an instant download link once payment is confirmed.</p>
        @else
          <div class="auth-card glass" style="text-align:center;">Online payment isn't configured yet. Please contact support to get the full report.</div>
        @endif

        <div id="aiseoSuccess" class="aiseo-success" hidden>
          <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
          <p>Payment confirmed — your report is ready.</p>
          <a href="#" id="aiseoDownloadLink" class="btn btn--primary btn--lg ripple">Download your PDF report</a>
        </div>
      </div>

    </div>
  </div>
</section>

<template id="aiseoEngineCardTemplate">
  <div class="aiseo-engine-card">
    <div class="aiseo-engine-card__icon"><i></i></div>
    <div class="aiseo-engine-card__body">
      <p class="aiseo-engine-card__name"></p>
      <div class="aiseo-engine-card__bar"><span></span></div>
    </div>
    <div class="aiseo-engine-card__score"></div>
  </div>
</template>

<template id="aiseoFindingTemplate">
  <div class="aiseo-finding">
    <span class="aiseo-finding__badge"></span>
    <div class="aiseo-finding__body">
      <p class="aiseo-finding__title"></p>
      <p class="aiseo-finding__detail"></p>
    </div>
  </div>
</template>

<section class="section section--tint">
  <div class="container container--narrow">
    <div class="pdp__body reveal">
      <h2>How the AI SEO score works</h2>
      <p>We fetch the public page you enter and read the same signals AI crawlers rely on: whether their bots (like GPTBot, Google-Extended, PerplexityBot and ClaudeBot) are allowed in, structured data (JSON-LD), heading structure and FAQ-style content, technical basics (title, meta description, canonical, HTTPS), and authority/freshness signals (author, publish date, outbound citations). Each of the 5 engines below weighs those signals differently, based on how it's known to crawl and cite sources — this is an on-page audit, not a live query sent to ChatGPT, Gemini or any other AI product.</p>
    </div>
    <div class="pdp__body reveal">
      <h2>Free score, paid roadmap</h2>
      <p>Your overall score and every engine's score are always free to see. The ${{ number_format($price, 0) }} report unlocks the complete, prioritized list of issues our audit found on your page — each with a plain-English fix — so you know exactly what to change to move every score toward 100%.</p>
    </div>
  </div>
</section>

<section class="section" id="aiseo-faq">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> FAQ</p>
      <h2 class="section__title">Questions about the AI SEO checker</h2>
    </header>

    @php
      $aiSeoFaqs = collect($seo->jsonLd)->firstWhere('@type', 'FAQPage')['mainEntity'] ?? [];
    @endphp

    <div class="faq">
      @foreach ($aiSeoFaqs as $faq)
        <div class="faq__item reveal">
          <button class="faq__q" type="button" aria-expanded="false">
            <span>{{ $faq['name'] }}</span>
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
          </button>
          <div class="faq__a"><p>{{ $faq['acceptedAnswer']['text'] }}</p></div>
        </div>
      @endforeach
    </div>
  </div>
</section>

@if ($paypal->enabled && $paypal->client_id)
  @push('scripts')
  <script src="https://www.paypal.com/sdk/js?client-id={{ $paypal->client_id }}&currency=USD"></script>
  @endpush
@endif

@php
  $aiseoCreateUrlTemplate = str_replace('999999999', '__ID__', route('ai-seo-checker.paypal.orders.create', ['aiSeoCheck' => 999999999]));
  $aiseoCaptureUrlTemplate = str_replace(['999999999', 'ORDERPLACEHOLDER'], ['__ID__', '__ORDER__'], route('ai-seo-checker.paypal.orders.capture', ['aiSeoCheck' => 999999999, 'paypalOrderId' => 'ORDERPLACEHOLDER']));
@endphp
@push('scripts')
<script>
  window.AISEO_CHECK_URL = @json(route('ai-seo-checker.check'));
  window.AISEO_PAYPAL_CREATE_URL_TEMPLATE = @json($aiseoCreateUrlTemplate);
  window.AISEO_PAYPAL_CAPTURE_URL_TEMPLATE = @json($aiseoCaptureUrlTemplate);
</script>
<script src="{{ asset('js/ai-seo-checker.js') }}"></script>
@endpush

</x-app-layout>
