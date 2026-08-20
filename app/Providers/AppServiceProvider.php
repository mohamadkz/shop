<?php

namespace App\Providers;

use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use App\Domain\Payment\Services\Gateways\ZarinpalGateway;
use App\Domain\Payment\Services\Gateways\FakePaymentGateway;
use App\Domain\Customer\Services\Contracts\SmsProviderContract;
use App\Domain\Customer\Services\Providers\KavenegarProvider;
use App\Domain\Customer\Services\Providers\MockSmsProvider;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Sms\SmsProviderInterface;
// use App\Sms\Providers\MockSmsProvider;
use App\Domain\Cart\Models\BasketItem;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
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
            SmsProviderContract::class,
            MockSmsProvider::class
        );

        $this->app->singleton(
            PaymentGatewayContract::class,
            FakePaymentGateway::class
        );

        // $this->app->singleton(
        //     PaymentGatewayContract::class,
        //     ZarinpalGateway::class
        // );

        // $this->app->singleton(
        //     SmsProviderContract::class,
        //     KavenegarProvider::class
        // );
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
