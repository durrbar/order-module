<?php

namespace Modules\Order\Listeners;

use Modules\Order\Services\OrderHistoryService;
use Modules\Order\Services\OrderService;
use Modules\Payment\Events\PaymentSuccessEvent;

class PaymentSuccessListener
{
    protected OrderService $orderService;

    protected OrderHistoryService $orderHistoryService;

    public function __construct(OrderService $orderService, OrderHistoryService $orderHistoryService)
    {
        $this->orderService = $orderService;
        $this->orderHistoryService = $orderHistoryService;
    }

    public function handle(PaymentSuccessEvent $event)
    {
        $this->orderHistoryService->updateTimestamp($event->payment->order, 'payment_time');

        // Update the associated order status to "processing"
        $this->orderService->updateOrderStatus($event->payment->order, 'processing');
    }
}
