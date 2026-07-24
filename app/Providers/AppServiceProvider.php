<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Sms\SmsProviderInterface;
use App\Sms\Providers\MockSmsProvider;
use App\Models\BasketItem;
use App\Models\Item;
use App\Models\User;
use App\Observers\BasketItemObserver;
use App\Policies\ItemPolicy;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request; 



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SmsProviderInterface::class,
            MockSmsProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::define('admin-only', fn(User $user) => $user->role === 'admin');

        BasketItem::observe(BasketItemObserver::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
