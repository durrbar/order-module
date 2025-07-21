<?php

namespace Modules\Order\Services;

use Modules\Order\Models\Order;
use Modules\Order\Models\OrderHistory;

class OrderHistoryService
{
    /**
     * Create initial order history when an order is created.
     */
    public function createInitialHistory(Order $order): OrderHistory
    {
        return $order->history()->create([
            'order_time' => now(),
            'timeline' => [
                [
                    'title' => 'Order has been created',
                    'time' => now(),
                ],
            ],
        ]);
    }

    /**
     * Update order history timeline with new events.
     *
     * @param  \DateTime|string|null  $time
     */
    public function addTimelineEvent(Order $order, string $title, $time = null): void
    {
        $history = $order->history;

        if (! $history) {
            throw new \Exception('Order history not found.');
        }

        $timeline = $history->timeline ?? [];
        $newEntry[] = [
            'title' => $title,
            'time' => $time ?? now(),
        ];

        $this->validateTimelineEntry($newEntry); // Validate the new entry
        $timeline[] = $newEntry;

        $history->update(['timeline' => $timeline]);
    }

    /**
     * Update specific timestamps in the order history.
     *
     * @param  \DateTime|string|null  $time
     */
    public function updateTimestamp(Order $order, string $field, $time = null): void
    {
        $history = $order->history;

        if (! $history) {
            throw new \Exception('Order history not found.');
        }

        if (! in_array($field, ['order_time', 'payment_time', 'delivery_time', 'completion_time'])) {
            throw new \Exception("Invalid timestamp field: $field");
        }

        $history->update([$field => $time ?? now()]);
    }

    private function validateTimelineEntry(array $entry): void
    {
        if (! isset($entry['title']) || ! isset($entry['time'])) {
            throw new \InvalidArgumentException('Invalid timeline entry: Missing "title" or "time".');
        }
    }
}
