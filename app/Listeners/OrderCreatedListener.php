<?php

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderCreatedEvent;
use Modules\Order\Services\OrderHistoryService;

class OrderCreatedListener
{
    protected OrderHistoryService $orderHistoryService;

    public function __construct(OrderHistoryService $orderHistoryService)
    {
        $this->orderHistoryService = $orderHistoryService;
    }

    public function handle(OrderCreatedEvent $event)
    {
        $order = $event->order;

        // Create a OrderHistory for the order
        $this->orderHistoryService->createInitialHistory($event->order);

        // If the order was created via a web request, set a flag
        if (! request()->isJson() && ! request()->wantsJson()) {
            app()->instance('web_created_order_'.$order->id, true);
        }
    }
}
