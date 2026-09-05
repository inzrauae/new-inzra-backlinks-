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
        'name' => 'Free Image Converter',
        'icon' => 'fa-solid fa-file-image',
        'tagline' => 'Convert JPG, PNG, WebP & AVIF — free',
        'description' => 'Free tool to convert images between formats entirely in your browser. No sign-up, no uploads, no server processing.',
        'route' => 'tools.image-converter',
    ],

    'pdf-editor' => [
        'name' => 'Free PDF Editor',
        'icon' => 'fa-solid fa-file-pdf',
        'tagline' => 'Edit, sign, merge & organize PDFs — free',
        'description' => 'Edit text, add images, sign, merge, split and organize PDFs entirely in your browser. No sign-up, no uploads.',
        'route' => 'tools.pdf-editor',
    ],

];
