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
        'why_heading' => 'Why the Nordic market is different',
        'why_sub' => 'Three languages, one region most vendors treat as English-only.',
        'why_points' => [
            ['icon' => 'fa-solid fa-money-bill-wave', 'title' => 'iGaming and affiliate money', 'text' => 'Sweden, Norway and Denmark move enormous affiliate budgets, and local-language placements convert meaningfully better than English ones.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real Swedish inventory today', 'text' => 'See the Swedish listing below — a live product in our marketplace, not a promise.'],
            ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Norway & Denmark, scoped individually', 'text' => "No fixed catalog yet for Norwegian or Danish — contact us and we'll build a campaign around your specific goals."],
        ],
        'related_product_slugs' => ['premium-svenska-backlinks-500-hogkvalitativa-lankar'],
        'faqs' => [
            ['question' => 'Do you have real Swedish inventory, or is this just marketing copy?', 'answer' => 'Real — see the linked listing on this page. It\'s an actual product in our marketplace, not a placeholder.'],
            ['question' => 'What about Norway and Denmark specifically?', 'answer' => "We don't have fixed catalog listings for those yet. Contact us with your target keywords and we'll scope what's realistic."],
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
        'why_heading' => 'Why this market is different',
        'why_sub' => 'Premium pricing, and a language advantage we already have.',
        'why_points' => [
            ['icon' => 'fa-solid fa-coins', 'title' => 'Highest per-link prices in Europe', 'text' => 'Switzerland and Austria command premium pricing for genuine local placements.'],
            ['icon' => 'fa-solid fa-circle-check', 'title' => 'Real German-language inventory today', 'text' => 'See the current listings below — the same publishers and approach that work for German buyers work here too.'],
            ['icon' => 'fa-solid fa-language', 'title' => 'Light localisation on request', 'text' => 'Swiss-German spelling and terminology available on request where it matters for your campaign.'],
        ],
        'related_product_slugs' => [
            '500-deutsche-backlinks-backlinks-kaufen-seo-link',
            '80-german-backlinks-dofollow-by-top-level-domain',
        ],
        'faqs' => [
            ['question' => 'Are these German listings actually usable for Swiss or Austrian audiences?', 'answer' => 'Yes, generally — the language is shared. We offer light localisation on request for Swiss-specific spelling/terminology if that matters for your campaign.'],
        ],
    ],

];
