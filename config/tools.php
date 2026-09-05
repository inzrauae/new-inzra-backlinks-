<?php

/*
|--------------------------------------------------------------------------
| Free tools
|--------------------------------------------------------------------------
|
| Each entry drives a card on the /tools hub page. Add new tools here as
| they're built (e.g. image-compressor, jpg-to-png) — the hub and nav
| don't need any other changes.
|
*/

return [

    'image-converter' => [
        'name' => 'Image Converter',
        'icon' => 'fa-solid fa-file-image',
        'tagline' => 'Convert JPG, PNG, WebP & AVIF',
        'description' => 'Convert images between formats entirely in your browser. No uploads, no server processing, completely private.',
        'route' => 'tools.image-converter',
    ],

];
