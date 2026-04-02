<?php

declare(strict_types=1);

use Modules\Delivery\Models\Delivery;
use Modules\Invoice\Models\Invoice;
use Modules\Payment\Models\Payment;
use Modules\User\Models\User;

return [
    'name' => 'Order',

    'invoice' => [
        'model' => env('INVOICE_MODEL', Invoice::class),
    ],

    'payment' => [
        'model' => env('PAYMENT_MODEL', Payment::class),
    ],

    'delivery' => [
        'model' => env('DELIVERY_MODEL', Delivery::class),
    ],

    'customer' => [
        'model' => env('CUSTOMER_MODEL', User::class),
    ],
];
