<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    @include('partials.admin.subnav', ['adminActive' => 'seo-services'])
    <p class="pdp__crumb reveal"><a href="{{ route('admin.seo-services.index') }}">SEO services</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> New service</p>

    <header class="section__head reveal">
      <h2 class="section__title">New SEO service</h2>
    </header>

    <div class="auth-card glass reveal">
      <form method="POST" action="{{ route('admin.seo-services.store') }}">
        @csrf
        @include('admin.seo-services._form', ['service' => null])
        <button type="submit" class="btn btn--primary ripple">Create service</button>
      </form>
    </div>
  </div>
</section>

</x-app-layout>
