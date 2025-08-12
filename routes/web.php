<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\CheckoutController;
use Modules\Order\Http\Controllers\DownloadController;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Role\Enums\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::group([], function () {
//     Route::resource('order', OrderController::class)->names('order');
// });

Route::get('export-order/token/{token}', [OrderController::class, 'exportOrder'])->name('export_order.token');
Route::get('download-invoice/token/{token}', [OrderController::class, 'downloadInvoice'])->name('download_invoice.token');

Route::apiResource('orders', OrderController::class, [
    'only' => ['show', 'store'],
]);

Route::post('orders/payment', [OrderController::class, 'submitPayment']);

Route::post('orders/checkout/verify', [CheckoutController::class, 'verify']);


Route::get('download_url/token/{token}', [DownloadController::class, 'downloadFile'])->name('download_url.token');

/**
 * ******************************************
 * Authorized Route for Customers only
 * ******************************************
 */
Route::group(['middleware' => ['can:'.Permission::CUSTOMER, 'auth:sanctum', 'email.verified']], function (): void {
    Route::apiResource('orders', OrderController::class, [
        'only' => ['index'],
    ]);
    Route::get('orders/tracking-number/{tracking_number}', [OrderController::class, 'findByTrackingNumber']);

    Route::get('downloads', [DownloadController::class, 'fetchDownloadableFiles']);
    Route::post('downloads/digital_file', [DownloadController::class, 'generateDownloadableUrl']);
});

/**
 * ******************************************
 * Authorized Route for Staff & Store Owner
 * ******************************************
 */
Route::group(
    ['middleware' => ['permission:'.Permission::STAFF.'|'.Permission::STORE_OWNER, 'auth:sanctum', 'email.verified']],
    function (): void {
        Route::apiResource('orders', OrderController::class, [
            'only' => ['update', 'destroy'],
        ]);

        Route::get('export-order-url/{shop_id?}', [OrderController::class, 'exportOrderUrl']);
        Route::post('download-invoice-url', [OrderController::class, 'downloadInvoiceUrl']);
    }
);

/**
 * *****************************************
 * Authorized Route for Super Admin only
 * *****************************************
 */
Route::group(['middleware' => ['permission:'.Permission::SUPER_ADMIN, 'auth:sanctum']], function (): void {
    // Route::apiResource('order-status', OrderStatusController::class, [
    //     'only' => ['store', 'update', 'destroy'],
    // ]);
});
