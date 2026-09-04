<x-app-layout :seo="$seo" active="dashboard">

<section class="section">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Account</p>
      <h2 class="section__title">Profile settings</h2>
    </header>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      @include('profile.partials.update-profile-information-form')
    </div>

    <div class="auth-card glass reveal" style="margin-bottom:24px;">
      @include('profile.partials.update-password-form')
    </div>

    <div class="auth-card glass reveal">
      @include('profile.partials.delete-user-form')
    </div>
  </div>
</section>

</x-app-layout>
