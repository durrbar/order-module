<?php
namespace Modules\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Order\Models\Order;

class OrderCompletedEvent
{
    use Dispatchable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
