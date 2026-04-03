<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Order\Enums\OrderLegacyStatus;
use Modules\Order\Services\OrderHistoryService;
use Modules\Order\Services\OrderService;
use Modules\Payment\Events\PaymentSuccessEvent;

class PaymentSuccessListener
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderHistoryService $orderHistoryService
    ) {}

    public function handle(PaymentSuccessEvent $event): void
    {
        $this->orderHistoryService->updateTimestamp($event->payment->order, 'payment_time');

        // Update the associated order status to "processing"
        $this->orderService->updateOrderStatus($event->payment->order, OrderLegacyStatus::Processing->value);
    }
}
