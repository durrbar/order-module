<?php

namespace Modules\Order\Http\Controllers\Traits;

trait HandleOrderOperations
{
    private const CACHE_PUBLIC_ORDERS = 'api.v1.orders.public_';
    private const CACHE_ADMIN_ORDERS = 'api.v1.orders.admin_';
    private const CACHE_FEATURED_ORDERS = 'featured_orders';
    private const CACHE_LATEST_ORDERS = 'latest_orders';

    // Error messages
    private const ERROR_CREATE = 'Failed to create order';
    private const ERROR_UPDATE = 'Failed to update order';
    private const ERROR_DELETE = 'Failed to delete order';
    private const ERROR_FEATURED = 'Failed to retrieve featured orders';
    private const ERROR_LATEST = 'Failed to retrieve latest orders';
}
