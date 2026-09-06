<?php

/*
|--------------------------------------------------------------------------
| SEO Publication / Backlink Order tool
|--------------------------------------------------------------------------
|
| Settings for the managed publication ordering tool at
| /seo-backlink-services. Service names and pricing live in the database
| (seo_services table, editable from /admin/seo-services) — this file only
| holds the handful of settings that aren't per-service.
|
*/

return [

    // Bumping this re-prompts every customer to accept terms again on their
    // next order; past orders keep the version they originally accepted.
    'terms_version' => '2026-09-06',

    // Flat percentage tax applied to every order's subtotal, e.g. 5 for 5%.
    // 0 disables tax entirely (no "Tax" line is shown at checkout).
    'tax_rate' => 0,

    // Quick-select chips shown next to the quantity input on the order form.
    'quantity_presets' => [10, 50, 100, 500, 1000, 5000],

];
