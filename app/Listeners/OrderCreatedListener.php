<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderCreatedEvent;
use Modules\Order\Services\OrderHistoryService;

class OrderCreatedListener
{
    public function __construct(private readonly OrderHistoryService $orderHistoryService) {}

    public function handle(OrderCreatedEvent $event): void
    {
        $order = $event->order;

        $this->orderHistoryService->createInitialHistory($event->order);

        if (! request()->isJson() && ! request()->wantsJson()) {
            app()->instance('web_created_order_'.$order->id, true);
        }
    }
}
