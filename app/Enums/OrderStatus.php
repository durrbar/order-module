<?php

declare(strict_types=1);

namespace Modules\Order\Enums;

enum OrderStatus: string
{
    case Pending = 'order-pending';
    case Processing = 'order-processing';
    case Completed = 'order-completed';
    case Cancelled = 'order-cancelled';
    case Refunded = 'order-refunded';
    case Failed = 'order-failed';
    case AtLocalFacility = 'order-at-local-facility';
    case OutForDelivery = 'order-out-for-delivery';
}
