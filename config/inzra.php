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
