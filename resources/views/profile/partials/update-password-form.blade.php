<header style="margin-bottom:20px;">
  <h2 class="auth-card__title" style="font-size:1.3rem;">Update password</h2>
  <p class="auth-card__sub" style="margin-bottom:0;">Ensure your account is using a long, random password to stay secure.</p>
</header>

<form method="post" action="{{ route('password.update') }}">
  @csrf
  @method('put')

  <div class="auth-group">
    <label for="update_password_current_password" class="auth-label">Current password</label>
    <input id="update_password_current_password" class="auth-input @error('current_password', 'updatePassword') has-error @enderror" type="password" name="current_password" autocomplete="current-password">
    @error('current_password', 'updatePassword')
      <p class="auth-error">{{ $message }}</p>
    @enderror
  </div>

  <div class="auth-group">
    <label for="update_password_password" class="auth-label">New password</label>
    <input id="update_password_password" class="auth-input @error('password', 'updatePassword') has-error @enderror" type="password" name="password" autocomplete="new-password">
    @error('password', 'updatePassword')
      <p class="auth-error">{{ $message }}</p>
    @enderror
  </div>

  <div class="auth-group">
    <label for="update_password_password_confirmation" class="auth-label">Confirm password</label>
    <input id="update_password_password_confirmation" class="auth-input" type="password" name="password_confirmation" autocomplete="new-password">
    @error('password_confirmation', 'updatePassword')
      <p class="auth-error">{{ $message }}</p>
    @enderror
  </div>

  <div style="display:flex; align-items:center; gap:16px;">
    <button type="submit" class="btn btn--primary ripple">Save</button>
    @if (session('status') === 'password-updated')
      <span style="font-size:.85rem; color:var(--text-2);">Saved.</span>
    @endif
  </div>
</form>
