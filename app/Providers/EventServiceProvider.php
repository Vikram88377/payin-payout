<?php

namespace App\Providers;

use App\Events\PaymentStatusChanged;
use App\Listeners\LogPaymentStatusChange;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentStatusChanged::class => [
            LogPaymentStatusChange::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}