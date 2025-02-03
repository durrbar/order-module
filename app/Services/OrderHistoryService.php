<?php

namespace Modules\Order\Services;

use Modules\Order\Models\Order;
use Modules\Order\Models\OrderHistory;

class OrderHistoryService
{
    /**
     * Create initial order history when an order is created.
     *
     * @param Order $order
     * @return OrderHistory
     */
    public function createInitialHistory(Order $order): OrderHistory
    {
        return $order->history()->create([
            'order_time' => now(),
            'timeline' => [
                ['title' => 'Order Created', 'time' => now()],
            ],
        ]);
    }

    /**
     * Update order history timeline with new events.
     *
     * @param Order $order
     * @param string $title
     * @param \DateTime|string|null $time
     * @return void
     */
    public function addTimelineEvent(Order $order, string $title, $time = null): void
    {
        $history = $order->history;

        if (!$history) {
            throw new \Exception('Order history not found.');
        }

        $timeline = $history->timeline ?? [];
        $timeline[] = [
            'title' => $title,
            'time' => $time ?? now(),
        ];

        $history->update(['timeline' => $timeline]);
    }

    /**
     * Update specific timestamps in the order history.
     *
     * @param Order $order
     * @param string $field
     * @param \DateTime|string|null $time
     * @return void
     */
    public function updateTimestamp(Order $order, string $field, $time = null): void
    {
        $history = $order->history;

        if (!$history) {
            throw new \Exception('Order history not found.');
        }

        if (!in_array($field, ['order_time', 'payment_time', 'delivery_time', 'completion_time'])) {
            throw new \Exception("Invalid timestamp field: $field");
        }

        $history->update([$field => $time ?? now()]);
    }
}
