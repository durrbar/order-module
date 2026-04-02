<?php

declare(strict_types=1);

namespace Modules\Order\Enums;

enum OrderLegacyStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Canceled = 'canceled';
    case Refunded = 'refunded';
}
