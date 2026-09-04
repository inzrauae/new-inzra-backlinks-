<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
@if ($seo->keywords)
<meta name="keywords" content="{{ $seo->keywords }}">
@endif
<meta name="author" content="INZRA">
<meta name="robots" content="{{ $seo->robots }}">
<link rel="canonical" href="{{ $seo->canonical }}">

<!-- Open Graph -->
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:image" content="{{ $seo->ogImage ?? asset('og-cover.svg') }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:site_name" content="INZRA">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">

<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

@foreach ($seo->jsonLd as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

@stack('head')
</head>
<body>

@include('partials.loader')

@include('partials.nav', ['active' => $active ?? null])

<main id="main">
{{ $slot }}
</main>

@include('partials.footer')

<script src="{{ asset('js/script.js') }}"></script>
@stack('scripts')
</body>
</html>
