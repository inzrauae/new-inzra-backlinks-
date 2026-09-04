<header style="margin-bottom:20px;">
  <h2 class="auth-card__title" style="font-size:1.3rem;">Delete account</h2>
  <p class="auth-card__sub" style="margin-bottom:0;">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.</p>
</header>

<details>
  <summary class="btn btn--ghost" style="display:inline-flex; cursor:pointer; color:#DC2626; border-color:rgba(220,38,38,.35);">Delete account</summary>

  <form method="post" action="{{ route('profile.destroy') }}" style="margin-top:16px;">
    @csrf
    @method('delete')

    <div class="auth-group">
      <label for="password" class="auth-label">Password</label>
      <input id="password" class="auth-input @error('password', 'userDeletion') has-error @enderror" type="password" name="password" placeholder="Confirm your password">
      @error('password', 'userDeletion')
        <p class="auth-error">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn btn--primary ripple" style="background:#DC2626;">Permanently delete account</button>
  </form>
</details>
