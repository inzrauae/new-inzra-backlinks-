<?php

/*
|--------------------------------------------------------------------------
| Market landing pages
|--------------------------------------------------------------------------
|
| Each entry drives a homepage-style landing page for that market. The
| SECTIONS mirror the homepage (hero, why-this-market, inventory, process,
| FAQ) but the content in every field below must be genuinely written for
| that specific market — not a find-replace of the country name. Where
| INZRA has real inventory for a market (Swedish, German), that's said
| honestly and linked via related_product_slugs; where it doesn't yet, the
| copy says so plainly. No invented stats, no fabricated local presence.
|
| 'facts' are plain, verifiable general knowledge (language, currency,
| dominant search engine) — not claims about INZRA itself.
|
*/

return [

    'netherlands' => [
        'name' => 'Netherlands',
        'flag' => '🇳🇱',
        'countries' => ['Netherlands'],
        'meta_title' => 'Dutch Backlinks & Link Building | Netherlands SEO | INZRA',
        'meta_description' => 'Why Dutch-language link building is underserved despite high Dutch marketing budgets, what actually works, and how INZRA approaches it.',
        'eyebrow' => 'Netherlands',
        'h1' => 'Backlinks and SEO for the Dutch market',
        'intro' => "The Netherlands has some of the highest average marketing budgets in Europe, but most backlink vendors only operate in English. That leaves a real gap for anyone targeting Dutch-reading audiences directly — .nl domains, Dutch news sites and Dutch-language blogs that don't show up in a typical English-first vendor's inventory.",
        'link_types' => ['Guest posts', 'Niche edits', 'Digital PR'],
        'facts' => [
            'Language' => 'Dutch (English widely used in business)',
            'Primary search engine' => 'Google',
            'Currency' => 'Euro (EUR)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why the Dutch market is different',
        'why_sub' => "Budgets here are strong. The pool of vendors who can genuinely deliver in Dutch is not.",
        'why_points' => [
            ['icon' => 'fa-solid fa-language', 'title' => 'A real Dutch-language moat', 'text' => 'Most vendors only operate in English, leaving Dutch-reading audiences underserved despite high local marketing budgets.'],
            ['icon' => 'fa-solid fa-user-check', 'title' => 'Native content or nothing', 'text' => "Translated English copy on a .nl domain reads as obviously foreign — we won't sell that to you as \"local.\""],
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'An expanding network, told honestly', 'text' => "We're building Dutch publisher relationships now. Tell us your target keywords and we'll scope a real campaign, not a placeholder."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Do you have real Dutch-language publishers today?', 'answer' => "We're actively building this network. Today we can scope a custom campaign for you rather than sell an English placement labelled as Dutch — talk to us about your specific target keywords and timeline."],
            ['question' => 'Why is Dutch link building harder to find than English?', 'answer' => 'Because it requires a native Dutch writer and a real relationship with a .nl publisher. Most vendors skip both and publish English content on Dutch domains instead.'],
            ['question' => 'What language is the content written in?', 'answer' => "Standard Dutch (Nederlands) from a native writer — not Flemish-specific unless you tell us your audience is in Belgium."],
            ['question' => 'Can I run a Dutch campaign alongside my existing English or German one?', 'answer' => 'Yes — most clients add Dutch alongside an existing campaign rather than replacing it.'],
        ],
    ],

    'nordics' => [
        'name' => 'Sweden, Norway & Denmark',
        'flag' => '🇸🇪🇳🇴🇩🇰',
        'countries' => ['Sweden', 'Norway', 'Denmark'],
        'meta_title' => 'Nordic Backlinks — Sweden, Norway & Denmark | INZRA',
        'meta_description' => 'Real Swedish-language backlink inventory, plus an honest look at Norway and Denmark link building — why iGaming and affiliate budgets outpace local vendor supply.',
        'eyebrow' => 'Nordics',
        'h1' => 'Backlinks and SEO for Sweden, Norway & Denmark',
        'intro' => 'iGaming and affiliate marketing move enormous budgets across Sweden, Norway and Denmark, yet very few backlink vendors have a real presence in any of the three languages.',
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Languages' => 'Swedish, Norwegian, Danish',
            'Primary search engine' => 'Google',
            'Currencies' => 'SEK, NOK, DKK',
            'Best-fit link types' => 'Guest posts, contextual links',
        ],
        'why_heading' => 'Why the Nordic market is different',
        'why_sub' => 'Three languages, one region most vendors treat as English-only.',
        'why_points' => [
            ['icon' => 'fa-solid fa-money-bill-wave', 'title' => 'iGaming and affiliate money', 'text' => 'Sweden, Norway and Denmark move enormous affiliate budgets, and local-language placements convert meaningfully better than English ones.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real Swedish inventory today', 'text' => 'See the Swedish listing above — a live product in our marketplace, not a promise.'],
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Norway & Denmark, scoped individually', 'text' => "No fixed catalog yet for Norwegian or Danish — contact us and we'll build a campaign around your specific goals."],
        ],
        'related_product_slugs' => ['premium-svenska-backlinks-500-hogkvalitativa-lankar'],
        'faqs' => [
            ['question' => 'Do you have real Swedish inventory, or is this just marketing copy?', 'answer' => 'Real — see the linked listing on this page. It\'s an actual product in our marketplace, not a placeholder.'],
            ['question' => 'What about Norway and Denmark specifically?', 'answer' => "We don't have fixed catalog listings for those yet. Contact us with your target keywords and we'll scope what's realistic."],
            ['question' => 'Which Nordic language should I start with?', 'answer' => "Swedish, if you only pick one — it's where we have live inventory today and the fastest turnaround."],
            ['question' => 'Do Swedish, Norwegian and Danish readers respond differently to the same content?', 'answer' => "Yes. They're mutually intelligible in writing, but a native reader still notices when copy \"feels\" like it was written for a different one of the three."],
        ],
    ],

    'israel' => [
        'name' => 'Israel',
        'flag' => '🇮🇱',
        'countries' => ['Israel'],
        'meta_title' => 'Israel Backlinks & SEO — Forex, Crypto & Affiliate | INZRA',
        'meta_description' => 'An honest look at Hebrew-language link building for Israeli forex, crypto and affiliate campaigns, and what INZRA can support today.',
        'eyebrow' => 'Israel',
        'h1' => 'Backlinks and SEO for the Israeli market',
        'intro' => 'Forex, crypto and affiliate marketing are three of the biggest verticals in Israeli digital marketing, and Hebrew is a genuine language barrier for most international backlink vendors.',
        'link_types' => ['Guest posts', 'Digital PR', 'Contextual links'],
        'facts' => [
            'Language' => 'Hebrew (official); English common in tech/business',
            'Primary search engine' => 'Google',
            'Currency' => 'New Israeli Shekel (ILS)',
            'Best-fit link types' => 'Guest posts, digital PR',
        ],
        'why_heading' => 'Why the Israeli market is different',
        'why_sub' => 'High-value verticals, a real language barrier most vendors ignore.',
        'why_points' => [
            ['icon' => 'fa-solid fa-chart-line', 'title' => 'Forex, crypto & affiliate heavy', 'text' => "Three of Israel's biggest digital verticals, where local-language trust signals matter most."],
            ['icon' => 'fa-solid fa-language', 'title' => 'Hebrew is a genuine barrier', 'text' => 'Most vendors have no Hebrew writers or publisher relationships at all — English-only campaigns underperform here.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => "Honest about today's limits", 'text' => 'We currently support English-language guest posts and PR aimed at international audiences; Hebrew placement is on our roadmap, not live yet.'],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Can you place Hebrew-language content?', 'answer' => "Not yet as a standing service — it's on our roadmap. We can discuss a custom scope if you have a specific need."],
            ['question' => 'What can INZRA do for Israeli campaigns today?', 'answer' => 'English-language guest posts and digital PR aimed at the international audience your Israeli business also serves.'],
            ['question' => 'Is English content enough for the Israeli tech and startup scene?', 'answer' => "Often yes — Israeli B2B tech audiences read English comfortably. Hebrew matters more for consumer-facing finance, crypto and affiliate campaigns."],
            ['question' => "What's the timeline for scoping a Hebrew campaign?", 'answer' => "We don't have a fixed timeline since it's not an active service yet — reach out and we'll tell you honestly what's realistic."],
        ],
    ],

    'uae-saudi-arabia' => [
        'name' => 'UAE & Saudi Arabia',
        'flag' => '🇦🇪🇸🇦',
        'countries' => ['United Arab Emirates', 'Saudi Arabia'],
        'meta_title' => 'UAE & Saudi Arabia Backlinks — Arabic SEO | INZRA',
        'meta_description' => "INZRA is based in Dubai. An honest, locally-grounded look at Arabic SEO and link building across the UAE and Saudi market.",
        'eyebrow' => 'UAE & Saudi Arabia',
        'h1' => 'Backlinks and SEO for the UAE & Saudi market',
        'intro' => "INZRA is based in Dubai, so the UAE and Saudi market isn't a new region for us — it's home turf.",
        'link_types' => ['Guest posts', 'Digital PR', 'Local citations'],
        'facts' => [
            'Language' => 'Arabic (official); English widely used in business',
            'Primary search engine' => 'Google',
            'Currencies' => 'AED (UAE), SAR (Saudi Arabia)',
            'Best-fit link types' => 'Guest posts, local citations',
        ],
        'why_heading' => 'Why the Gulf market is different',
        'why_sub' => 'Big budgets, and a market we actually operate in day to day.',
        'why_points' => [
            ['icon' => 'fa-solid fa-location-dot', 'title' => 'Our actual home market', 'text' => "INZRA is Dubai-based — this isn't a new region for us, it's where we operate day to day."],
            ['icon' => 'fa-solid fa-language', 'title' => 'Arabic SEO, badly underserved', 'text' => 'Big Gulf budgets are still mostly served English-only, while Arabic-first content converts meaningfully better.'],
            ['icon' => 'fa-solid fa-bolt', 'title' => 'Direct relationships, fast turnaround', 'text' => "Being on the ground means real publisher relationships and content that doesn't read like a translation."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Is INZRA actually based in the UAE, or is this just a marketing angle?', 'answer' => "Yes — INZRA is a real, Dubai-registered business. It's not a claim we're making up for this page."],
            ['question' => 'Do you offer Arabic-language content specifically?', 'answer' => "We can scope Arabic or bilingual Arabic/English campaigns — contact us with your target market and keywords."],
            ['question' => 'Do you serve both the UAE and Saudi Arabia, or just Dubai?', 'answer' => 'Both — being Gulf-based gives us reach across the wider region, not just the UAE.'],
            ['question' => 'Is Arabic content written by a native speaker?', 'answer' => 'Yes — when we scope an Arabic campaign, it\'s a native Arabic writer, not a translation.'],
        ],
    ],

    'japan' => [
        'name' => 'Japan',
        'flag' => '🇯🇵',
        'countries' => ['Japan'],
        'meta_title' => 'Japan Backlinks & SEO — An Honest Assessment | INZRA',
        'meta_description' => "Japan has large digital budgets and few foreign link-building vendors — here's why entry is genuinely hard, and what INZRA can honestly offer today.",
        'eyebrow' => 'Japan',
        'h1' => 'Backlinks and SEO for the Japanese market',
        'intro' => "Japan has some of the largest digital marketing budgets in Asia and almost no foreign backlink vendors competing for them — but that's because entering the market is genuinely hard, not because the opportunity is small.",
        'link_types' => ['Digital PR', 'Guest posts'],
        'facts' => [
            'Language' => 'Japanese',
            'Primary search engine' => 'Google (Yahoo! Japan also widely used)',
            'Currency' => 'Japanese Yen (JPY)',
            'Best-fit link types' => 'Digital PR, guest posts',
        ],
        'why_heading' => 'Why the Japanese market is different',
        'why_sub' => 'A real opportunity, and a real barrier to entry — we won\'t pretend otherwise.',
        'why_points' => [
            ['icon' => 'fa-solid fa-yen-sign', 'title' => 'Large budgets, thin foreign competition', 'text' => "Japan has some of Asia's biggest digital budgets and almost no foreign link-building vendors competing for them."],
            ['icon' => 'fa-solid fa-lock', 'title' => 'Trust is the real barrier', 'text' => 'Japanese publishers are typically slow to work with vendors that lack a local presence or a direct introduction.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'No active network yet — said plainly', 'text' => "We'd rather tell you that directly than oversell it. Contact us and we'll be upfront about what's realistic today."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Do you have Japanese publishers today?', 'answer' => 'No, not as an active network — this page exists to be honest about that rather than imply coverage we don\'t have.'],
            ['question' => 'Why is Japan harder to enter than other large markets?', 'answer' => 'Publisher trust there is typically built through local presence and direct introductions, not cold outreach — which is a slower, different process than most Western link building.'],
            ['question' => 'Is Yahoo! Japan worth optimizing for separately from Google?', 'answer' => "Yahoo! Japan has used Google's search results and ranking algorithm since 2010, so optimizing for Google covers both in practice."],
            ['question' => 'What would it take for INZRA to build a real Japanese network?', 'answer' => "Direct local relationships and likely a local partner — real lead time we don't have a date for yet, which is why we're not promising it here."],
        ],
    ],

    'south-korea' => [
        'name' => 'South Korea',
        'flag' => '🇰🇷',
        'countries' => ['South Korea'],
        'meta_title' => 'South Korea Backlinks & SEO — Naver, Not Just Google | INZRA',
        'meta_description' => 'Why Korean SEO needs a Naver-specific strategy alongside link building, and an honest look at what INZRA can offer in this market today.',
        'eyebrow' => 'South Korea',
        'h1' => 'Backlinks and SEO for the Korean market',
        'intro' => "Korea shares Japan's combination of large budgets and thin foreign competition, with a slightly lower barrier to entry for vendors willing to invest in the relationships.",
        'link_types' => ['Digital PR', 'Guest posts'],
        'facts' => [
            'Language' => 'Korean',
            'Primary search engines' => 'Naver, Google',
            'Currency' => 'Korean Won (KRW)',
            'Best-fit link types' => 'Digital PR, guest posts',
        ],
        'why_heading' => 'Why the Korean market is different',
        'why_sub' => "It's not just Google — and most vendors miss that entirely.",
        'why_points' => [
            ['icon' => 'fa-solid fa-magnifying-glass', 'title' => 'Naver, not just Google', 'text' => 'Korean search behaviour runs through Naver as much as Google, which changes what "SEO" even means locally.'],
            ['icon' => 'fa-solid fa-won-sign', 'title' => 'Large budgets, low competition', 'text' => 'Similar opportunity profile to Japan, with a somewhat lower barrier to entry for vendors willing to invest.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'An emerging market for us, not an active one', 'text' => "We'll say so plainly if you reach out, and can talk through what a real Korean campaign needs."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Can INZRA help with Naver specifically?', 'answer' => "Not as a packaged service today. It's a genuinely different discipline from Google SEO, and we'd want to scope it properly with you rather than bolt it onto a standard link-building package."],
            ['question' => 'Does ranking on Google help if my audience uses Naver?', 'answer' => "Only partly. Naver has its own indexing and its own blog/cafe ecosystem, and doesn't weight backlinks the way Google does — a Google-only strategy will underperform there."],
            ['question' => 'Can you do Naver blog or cafe placements?', 'answer' => "Not as a standing service today — it's a genuinely different discipline from link building and we'd want to scope it as its own project."],
        ],
    ],

    'central-europe' => [
        'name' => 'Czechia, Hungary & Romania',
        'flag' => '🇨🇿🇭🇺🇷🇴',
        'countries' => ['Czechia', 'Hungary', 'Romania'],
        'meta_title' => 'Czech, Hungarian & Romanian Backlinks | Central Europe SEO | INZRA',
        'meta_description' => 'Growing SEO spend across Czechia, Hungary and Romania, and why treating them as three separate language markets (not one region) actually matters.',
        'eyebrow' => 'Central Europe',
        'h1' => 'Backlinks and SEO for Czechia, Hungary & Romania',
        'intro' => 'SEO spend across Czechia, Hungary and Romania has been climbing steadily, while the number of vendors who can genuinely place content in Czech, Hungarian or Romanian has not kept pace.',
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Languages' => 'Czech, Hungarian, Romanian',
            'Primary search engine' => 'Google (Seznam.cz also used in Czechia)',
            'Currencies' => 'CZK, HUF, RON',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why this market is different',
        'why_sub' => 'Three languages, three publisher landscapes — not one region.',
        'why_points' => [
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Growing SEO spend', 'text' => "Spend across Czechia, Hungary and Romania has climbed steadily while vendor supply hasn't kept pace."],
            ['icon' => 'fa-solid fa-language', 'title' => 'Three languages, not one region', 'text' => 'Most vendors covering "Eastern Europe" quietly run English content across all three — obviously foreign to local readers.'],
            ['icon' => 'fa-solid fa-list-check', 'title' => 'Scoped separately, by language', 'text' => "Tell us which of the three you need — we won't bundle it into one generic package."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Is this one package covering all three countries?', 'answer' => "No — we scope Czech, Hungarian and Romanian campaigns separately, since they're different languages and different publisher networks."],
            ['question' => 'Is Seznam.cz worth targeting separately in Czechia?', 'answer' => "For a serious Czech campaign, yes — it's a real, independently-indexed search engine, not a Google mirror, so it needs its own consideration."],
            ['question' => 'Do you write in Hungarian and Romanian with native writers?', 'answer' => "For any campaign we scope in those languages, yes — we won't publish machine-translated content under a client's name."],
        ],
    ],

    'switzerland-austria' => [
        'name' => 'Switzerland & Austria',
        'flag' => '🇨🇭🇦🇹',
        'countries' => ['Switzerland', 'Austria'],
        'meta_title' => 'Swiss & Austrian Backlinks — German-Language SEO | INZRA',
        'meta_description' => 'Real German-language backlink inventory that works directly for Swiss-German and Austrian audiences, plus why per-link prices run highest here in Europe.',
        'eyebrow' => 'Switzerland & Austria',
        'h1' => 'Backlinks and SEO for Switzerland & Austria',
        'intro' => 'Switzerland and Austria have the highest average per-link prices in Europe, and both are effectively part of the German-language SEO world — which is where we already have real inventory.',
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Language' => 'German (Swiss German spoken; standard German written)',
            'Primary search engine' => 'Google',
            'Currencies' => 'CHF (Switzerland), EUR (Austria)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why this market is different',
        'why_sub' => 'Premium pricing, and a language advantage we already have.',
        'why_points' => [
            ['icon' => 'fa-solid fa-coins', 'title' => 'Highest per-link prices in Europe', 'text' => 'Switzerland and Austria command premium pricing for genuine local placements.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real German-language inventory today', 'text' => 'See the current listings above — the same publishers and approach that work for German buyers work here too.'],
            ['icon' => 'fa-solid fa-language', 'title' => 'Light localisation on request', 'text' => 'Swiss-German spelling and terminology available on request where it matters for your campaign.'],
        ],
        'related_product_slugs' => [
            '500-deutsche-backlinks-backlinks-kaufen-seo-link',
            '80-german-backlinks-dofollow-by-top-level-domain',
        ],
        'faqs' => [
            ['question' => 'Are these German listings actually usable for Swiss or Austrian audiences?', 'answer' => 'Yes, generally — the language is shared. We offer light localisation on request for Swiss-specific spelling/terminology if that matters for your campaign.'],
            ['question' => 'Is Swiss German different enough to need separate content?', 'answer' => 'Spoken Swiss German varies a lot by canton, but written content (including web content) is standard German almost everywhere — so our existing German inventory works with only light localisation.'],
            ['question' => 'Why are per-link prices higher in Switzerland and Austria?', 'answer' => 'Smaller, wealthier markets with less publisher competition than Germany itself — genuine supply and demand, not a vendor markup.'],
        ],
    ],

    'united-kingdom' => [
        'name' => 'United Kingdom',
        'flag' => '🇬🇧',
        'countries' => ['United Kingdom'],
        'meta_title' => 'UK Backlinks & Link Building | British SEO | INZRA',
        'meta_description' => "Real UK-based backlink inventory, plus what actually moves rankings in one of the world's most competitive English-language SEO markets.",
        'eyebrow' => 'United Kingdom',
        'h1' => 'Backlinks and SEO for the UK market',
        'intro' => "The UK is one of the most competitive English-language SEO markets in the world — high publisher quality expectations, aggressive competitor link profiles, and buyers who can spot a low-quality placement immediately.",
        'link_types' => ['Guest posts', 'Niche edits', 'Digital PR'],
        'facts' => [
            'Language' => 'English',
            'Primary search engine' => 'Google',
            'Currency' => 'British Pound (GBP)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why the UK market is different',
        'why_sub' => 'Same language as our biggest market, much higher competition for the same links.',
        'why_points' => [
            ['icon' => 'fa-solid fa-scale-balanced', 'title' => 'High competitive intensity', 'text' => 'UK SEO is crowded — links that would move the needle in a smaller market barely register here without real relevance and authority.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real UK inventory today', 'text' => 'See the current UK listing above — a genuine UK-hosted, UK-audience placement, not a repurposed US one.'],
            ['icon' => 'fa-solid fa-magnifying-glass', 'title' => 'Quality bar, not volume', 'text' => "One relevant UK placement outperforms ten generic English ones for a UK-targeted campaign — we'd rather sell you fewer, better links."],
        ],
        'related_product_slugs' => ['100-permanent-uk-backlinks-with-high-pr-sites'],
        'faqs' => [
            ['question' => 'Is this actually a UK-hosted, UK-audience placement?', 'answer' => "Yes — see the linked listing on this page. It's a real product in our marketplace, not a US site with a .co.uk redirect."],
            ['question' => 'How is UK SEO different from US SEO if the language is the same?', 'answer' => "Publisher expectations, competitor link profiles, and what Google treats as locally relevant all differ — a US-focused campaign doesn't automatically translate."],
            ['question' => 'Can I combine UK-specific links with a broader English campaign?', 'answer' => 'Yes — most clients layer a UK-specific placement on top of a wider English-language campaign rather than choosing one or the other.'],
        ],
    ],

    'germany' => [
        'name' => 'Germany',
        'flag' => '🇩🇪',
        'countries' => ['Germany'],
        'meta_title' => 'German Backlinks & Link Building | Germany SEO | INZRA',
        'meta_description' => 'Real German-language backlink inventory — genuine German-hosted placements for Germany-targeted campaigns, not a translated afterthought.',
        'eyebrow' => 'Germany',
        'h1' => 'Backlinks and SEO for the German market',
        'intro' => "Germany is Europe's largest digital economy, and German-language content is one of the few areas where we already have real, live inventory rather than a roadmap.",
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Language' => 'German',
            'Primary search engine' => 'Google',
            'Currency' => 'Euro (EUR)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why the German market is different',
        'why_sub' => 'The largest economy in the EU, and one of our few markets with real inventory today.',
        'why_points' => [
            ['icon' => 'fa-solid fa-industry', 'title' => "Europe's largest digital economy", 'text' => 'Germany moves more SEO and marketing budget than any other EU country, with a correspondingly deep and demanding publisher landscape.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real inventory, not a roadmap', 'text' => 'See the current German listings above — live products in our marketplace, built specifically for German-reading audiences.'],
            ['icon' => 'fa-solid fa-language', 'title' => 'One language, three markets', 'text' => 'The same content approach also serves Austria and German-speaking Switzerland with only light localisation — see our dedicated page for that.'],
        ],
        'related_product_slugs' => [
            '500-deutsche-backlinks-backlinks-kaufen-seo-link',
            '80-german-backlinks-dofollow-by-top-level-domain',
        ],
        'faqs' => [
            ['question' => 'Is this genuinely German content, or translated English?', 'answer' => "Genuinely German — see the linked listings on this page. We don't sell translated placements as native content."],
            ['question' => 'Do these listings also work for Austria or Switzerland?', 'answer' => 'Yes, largely — see our dedicated Switzerland & Austria page for the specifics on localisation.'],
            ['question' => 'What makes German SEO different from English?', 'answer' => 'Different publisher expectations, a famously literal audience for marketing claims, and search behaviour that still rewards well-structured, thorough content over short-form copy.'],
        ],
    ],

    'spain' => [
        'name' => 'Spain',
        'flag' => '🇪🇸',
        'countries' => ['Spain'],
        'meta_title' => 'Spain Backlinks & Spanish SEO | INZRA',
        'meta_description' => 'Honest positioning on Spanish-language link building: real Latin American Spanish inventory today, and what a genuine Spain-specific campaign would need.',
        'eyebrow' => 'Spain',
        'h1' => 'Backlinks and SEO for the Spanish market',
        'intro' => "We already place real Spanish-language backlinks — but today that inventory is Latin America-focused, not Spain-specific. European Spanish has real differences in vocabulary and tone that matter for a Spain-targeted campaign.",
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Language' => 'Spanish (Castilian)',
            'Primary search engine' => 'Google',
            'Currency' => 'Euro (EUR)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why Spain is a different market from Spanish-speaking Latin America',
        'why_sub' => 'Same language, genuinely different vocabulary, tone and publisher landscape.',
        'why_points' => [
            ['icon' => 'fa-solid fa-language', 'title' => 'Castilian Spanish, not LatAm Spanish', 'text' => 'Vocabulary, verb forms and tone differ enough that a Mexican-Spanish article reads as foreign to a Spain-based audience.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'Real Spanish inventory, wrong region', 'text' => "Our existing Spanish-language products are genuinely real, but built for Mexico and Latin America — we won't sell them to you as Spain-specific."],
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Scoped honestly, on request', 'text' => "Tell us your target keywords and we'll scope a genuine Castilian Spanish campaign rather than repurpose a LatAm one."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Can I use your existing Spanish-language products for a Spain campaign?', 'answer' => "We wouldn't recommend it as-is — they're written for a Latin American audience. We can scope a Spain-specific campaign separately."],
            ['question' => 'Is the difference between LatAm and Spain Spanish really that noticeable?', 'answer' => 'Yes — similar to the difference between US and UK English. A native Spain-based reader notices immediately.'],
            ['question' => 'How long would a genuine Spain-specific campaign take to scope?', 'answer' => "A few business days once we have your target keywords and niche — we'd rather scope it properly than rush a mismatched placement."],
        ],
    ],

    'france' => [
        'name' => 'France',
        'flag' => '🇫🇷',
        'countries' => ['France'],
        'meta_title' => 'French Backlinks & Link Building | France SEO | INZRA',
        'meta_description' => "An honest look at French-language link building — why it's underserved internationally, and what INZRA can scope today.",
        'eyebrow' => 'France',
        'h1' => 'Backlinks and SEO for the French market',
        'intro' => 'France has one of the largest digital economies in Europe, and French-language SEO content is still mostly served by local agencies rather than international vendors.',
        'link_types' => ['Guest posts', 'Digital PR', 'Contextual links'],
        'facts' => [
            'Language' => 'French',
            'Primary search engine' => 'Google',
            'Currency' => 'Euro (EUR)',
            'Best-fit link types' => 'Guest posts, digital PR',
        ],
        'why_heading' => 'Why the French market is different',
        'why_sub' => 'A large, mature digital market that international link builders mostly ignore.',
        'why_points' => [
            ['icon' => 'fa-solid fa-building-columns', 'title' => 'A large, mature digital economy', 'text' => "France is one of Europe's biggest ad-spend markets, yet most international link-building vendors treat it as an afterthought to English or German."],
            ['icon' => 'fa-solid fa-language', 'title' => 'Genuinely different from machine translation', 'text' => 'French readers are quick to spot AI-translated or non-native copy — a real native writer is non-negotiable here.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'No active network yet — said plainly', 'text' => "We don't have French publisher relationships live today. Contact us and we'll be upfront about what's realistic and what timeline it needs."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Do you have French-language publishers today?', 'answer' => "Not as an active network yet — we'd rather tell you that than oversell it."],
            ['question' => 'Is machine-translated content acceptable for a French campaign?', 'answer' => "We wouldn't publish it, and French readers tend to notice anyway. Any French campaign we scope uses a native writer."],
            ['question' => 'What would you need from me to scope a French campaign?', 'answer' => 'Your target keywords, niche, and a sense of the domains you already compete with — from there we can give you an honest timeline.'],
        ],
    ],

    'italy' => [
        'name' => 'Italy',
        'flag' => '🇮🇹',
        'countries' => ['Italy'],
        'meta_title' => 'Italian Backlinks & Link Building | Italy SEO | INZRA',
        'meta_description' => "Why Italian-language link building is thin on international vendor rosters, and an honest look at what INZRA can offer today.",
        'eyebrow' => 'Italy',
        'h1' => 'Backlinks and SEO for the Italian market',
        'intro' => 'Italy has a large, established digital advertising market, but most international backlink vendors treat Italian the same way they treat French or Dutch: as an English translation exercise rather than a real local market.',
        'link_types' => ['Guest posts', 'Niche edits', 'Digital PR'],
        'facts' => [
            'Language' => 'Italian',
            'Primary search engine' => 'Google',
            'Currency' => 'Euro (EUR)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why the Italian market is different',
        'why_sub' => 'A real market, and a real gap between demand and genuine local vendors.',
        'why_points' => [
            ['icon' => 'fa-solid fa-chart-pie', 'title' => 'Established but underserved', 'text' => 'Italy has mature digital ad spend, yet genuinely local, native-language link building is harder to find than the market size would suggest.'],
            ['icon' => 'fa-solid fa-language', 'title' => 'Regional variation matters', 'text' => "Formal, editorial Italian reads differently depending on a publication's region and register — a generic translation misses this."],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'Not an active network yet', "text" => "We're honest that Italian publisher relationships aren't live today. Contact us to discuss what a real campaign would need."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Do you have Italian publishers today?', 'answer' => "No, not as an active network — this page is here to say that plainly rather than imply coverage we don't have."],
            ['question' => 'Would you use a translated English article for an Italian placement?', 'answer' => 'No — any Italian campaign we scope would use a native Italian writer, not a translation.'],
            ['question' => 'What can INZRA do for an Italian-focused business today?', 'answer' => "English-language guest posts and digital PR aimed at the international side of your business, while we're upfront that Italian-specific placement is not yet a standing service."],
        ],
    ],

    'poland' => [
        'name' => 'Poland',
        'flag' => '🇵🇱',
        'countries' => ['Poland'],
        'meta_title' => 'Polish Backlinks & Link Building | Poland SEO | INZRA',
        'meta_description' => "Poland's SEO spend is growing fast; here's an honest look at Polish-language link building and what INZRA can scope today.",
        'eyebrow' => 'Poland',
        'h1' => 'Backlinks and SEO for the Polish market',
        'intro' => "Poland is one of the fastest-growing digital ad markets in Central Europe, with SEO spend climbing faster than the pool of vendors who can genuinely place Polish-language content.",
        'link_types' => ['Guest posts', 'Niche edits', 'Contextual links'],
        'facts' => [
            'Language' => 'Polish',
            'Primary search engine' => 'Google',
            'Currency' => 'Polish Złoty (PLN)',
            'Best-fit link types' => 'Guest posts, niche edits',
        ],
        'why_heading' => 'Why the Polish market is different',
        'why_sub' => 'Fast-growing spend, a genuinely separate language from its Central European neighbours.',
        'why_points' => [
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Fast-growing SEO spend', 'text' => "Poland's digital ad market has grown quickly, and vendor supply for genuine Polish-language placements hasn't kept pace."],
            ['icon' => 'fa-solid fa-language', 'title' => 'Not interchangeable with Czech or Hungarian', 'text' => 'Polish is a distinct Slavic language — it\'s not mutually intelligible with Czech, despite both sitting in "Central Europe" on a map.'],
            ['icon' => 'fa-solid fa-circle-info', 'title' => 'Scoped honestly, not bundled', 'text' => "We don't have a fixed Polish catalog yet. Tell us your target keywords and we'll scope a real campaign rather than fold it into a generic Eastern-Europe package."],
        ],
        'related_product_slugs' => [],
        'faqs' => [
            ['question' => 'Is Poland covered under your Central Europe (Czechia/Hungary/Romania) page?', 'answer' => 'No — Poland is a separate language and a separate campaign. We keep it distinct rather than bundling it in.'],
            ['question' => 'Do you have Polish publishers today?', 'answer' => 'Not as a fixed catalog — we scope Polish campaigns individually based on your target keywords.'],
        ],
    ],

];
