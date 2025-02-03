<?php

namespace Modules\Order\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Delivery\Services\DeliveryService;
use Modules\Invoice\Services\InvoiceService;
use Modules\Payment\Services\PaymentService;
use Modules\Order\Models\Order;

class OrderService
{
    protected OrderHistoryService $orderHistoryService;
    protected $invoiceService;
    protected $paymentService;
    protected $deliveryService;

    public function __construct(OrderHistoryService $orderHistoryService, InvoiceService $invoiceService, PaymentService $paymentService, DeliveryService $deliveryService)
    {
        $this->orderHistoryService = $orderHistoryService;
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Create a new order and initialize its history.
     *
     * @param array $orderData
     * @return Order
     * @throws Exception
     */
    public function createOrder(array $orderData): Order
    {

        try {
            $order = DB::transaction(function () use ($orderData) {
                // Create the order
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending', // Default status
                    'customer_id' => Auth::id(),
                    'total_amount' => $orderData['total_amount'],
                    'shipping_address' => $orderData['shipping_address'],

                    // Add other necessary fields
                ]);

                // Create the initial order history
                $this->orderHistoryService->createInitialHistory($order);

                // Create the associated invoice
                $invoice = $this->invoiceService->createInvoice($order);

                // Create a payment (mark as pending initially)
                $this->paymentService->createPayment($order, 'pending');

                return $order;
            });

            return $order;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception to be handled elsewhere
        }
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    /**
     * Update the order status after a successful payment.
     *
     * @param Order $order
     * @param string $status
     * @return void
     */
    public function updateOrderStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);

        // If the order is marked as paid, trigger payment and delivery logic
        if ($status === 'paid') {
            // Sync the invoice status
            $this->invoiceService->updateInvoiceStatus($order->invoice, 'paid');

            // Schedule delivery
            $this->deliveryService->scheduleDelivery($order);
        }
    }

    /**
     * Sync the order status with the invoice and delivery statuses.
     *
     * @param Order $order
     * @param string $invoiceStatus
     * @param string $deliveryStatus
     * @return void
     */
    public function syncStatuses(Order $order, string $invoiceStatus, string $deliveryStatus): void
    {
        // Sync the order status with the invoice and delivery statuses
        if ($invoiceStatus === 'paid' && $deliveryStatus === 'completed') {
            $order->update(['status' => 'completed']);
        } elseif ($invoiceStatus === 'failed' || $deliveryStatus === 'failed') {
            $order->update(['status' => 'failed']);
        }
    }
}
