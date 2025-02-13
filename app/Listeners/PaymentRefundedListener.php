<?php

namespace Modules\Order\Listeners;

use Modules\Order\Services\OrderService;
use Modules\Payment\Events\PaymentRefundedEvent;

class PaymentRefundedListener
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(PaymentRefundedEvent $event)
    {
        // Update the associated order status to "refunded"
        $this->orderService->updateOrderStatus($event->payment->order, 'refunded');
    }
}
