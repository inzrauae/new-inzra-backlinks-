<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    @include('partials.admin.subnav', ['adminActive' => 'seo-services'])
    <p class="pdp__crumb reveal"><a href="{{ route('admin.seo-services.index') }}">SEO services</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i> {{ $service->name }}</p>

    <header class="section__head reveal">
      <h2 class="section__title">Edit {{ $service->name }}</h2>
      <p class="section__sub">Price changes only apply to new orders — existing orders keep the unit price they were placed at.</p>
    </header>

    <div class="auth-card glass reveal">
      <form method="POST" action="{{ route('admin.seo-services.update', $service) }}">
        @csrf
        @method('patch')
        @include('admin.seo-services._form', ['service' => $service])
        <button type="submit" class="btn btn--primary ripple">Save changes</button>
      </form>
    </div>
  </div>
</section>

</x-app-layout>
