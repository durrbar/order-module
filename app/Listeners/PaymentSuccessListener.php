<?php

namespace Modules\Order\Listeners;

use Modules\Order\Services\OrderHistoryService;
use Modules\Payment\Events\PaymentSuccessEvent;

class PaymentSuccessListener
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
     * @param PaymentSuccess $event
     * @return void
     */
    public function handle(PaymentSuccessEvent $event): void
    {
        $this->orderHistoryService->addTimelineEvent($event->payment->order, 'Payment Successful');
        $this->orderHistoryService->updateTimestamp($event->payment->order, 'payment_time');
    }
}
