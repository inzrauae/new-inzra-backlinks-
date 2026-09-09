<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\SeoOrder;
use App\Policies\OrderPolicy;
use App\Policies\SeoOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(SeoOrder::class, SeoOrderPolicy::class);

        // Without this, url()/route() (and therefore every canonical tag)
        // mirror whatever host/scheme the request came in on — so
        // www.inzra.com and http://inzra.com each generate a
        // self-referencing canonical instead of pointing at the one real
        // domain, which tells search engines these are separate pages.
        if ($this->app->environment('production')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }
    }
}
