<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Delivery\Events\DeliveryCompletedEvent;
use Modules\Order\Services\OrderHistoryService;
use Modules\Order\Services\OrderService;

class DeliveryCompletedListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderHistoryService $orderHistoryService
    ) {}

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
