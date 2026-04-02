<?php

declare(strict_types=1);

namespace Modules\Order\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Order\Models\Order;

class OrderCancelled implements ShouldQueue
{
    /**
     * @var Order
     */
    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
