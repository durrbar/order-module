<?php

namespace Modules\Order\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;

class OrderService
{

    // /**
    //  * Create a new order and initialize its history.
    //  *
    //  * @param array $orderData
    //  * @return Order
    //  * @throws Exception
    //  */
    // public function createOrder(array $orderData): Order
    // {

    //     try {
    //         $order = DB::transaction(function () use ($orderData) {
    //             // Create the order
    //             $order = Order::create([
    //                 'order_number' => $this->generateOrderNumber(),
    //                 'status' => 'pending', // Default status
    //                 'customer_id' => Auth::id(),
    //                 'total_amount' => $orderData['total_amount'],
    //                 'shipping_address' => $orderData['shipping_address'],

    //                 // Add other necessary fields
    //             ]);

    //             return $order;
    //         });

    //         return $order;
    //     } catch (Exception $e) {
    //         throw $e; // Re-throw the exception to be handled elsewhere
    //     }
    // }

    

    /**
     * Create a new order and initialize its history, invoice, payment, and delivery.
     *
     * @param array $checkoutData
     * @return Order
     * @throws Exception
     */
    public function createOrder(array $checkoutData): Order
    {
        try {
            // Start a database transaction to ensure atomicity
            $order = DB::transaction(function () use ($checkoutData) {
                // Extract relevant data from the checkout state
                $billingAddressId = $checkoutData['billingAddress']['id'] ?? null;
                $shippingAddressId = $checkoutData['shippingAddress']['id'] ?? null;
                $items = $checkoutData['items'];

                // Create the order
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending', // Default status
                    'customer_id' => Auth::id(),
                    'total_amount' => $checkoutData['total'],
                    'discount' => $checkoutData['discount'],
                    'shipping_charge' => $checkoutData['shippingCharge'],
                    // Add other necessary fields
                ]);

                // Attach billing address if provided
                if ($billingAddressId) {
                    $order->addresses()->attach($billingAddressId, ['type' => 'billing']);
                }

                // Attach shipping address if provided
                if ($shippingAddressId) {
                    $order->addresses()->attach($shippingAddressId, ['type' => 'shipping']);
                }

                // Create order items
                foreach ($items as $item) {
                    $order->items()->create([
                        'orderable_type' => 'product', // Assuming all items are products
                        'orderable_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'type' => 'physical', // Assuming all items are physical for now
                    ]);
                }

                return $order;
            });

            return $order;
        } catch (Exception $e) {
            // Rollback the transaction and re-throw the exception
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . strtoupper(bin2hex(random_bytes(4)));
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
    }

    /**
     * Mark order as completed and update order status.
     *
     * @param Order $order
     * @return void
     */
    public function markOrderCompleted(Order $order): void
    {
        // Mark the order as completed
        $this->updateOrderStatus($order, 'completed');
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
