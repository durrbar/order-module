<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Enums\OrderLegacyStatus;
use Modules\Order\Services\OrderService;
use Modules\Payment\Events\PaymentRefundedEvent;

class PaymentRefundedListener
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(PaymentRefundedEvent $event)
    {
        // Update the associated order status to "refunded"
        $this->orderService->updateOrderStatus($event->payment->order, OrderLegacyStatus::Refunded->value);
    }
}
