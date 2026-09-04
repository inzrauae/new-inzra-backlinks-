<header style="margin-bottom:20px;">
  <h2 class="auth-card__title" style="font-size:1.3rem;">Profile information</h2>
  <p class="auth-card__sub" style="margin-bottom:0;">Update your account's name, phone and email address.</p>
</header>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
  @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
  @csrf
  @method('patch')

  <div class="auth-group">
    <label for="name" class="auth-label">Name</label>
    <input id="name" class="auth-input @error('name') has-error @enderror" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
    @error('name')
      <p class="auth-error">{{ $message }}</p>
    @enderror
  </div>

  <div class="auth-group">
    <label for="phone" class="auth-label">Phone (WhatsApp number)</label>
    <input id="phone" class="auth-input @error('phone') has-error @enderror" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
    @error('phone')
      <p class="auth-error">{{ $message }}</p>
    @enderror
  </div>

  <div class="auth-group">
    <label for="email" class="auth-label">Email</label>
    <input id="email" class="auth-input @error('email') has-error @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
    @error('email')
      <p class="auth-error">{{ $message }}</p>
    @enderror

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
      <p style="font-size:.85rem; color:var(--text-2); margin-top:8px;">
        Your email address is unverified.
        <button form="send-verification" class="auth-link" style="background:none; border:0; cursor:pointer;">Click here to re-send the verification email.</button>
      </p>

      @if (session('status') === 'verification-link-sent')
        <p style="font-size:.85rem; color:#15803D; font-weight:600; margin-top:8px;">A new verification link has been sent to your email address.</p>
      @endif
    @endif
  </div>

  <div style="display:flex; align-items:center; gap:16px;">
    <button type="submit" class="btn btn--primary ripple">Save</button>
    @if (session('status') === 'profile-updated')
      <span style="font-size:.85rem; color:var(--text-2);">Saved.</span>
    @endif
  </div>
</form>
