<?php

namespace Modules\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Order\Models\Order;

class OrderCreatedEvent
{
    use Dispatchable;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
