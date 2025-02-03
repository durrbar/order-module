<?php

namespace Modules\Order\Listeners;

use Modules\Invoice\Services\InvoiceService;
use Modules\Order\Events\OrderCreatedEvent;
use Modules\Order\Services\OrderHistoryService;
use Modules\Payment\Services\PaymentService;

class OrderCreatedListener
{
    protected InvoiceService $invoiceService;
    protected PaymentService $paymentService;
    protected OrderHistoryService $orderHistoryService;

    public function __construct(InvoiceService $invoiceService, PaymentService $paymentService, OrderHistoryService $orderHistoryService)
    {
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
        $this->orderHistoryService = $orderHistoryService;
    }

    public function handle(OrderCreatedEvent $event)
    {
        $order = $event->order;

        // Create a OrderHistory for the order
        $this->orderHistoryService->createInitialHistory($event->order);

        // Create the invoice for the order
        $this->invoiceService->createInvoice($order);

        // Create a Payment record for the Order
        $this->paymentService->createPayment($order);
    }
}
