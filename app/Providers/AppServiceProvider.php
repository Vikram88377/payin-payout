<?php

namespace App\Providers;
 
use App\Models\Payin;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        //


            Relation::enforceMorphMap([
            'payin' => Payin::class,
            'payout' => Payout::class,
        ]);
    }
}
