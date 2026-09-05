@php($active ??= null)
<header class="nav" id="nav">
  <div class="container nav__inner">

    <a class="brand" href="{{ url('/') }}" aria-label="INZRA home">
      <span class="brand__text">
        <span class="brand__name">INZRA</span>
        <span class="brand__tagline">Powered by Applantics (PVT) LTD</span>
      </span>
    </a>

    <nav class="nav__links" id="navLinks" aria-label="Primary">
      <a href="{{ url('/') }}" class="nav__link {{ $active === 'home' ? 'is-active' : '' }}">Home</a>
      <a href="{{ route('marketplace') }}" class="nav__link {{ $active === 'marketplace' ? 'is-active' : '' }}">Marketplace</a>
      <a href="{{ route('categories') }}" class="nav__link {{ $active === 'categories' ? 'is-active' : '' }}">Categories</a>
      <a href="{{ route('pricing') }}" class="nav__link {{ $active === 'pricing' ? 'is-active' : '' }}">Pricing</a>
      <a href="{{ route('blog.index') }}" class="nav__link {{ $active === 'blog' ? 'is-active' : '' }}">Blog</a>
      <a href="{{ route('tools.index') }}" class="nav__link {{ $active === 'tools' ? 'is-active' : '' }}">Tools</a>
      <a href="{{ route('contact') }}" class="nav__link {{ $active === 'contact' ? 'is-active' : '' }}">Contact</a>
      <div class="nav__mobile-actions">
        @guest
          <a href="{{ route('login') }}" class="btn btn--ghost btn--block">Log in</a>
          <a href="{{ route('register') }}" class="btn btn--primary btn--block">Create account</a>
        @else
          @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost btn--block">Admin</a>
          @endif
          <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--block">Dashboard</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn--primary btn--block">Log out</button>
          </form>
        @endguest
      </div>
    </nav>

    <div class="nav__actions">
      @guest
        <a href="{{ route('login') }}" class="btn btn--ghost nav__login">Log in</a>
        <a href="{{ route('register') }}" class="btn btn--primary">Create account</a>
      @else
        @if (Auth::user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost nav__login">Admin</a>
        @endif
        <a href="{{ route('dashboard') }}" class="btn btn--ghost nav__login">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn--primary">Log out</button>
        </form>
      @endguest
      <button class="icon-btn nav__burger" id="burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="navLinks">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</header>
