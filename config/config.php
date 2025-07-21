<?php

return [
    'name' => 'Order',

    'invoice' => [
        'model' => env('INVOICE_MODEL', \Modules\Invoice\Models\Invoice::class),
    ],

    'payment' => [
        'model' => env('PAYMENT_MODEL', \Modules\Payment\Models\Payment::class),
    ],

    'delivery' => [
        'model' => env('DELIVERY_MODEL', \Modules\Delivery\Models\Delivery::class),
    ],

    'customer' => [
        'model' => env('CUSTOMER_MODEL', \Modules\User\Models\User::class),
    ],
];
