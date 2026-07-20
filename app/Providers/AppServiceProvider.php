<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Sms\SmsProviderInterface;
use App\Sms\Providers\MockSmsProvider;
use App\Models\Basket;
use App\Observers\BasketObserver;


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
        Basket::observe(BasketObserver::class);
    }
}
