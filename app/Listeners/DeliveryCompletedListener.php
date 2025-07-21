<?php

namespace Modules\Order\Listeners;

use Modules\Delivery\Events\DeliveryCompletedEvent;
use Modules\Order\Services\OrderHistoryService;
use Modules\Order\Services\OrderService;

class DeliveryCompletedListener
{
    protected OrderService $orderService;

    protected OrderHistoryService $orderHistoryService;

    /**
     * Create the event listener.
     */
    public function __construct(OrderService $orderService, OrderHistoryService $orderHistoryService)
    {
        $this->orderHistoryService = $orderHistoryService;
        $this->orderService = $orderService;
    }

    /**
     * Handle the event.
     */
    public function handle(DeliveryCompletedEvent $event): void
    {
        $this->orderHistoryService->addTimelineEvent($event->delivery->order, 'Order Delivered');
        $this->orderHistoryService->updateTimestamp($event->delivery->order, 'delivery_time');

        // Update the associated order status to "completed"
        $this->orderService->markOrderCompleted($event->delivery->order);
    }
}
