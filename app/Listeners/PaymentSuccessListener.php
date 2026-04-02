<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Enums\OrderLegacyStatus;
use Modules\Order\Services\OrderHistoryService;
use Modules\Order\Services\OrderService;
use Modules\Payment\Events\PaymentSuccessEvent;

final class PaymentSuccessListener
{
    private OrderService $orderService;

    private OrderHistoryService $orderHistoryService;

    public function __construct(OrderService $orderService, OrderHistoryService $orderHistoryService)
    {
        $this->orderService = $orderService;
        $this->orderHistoryService = $orderHistoryService;
    }

    public function handle(PaymentSuccessEvent $event)
    {
        $this->orderHistoryService->updateTimestamp($event->payment->order, 'payment_time');

        // Update the associated order status to "processing"
        $this->orderService->updateOrderStatus($event->payment->order, OrderLegacyStatus::Processing->value);
    }
}
