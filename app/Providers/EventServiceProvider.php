<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Delivery\Events\DeliveryCompletedEvent;
use Modules\Order\Listeners\DeliveryCompletedListener;
use Modules\Order\Listeners\PaymentSuccessListener;
use Modules\Payment\Events\PaymentSuccessEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        PaymentSuccessEvent::class => [
            PaymentSuccessListener::class,
        ],
        DeliveryCompletedEvent::class => [
            DeliveryCompletedListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}
