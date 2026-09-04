<x-app-layout>

<section class="section">
  <div class="container container--narrow" style="display:flex; justify-content:center;">
    <div class="auth-wrap">
      <div class="auth-card glass reveal">
        <h1 class="auth-card__title">Verify your email</h1>
        <p class="auth-card__sub">Thanks for signing up! Before getting started, please verify your email by clicking the link we just emailed you. If you didn't receive it, we'll gladly send another.</p>

        @if (session('status') == 'verification-link-sent')
          <div class="auth-status">A new verification link has been sent to the email you provided during registration.</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom:12px;">
          @csrf
          <button type="submit" class="btn btn--primary btn--block ripple">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn--ghost btn--block">Log out</button>
        </form>
      </div>
    </div>
  </div>
</section>

</x-app-layout>
