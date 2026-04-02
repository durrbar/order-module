<?php

declare(strict_types=1);

namespace Modules\Order\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Delivery\Enums\DeliveryStatus;
use Modules\Invoice\Enums\InvoicePaymentStatus;
use Modules\Order\Enums\OrderLegacyStatus;
use Modules\Order\Models\Order;
use Modules\Order\Services\OrderService;
use Tests\TestCase;

final class OrderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService();
    }

    public function test_sync_statuses_sets_completed_when_invoice_paid_and_delivery_delivered(): void
    {
        /** @var Order|Mockery\MockInterface $order */
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('update')
            ->once()
            ->with(['status' => OrderLegacyStatus::Completed->value]);

        $this->orderService->syncStatuses(
            $order,
            InvoicePaymentStatus::Paid->value,
            DeliveryStatus::Delivered->value
        );
    }

    public function test_sync_statuses_sets_completed_when_invoice_paid_and_delivery_completed_legacy(): void
    {
        /** @var Order|Mockery\MockInterface $order */
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('update')
            ->once()
            ->with(['status' => OrderLegacyStatus::Completed->value]);

        $this->orderService->syncStatuses(
            $order,
            InvoicePaymentStatus::Paid->value,
            'completed'
        );
    }

    public function test_sync_statuses_sets_failed_when_invoice_failed(): void
    {
        /** @var Order|Mockery\MockInterface $order */
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('update')
            ->once()
            ->with(['status' => OrderLegacyStatus::Failed->value]);

        $this->orderService->syncStatuses(
            $order,
            InvoicePaymentStatus::Failed->value,
            DeliveryStatus::Delivered->value
        );
    }

    public function test_sync_statuses_sets_failed_when_delivery_failed(): void
    {
        /** @var Order|Mockery\MockInterface $order */
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('update')
            ->once()
            ->with(['status' => OrderLegacyStatus::Failed->value]);

        $this->orderService->syncStatuses(
            $order,
            InvoicePaymentStatus::Paid->value,
            DeliveryStatus::Failed->value
        );
    }
}
