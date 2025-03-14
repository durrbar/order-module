<?php

namespace Modules\Order\Observers;

use Modules\Order\Events\OrderCreatedEvent;
use Modules\Order\Events\OrderCompletedEvent;
use Modules\Order\Events\OrderPaidEvent;
use Modules\Order\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Dispatch an event to notify other modules
        event(new OrderCreatedEvent($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            if ($order->status === 'paid') {
                event(new OrderPaidEvent($order));
            } elseif ($order->status === 'completed') {
                event(new OrderCompletedEvent($order));
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
