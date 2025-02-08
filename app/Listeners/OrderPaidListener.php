<?php

namespace Modules\Order\Listeners;

use Modules\Delivery\Services\DeliveryService;
use Modules\Invoice\Services\InvoiceService;
use Modules\Order\Events\OrderPaidEvent;

class OrderPaidListener
{
    protected InvoiceService $invoiceService;
    protected DeliveryService $deliveryService;

    public function __construct(InvoiceService $invoiceService, DeliveryService $deliveryService)
    {
        $this->invoiceService = $invoiceService;
        $this->deliveryService = $deliveryService;
    }

    public function handle(OrderPaidEvent $event)
    {
        $order = $event->order;

        // Sync the invoice status
        $this->invoiceService->updateInvoiceStatus($order->invoice, 'paid');

        // Schedule delivery
        $this->deliveryService->scheduleDelivery($order);
    }
}
