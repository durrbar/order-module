<?php

namespace Modules\Order\Listeners;

use Modules\Delivery\Events\DeliveryCompletedEvent;
use Modules\Order\Services\OrderHistoryService;

class DeliveryCompletedListener
{
    protected OrderHistoryService $orderHistoryService;

    /**
     * Create the event listener.
     */
    public function __construct(OrderHistoryService $orderHistoryService)
    {
        $this->orderHistoryService = $orderHistoryService;
    }

    /**
     * Handle the event.
     *
     * @param DeliveryCompletedEvent $event
     * @return void
     */
    public function handle(DeliveryCompletedEvent $event): void
    {
        $this->orderHistoryService->addTimelineEvent($event->order, 'Order Delivered');
        $this->orderHistoryService->updateTimestamp($event->order, 'delivery_time');
        $this->orderHistoryService->updateTimestamp($event->order, 'completion_time');
    }
}
