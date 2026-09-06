<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\SeoOrder;
use App\Policies\OrderPolicy;
use App\Policies\SeoOrderPolicy;
use Illuminate\Support\Facades\Gate;
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
    }
}
