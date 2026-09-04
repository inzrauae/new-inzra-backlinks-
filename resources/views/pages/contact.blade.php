<x-app-layout :seo="$seo" active="contact">

<section class="section" id="faq">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> FAQ</p>
      <h2 class="section__title">Questions people ask before their first order</h2>
    </header>

    <div class="faq">
      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>Are these links safe after a Google core update?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>No vendor can promise immunity, and anyone who does is selling you something. What we control is risk: publishers with genuine organic traffic, editorial review on every article, mixed anchor text, and drip-fed delivery. We also monitor your placements for 12 months and replace anything that gets removed or nofollowed.</p></div>
      </div>

      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>How long until I see a ranking change?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>Indexing usually happens inside 14 days. Movement on competitive keywords typically starts between weeks 6 and 12, and compounds from there. If a page is stuck on page three, a handful of strong links moves it faster than a page-eight page will ever move.</p></div>
      </div>

      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>Can I see the website before I pay?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>Yes. Marketplace listings show full metrics, traffic charts and sample published articles. For retainer plans we send three named domains per placement and you choose. Some publishers ask us to keep their domain private until an order is placed — those are labelled clearly.</p></div>
      </div>

      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>Do you write the content, or do I?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>Either. Every package includes an article written by a native-language writer with experience in your niche, and you review it before submission. If you'd rather supply your own copy, deduct $40 from the package price at checkout.</p></div>
      </div>

      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>What happens if the link is removed?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>Our monitor checks each live URL weekly. If a link disappears, turns nofollow or the page 404s within 12 months, we place a replacement of equal or better metrics at no cost. If we can't, that placement is refunded in full.</p></div>
      </div>

      <div class="faq__item reveal">
        <button class="faq__q" type="button" aria-expanded="false">
          <span>Which niches do you refuse?</span>
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </button>
        <div class="faq__a"><p>We don't place links for adult content, unlicensed pharmaceuticals, weapons or anything requiring a licence we can't verify. Gambling and CBD are handled case by case with publishers who accept them and disclose properly.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="section section--tint" id="contact">
  <div class="container">
    <div class="news reveal">
      <div class="news__inner">
        <h2 class="news__title">New publishers, sent every Tuesday</h2>
        <p class="news__sub">Roughly 40 sites join the marketplace each week. Get the list with metrics before the good ones sell out.</p>

        <form class="news__form" id="newsForm" novalidate>
          <label class="sr-only" for="newsEmail">Email address</label>
          <div class="field">
            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
            <input type="email" id="newsEmail" name="email" placeholder="you@company.com" autocomplete="email" required>
          </div>
          <button class="btn btn--dark ripple" type="submit">Subscribe</button>
        </form>

        <p class="news__note" id="newsNote" role="status">No more than one email a week. Unsubscribe in one click.</p>
      </div>
      <span class="news__glow" aria-hidden="true"></span>
    </div>
  </div>
</section>

</x-app-layout>
