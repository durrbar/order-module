<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderCompletedEvent;
use Modules\Order\Services\OrderHistoryService;

class OrderCompletedListener
{
    public function __construct(private OrderHistoryService $orderHistoryService) {}

    public function handle(OrderCompletedEvent $event): void
    {
        $this->orderHistoryService->updateTimestamp($event->order, 'completion_time');
    }
}
