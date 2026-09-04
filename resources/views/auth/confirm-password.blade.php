<x-app-layout>

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Confirm your password</h1>
        <p class="auth-card__sub">This is a secure area. Please confirm your password before continuing.</p>

        <form method="POST" action="{{ route('password.confirm') }}">
          @csrf

          <div class="auth-group">
            <label for="password" class="auth-label">Password</label>
            <input id="password" class="auth-input @error('password') has-error @enderror" type="password" name="password" required autofocus autocomplete="current-password">
            @error('password')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <button type="submit" class="btn btn--primary btn--block ripple">Confirm</button>
        </form>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
