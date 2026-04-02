<?php

declare(strict_types=1);

namespace Modules\Order\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Order\Models\Order;

class OrderStatusChanged implements ShouldQueue
{
    public Order $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
