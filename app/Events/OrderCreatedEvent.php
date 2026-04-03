<?php

declare(strict_types=1);

namespace Modules\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Order\Models\Order;

class OrderCreatedEvent
{
    use Dispatchable;

    public function __construct(public readonly Order $order) {}
}
