<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Log in to INZRA</h1>
        <p class="auth-card__sub">Access your orders, dashboard and account details.</p>

        @if (session('status'))
          <div class="auth-status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div class="auth-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email" class="auth-input @error('email') has-error @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="password" class="auth-label">Password</label>
            <input id="password" class="auth-input @error('password') has-error @enderror" type="password" name="password" required autocomplete="current-password">
            @error('password')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-row">
            <label class="auth-check">
              <input type="checkbox" name="remember">
              Remember me
            </label>

            @if (Route::has('password.request'))
              <a class="auth-link" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
          </div>

          <button type="submit" class="btn btn--primary btn--block ripple">Log in</button>
        </form>

        @include('partials.auth.google-button')

        <p class="auth-foot">Don't have an account? <a class="auth-link" href="{{ route('register') }}">Create one</a></p>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
