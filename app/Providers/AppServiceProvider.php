<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contract;
use App\Observers\ContractObserver;

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
        Contract::observe(ContractObserver::class);
    }
}
