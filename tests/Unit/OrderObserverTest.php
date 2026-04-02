<?php

declare(strict_types=1);

namespace Modules\Order\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Modules\Order\Enums\OrderLegacyStatus;
use Modules\Order\Events\OrderCompletedEvent;
use Modules\Order\Events\OrderPaidEvent;
use Modules\Order\Models\Order;
use Modules\Order\Observers\OrderObserver;
use Tests\TestCase;

class OrderObserverTest extends TestCase
{
    public function test_updated_dispatches_paid_event_for_paid_status(): void
    {
        Event::fake();

        $order = new Order();
        $order->status = OrderLegacyStatus::Paid->value;

        $observer = new OrderObserver();
        $observer->updated($order);

        Event::assertDispatched(OrderPaidEvent::class);
        Event::assertNotDispatched(OrderCompletedEvent::class);
    }

    public function test_updated_dispatches_completed_event_for_completed_status(): void
    {
        Event::fake();

        $order = new Order();
        $order->status = OrderLegacyStatus::Completed->value;

        $observer = new OrderObserver();
        $observer->updated($order);

        Event::assertDispatched(OrderCompletedEvent::class);
        Event::assertNotDispatched(OrderPaidEvent::class);
    }
}
