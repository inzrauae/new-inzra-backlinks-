@if (config('services.google.client_id'))
  <div class="auth-divider">or</div>
  <a href="{{ route('auth.google') }}" class="btn btn--google btn--block ripple">
    <i class="fa-brands fa-google" aria-hidden="true"></i> Continue with Google
  </a>
@endif
