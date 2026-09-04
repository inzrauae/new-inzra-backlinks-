<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Reset your password</h1>

        <form method="POST" action="{{ route('password.store') }}">
          @csrf

          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <div class="auth-group">
            <label for="email" class="auth-label">Email</label>
            <input id="email" class="auth-input @error('email') has-error @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-group">
            <label for="password" class="auth-label">New password</label>
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

          <button type="submit" class="btn btn--primary btn--block ripple">Reset password</button>
        </form>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
