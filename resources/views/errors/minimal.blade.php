<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} | INZRA</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<style>
  body { display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; padding:24px; }
  .err { max-width:480px; }
  .err__code { font-family:var(--font-display); font-size:4rem; font-weight:700; color:var(--blue); line-height:1; margin-bottom:12px; }
  .err__title { font-family:var(--font-display); font-size:1.5rem; font-weight:600; margin-bottom:12px; }
  .err__sub { color:var(--text-2); margin-bottom:28px; }
</style>
</head>
<body>
  <div class="err">
    <p class="err__code">{{ $code }}</p>
    <h1 class="err__title">{{ $title }}</h1>
    <p class="err__sub">{{ $message }}</p>
    <a href="{{ url('/') }}" class="btn btn--primary ripple">Back to homepage</a>
  </div>
</body>
</html>
