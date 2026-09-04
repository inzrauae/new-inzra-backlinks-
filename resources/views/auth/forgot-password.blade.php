<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Forgot your password?</h1>
        <p class="auth-card__sub">Enter your email and we'll send you a link to reset it.</p>

        @if (session('status'))
          <div class="auth-status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
          @csrf

          <div class="auth-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email" class="auth-input @error('email') has-error @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <button type="submit" class="btn btn--primary btn--block ripple">Email password reset link</button>
        </form>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
