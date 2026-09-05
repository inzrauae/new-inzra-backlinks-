<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp ordering
    |--------------------------------------------------------------------------
    */
    'whatsapp_number' => '94778064714',

    /*
    |--------------------------------------------------------------------------
    | Featured products
    |--------------------------------------------------------------------------
    |
    | The 16 products originally hand-picked for the homepage "Popular
    | backlink placements" grid, in their original display order. Reused
    | as-is on market pages so they show the same general marketplace
    | catalog rather than a fabricated region-specific one.
    |
    */
    'featured_product_slugs' => [
        'premium-custom-website-design-development-modern',
        'premium-website-seo-backlink-power-pack',
        '500-web-2-0-backlinks-keyword-optimierte-backlinks',
        'premium-svenska-backlinks-500-hogkvalitativa-lankar',
        '500-sitemap-indexing-seo-backlinks',
        '500-yt-seo-backlniks-video-embedded-seo-backlink',
        'domain-website-submission-to-320-search-engines',
        'rank-10-keywords-on-google-s-first-page-with-inzra',
        '500-backlinks-mexicanos-web-2-0-seo-linkbuilding',
        '500-deutsche-backlinks-backlinks-kaufen-seo-link',
        'boost-your-website-ranking-with-1000-high-da-dofollow',
        '80-german-backlinks-dofollow-by-top-level-domain',
        '1000-mexico-spanish-seo-backlinks',
        'premium-package-100-usa-backlinks-high-domain',
        '100-permanent-uk-backlinks-with-high-pr-sites',
        'da-90-high-authority-backlinks-boost-your-seo-fast',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product detail page boilerplate
    |--------------------------------------------------------------------------
    |
    | Verified identical across all 40 legacy product pages, so it lives here
    | once instead of being duplicated per product row.
    |
    */
    'pdp' => [
        'format' => 'Digital service',
        'delivery' => 'Digital — instructions sent by email within 1 business day',
        'returns' => 'Money-back guarantee if your link drops within 12 months',
        'buyer_protection' => 'Secure checkout via PayPal — your payment details are never seen or stored by us',

        'faqs' => [
            [
                'question' => 'How is this delivered?',
                'answer' => 'Digital — instructions sent by email within 1 business day',
            ],
            [
                'question' => 'What if my link drops?',
                'answer' => 'Money-back guarantee if your link drops within 12 months',
            ],
            [
                'question' => 'Is my payment protected?',
                'answer' => 'Yes — checkout is handled entirely by PayPal, so your card or bank details are never seen or stored by us',
            ],
        ],

        'features' => [
            [
                'icon' => 'fa-solid fa-shield-halved',
                'title' => 'White hat first',
                'text' => "Editorial standards on content, disclosure where required, and no link schemes we can't defend.",
            ],
            [
                'icon' => 'fa-solid fa-tags',
                'title' => 'Honest pricing',
                'text' => 'Publisher cost and our fee shown separately. No markup that appears at checkout.',
            ],
            [
                'icon' => 'fa-solid fa-headset',
                'title' => 'Premium support',
                'text' => 'A named strategist on Slack or email, replying within 2 hours on business days.',
            ],
            [
                'icon' => 'fa-solid fa-rotate-left',
                'title' => 'Money-back guarantee',
                'text' => "Link removed or nofollowed within 12 months? We replace it or refund that placement.",
            ],
        ],
    ],
];
