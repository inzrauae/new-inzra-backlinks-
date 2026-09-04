<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Create your account</h1>
        <p class="auth-card__sub">Track your orders and check out faster next time.</p>

        <form method="POST" action="{{ route('register') }}">
          @csrf

          <div class="auth-group">
            <label for="name" class="auth-label">Name</label>
            <input id="name" class="auth-input @error('name') has-error @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email" class="auth-input @error('email') has-error @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="phone" class="auth-label">Phone number</label>
            <input id="phone" class="auth-input @error('phone') has-error @enderror" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+1 555 123 4567">
            @error('phone')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="password" class="auth-label">Password</label>
            <input id="password" class="auth-input @error('password') has-error @enderror" type="password" name="password" required autocomplete="new-password">
            @error('password')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="password_confirmation" class="auth-label">Confirm password</label>
            <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <button type="submit" class="btn btn--primary btn--block ripple">Create account</button>
        </form>

        @include('partials.auth.google-button')

        <p class="auth-foot">Already have an account? <a class="auth-link" href="{{ route('login') }}">Log in</a></p>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
