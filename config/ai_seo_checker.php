<?php

/*
|--------------------------------------------------------------------------
| Free AI SEO / GEO Checker
|--------------------------------------------------------------------------
|
| Drives the /ai-seo-checker landing page: the free score is a real,
| deterministic on-page audit (structured data, crawler access, content
| clarity, freshness/authority signals) weighted differently per engine
| to reflect what's publicly known about how each one sources answers —
| it is not a live query against any of these products.
|
*/

return [

    'price' => (float) env('AI_SEO_REPORT_PRICE', 30.00),
    'currency' => 'USD',

    'engines' => [

        'chatgpt' => [
            'name' => 'ChatGPT (OpenAI)',
            'icon' => 'fa-solid fa-comment-dots',
            'bots' => ['gptbot', 'oai-searchbot', 'chatgpt-user'],
            'weights' => [
                'crawler_access' => 0.30,
                'structured_data' => 0.20,
                'answer_readiness' => 0.25,
                'technical_clarity' => 0.15,
                'authority_freshness' => 0.10,
            ],
        ],

        'google_ai' => [
            'name' => 'Google AI Overviews (Gemini)',
            'icon' => 'fa-brands fa-google',
            'bots' => ['google-extended', 'googlebot'],
            'weights' => [
                'crawler_access' => 0.25,
                'structured_data' => 0.30,
                'answer_readiness' => 0.10,
                'technical_clarity' => 0.20,
                'authority_freshness' => 0.15,
            ],
        ],

        'perplexity' => [
            'name' => 'Perplexity',
            'icon' => 'fa-solid fa-magnifying-glass',
            'bots' => ['perplexitybot', 'perplexity-user'],
            'weights' => [
                'crawler_access' => 0.25,
                'structured_data' => 0.15,
                'answer_readiness' => 0.20,
                'technical_clarity' => 0.10,
                'authority_freshness' => 0.30,
            ],
        ],

        'claude' => [
            'name' => 'Claude (Anthropic)',
            'icon' => 'fa-solid fa-atom',
            'bots' => ['claudebot', 'anthropic-ai', 'claude-user'],
            'weights' => [
                'crawler_access' => 0.25,
                'structured_data' => 0.15,
                'answer_readiness' => 0.30,
                'technical_clarity' => 0.20,
                'authority_freshness' => 0.10,
            ],
        ],

        'copilot' => [
            'name' => 'Microsoft Copilot (Bing)',
            'icon' => 'fa-brands fa-microsoft',
            'bots' => ['bingbot'],
            'weights' => [
                'crawler_access' => 0.25,
                'structured_data' => 0.25,
                'answer_readiness' => 0.10,
                'technical_clarity' => 0.25,
                'authority_freshness' => 0.15,
            ],
        ],

    ],

];
