<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderCompletedEvent;
use Modules\Order\Services\OrderHistoryService;

class OrderCompletedListener
{
    private OrderHistoryService $orderHistoryService;

    public function __construct(OrderHistoryService $orderHistoryService)
    {
        $this->orderHistoryService = $orderHistoryService;
    }

    public function handle(OrderCompletedEvent $event)
    {
        $this->orderHistoryService->updateTimestamp($event->order, 'completion_time');
    }
}
