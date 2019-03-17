<?php

namespace App\Providers;

use App\Lib\CurrencyApi;
use App\Lib\FreeCurrencyApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CurrencyApi::class, function ($app) {
            return new FreeCurrencyApi(config('services.currency_api.key'), config('services.currency_api.url'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
