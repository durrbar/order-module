<?php

namespace Modules\Order\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Dompdf\Options;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Exceptions\DurrbarException;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Ecommerce\Traits\TranslationTrait;
use Modules\Ecommerce\Traits\WalletsTrait;
use Modules\Order\Exports\OrderExport;
use Modules\Order\Http\Requests\OrderCreateRequest;
use Modules\Order\Http\Requests\OrderUpdateRequest;
use Modules\Order\Models\DownloadToken;
use Modules\Order\Repositories\OrderRepository;
use Modules\Order\Traits\OrderManagementTrait;
use Modules\Payment\Enums\PaymentGatewayType;
use Modules\Payment\Traits\PaymentStatusManagerWithOrderTrait;
use Modules\Payment\Traits\PaymentTrait;
use Modules\Role\Enums\Permission;
use Modules\Settings\Models\Settings;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends CoreController
{
    use OrderManagementTrait;
    use PaymentStatusManagerWithOrderTrait;
    use PaymentTrait;
    use TranslationTrait;
    use WalletsTrait;

    public OrderRepository $repository;

    public Settings $settings;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
        $this->settings = Settings::first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection|Order[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;

        return $this->fetchOrders($request)->paginate($limit)->withQueryString();
    }

    /**
     * fetchOrders
     *
     * @param  mixed  $request
     * @return object
     */
    public function fetchOrders(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }

        switch ($user) {
            case $user->hasPermissionTo(Permission::SUPER_ADMIN):
                return $this->repository->with('children')->where('id', '!=', null)->where('parent_id', '=', null);
                break;

            case $user->hasPermissionTo(Permission::STORE_OWNER):
                if ($this->repository->hasPermission($user, $request->shop_id)) {
                    return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null);
                } else {
                    $orders = $this->repository->with('children')->where('parent_id', '!=', null)->whereIn('shop_id', $user->shops->pluck('id'));

                    return $orders;
                }
                break;

            case $user->hasPermissionTo(Permission::STAFF):
                if ($this->repository->hasPermission($user, $request->shop_id)) {
                    return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null);
                } else {
                    $orders = $this->repository->with('children')->where('parent_id', '!=', null)->where('shop_id', '=', $user->shop_id);

                    return $orders;
                }
                break;

            default:
                return $this->repository->with('children')->where('customer_id', '=', $user->id)->where('parent_id', '=', null);
                break;
        }

        // ********************* Old code *********************

        // if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && (!isset($request->shop_id) || $request->shop_id === 'undefined')) {
        //     return $this->repository->with('children')->where('id', '!=', null)->where('parent_id', '=', null); //->paginate($limit);
        // } else if ($this->repository->hasPermission($user, $request->shop_id)) {
        //     // if ($user && $user->hasPermissionTo(Permission::STORE_OWNER)) {
        //     return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
        //     // } elseif ($user && $user->hasPermissionTo(Permission::STAFF)) {
        //     //     return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
        //     // }
        // } else {
        //     return $this->repository->with('children')->where('customer_id', '=', $user->id)->where('parent_id', '=', null); //->paginate($limit);
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     *
     * @throws DurrbarException
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            // decision need
            // if(!($this->settings->options['useCashOnDelivery'] && $this->settings->options['useEnableGateway'])){
            //     throw new HttpException(400, PLEASE_ENABLE_PAYMENT_OPTION_FROM_THE_SETTINGS);
            // }

            return DB::transaction(fn () => $this->repository->storeOrder($request, $this->settings));
        } catch (DurrbarException $th) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     *
     * @throws DurrbarException
     */
    public function show(Request $request, $params)
    {
        $request['tracking_number'] = $params;
        try {
            return $this->fetchSingleOrder($request);
        } catch (DurrbarException $e) {
            throw new DurrbarException($e->getMessage());
        }
    }

    /**
     * fetchSingleOrder
     *
     * @param  mixed  $request
     * @return void
     *
     * @throws DurrbarException
     */
    public function fetchSingleOrder(Request $request)
    {
        $user = $request->user() ?? null;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        $orderParam = $request->tracking_number ?? $request->id;
        try {
            $order = $this->repository->where('language', $language)->with([
                'products',
                'shop',
                'children.shop',
                'wallet_point',
            ])->where('id', $orderParam)->orWhere('tracking_number', $orderParam)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(NOT_FOUND);
        }

        // Create Intent
        if (! in_array($order->payment_gateway, [
            PaymentGatewayType::CASH, PaymentGatewayType::CASH_ON_DELIVERY, PaymentGatewayType::FULL_WALLET_PAYMENT,
        ])) {
            // $order['payment_intent'] = $this->processPaymentIntent($request, $this->settings);
            $order['payment_intent'] = $this->attachPaymentIntent($orderParam);
        }

        if (! $order->customer_id) {
            return $order;
        }
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
            return $order;
        } elseif (isset($order->shop_id)) {
            if ($user && ($this->repository->hasPermission($user, $order->shop_id) || $user->id == $order->customer_id)) {
                return $order;
            }
        } elseif ($user && $user->id == $order->customer_id) {
            return $order;
        } else {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }
    }

    /**
     * findByTrackingNumber
     *
     * @param  mixed  $request
     * @param  mixed  $tracking_number
     * @return void
     */
    public function findByTrackingNumber(Request $request, $tracking_number)
    {
        $user = $request->user() ?? null;
        try {
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point', 'payment_intent'])
                ->findOneByFieldOrFail('tracking_number', $tracking_number);

            if ($order->customer_id === null) {
                return $order;
            }
            if ($user && ($user->id === $order->customer_id || $user->can('super_admin'))) {
                return $order;
            } else {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(OrderUpdateRequest $request, $id)
    {
        try {
            $request['id'] = $id;

            return $this->updateOrder($request);
        } catch (DurrbarException $e) {
            throw new DurrbarException(COULD_NOT_UPDATE_THE_RESOURCE, $e->getMessage());
        }
    }

    public function updateOrder(OrderUpdateRequest $request)
    {
        return $this->repository->updateOrder($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            return $this->repository->findOrFail($id)->delete();
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_FOUND);
        }
    }

    /**
     * Export order dynamic url
     *
     * @param  int  $shop_id
     * @return string
     */
    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        try {
            $user = $request->user();

            if ($user && ! $this->repository->hasPermission($user, $request->shop_id)) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }

            $dataArray = [
                'user_id' => $user->id,
                'token' => Str::random(16),
                'payload' => $request->shop_id,
            ];
            $newToken = DownloadToken::create($dataArray);

            return route('export_order.token', ['token' => $newToken->token]);
        } catch (DurrbarException $e) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

    /**
     * Export order to excel sheet
     *
     * @param  string  $token
     * @return void
     */
    public function exportOrder($token)
    {
        $shop_id = 0;
        try {
            $downloadToken = DownloadToken::where('token', $token)->first();

            $shop_id = $downloadToken->payload;
            $downloadToken->delete();
        } catch (DurrbarException $e) {
            throw new DurrbarException(TOKEN_NOT_FOUND);
        }

        try {
            return Excel::download(new OrderExport($this->repository, $shop_id), 'orders.xlsx');
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_FOUND);
        }
    }

    /**
     * Export order dynamic url
     *
     * @param  int  $shop_id
     * @return string
     */
    public function downloadInvoiceUrl(Request $request)
    {

        try {
            $user = $request->user();
            if ($user && ! $this->repository->hasPermission($user, $request->shop_id)) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
            if (empty($request->order_id)) {
                throw new NotFoundHttpException(NOT_FOUND);
            }
            $language = $request->language ?? DEFAULT_LANGUAGE;
            $isRTL = $request->is_rtl ?? false;

            $translatedText = $this->formatInvoiceTranslateText($request->translated_text);

            $payload = [
                'user_id' => $user->id,
                'order_id' => intval($request->order_id),
                'language' => $language,
                'translated_text' => $translatedText,
                'is_rtl' => $isRTL,
            ];

            $data = [
                'user_id' => $user->id,
                'token' => Str::random(16),
                'payload' => serialize($payload),
            ];

            $newToken = DownloadToken::create($data);

            return route('download_invoice.token', ['token' => $newToken->token]);
        } catch (DurrbarException $e) {
            throw new DurrbarException($e->getMessage());
        }
    }

    /**
     * Export order to excel sheet
     *
     * @param  string  $token
     * @return void
     */
    public function downloadInvoice($token)
    {
        $payloads = [];
        try {
            $downloadToken = DownloadToken::where('token', $token)->firstOrFail();
            $payloads = unserialize($downloadToken->payload);
            $downloadToken->delete();
        } catch (DurrbarException $e) {
            throw new DurrbarException(TOKEN_NOT_FOUND);
        }

        try {
            $settings = Settings::getData($payloads['language']);
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point', 'parent_order'])->where('id', $payloads['order_id'])->orWhere('tracking_number', $payloads['order_id'])->firstOrFail();

            $invoiceData = [
                'order' => $order,
                'settings' => $settings,
                'translated_text' => $payloads['translated_text'],
                'is_rtl' => $payloads['is_rtl'],
                'language' => $payloads['language'],
            ];
            $pdf = PDF::loadView('pdf.order-invoice', $invoiceData);
            $options = new Options();
            $options->setIsPhpEnabled(true);
            $options->setIsJavascriptEnabled(true);
            $pdf->getDomPDF()->setOptions($options);

            $filename = 'invoice-order-'.$payloads['order_id'].'.pdf';

            return $pdf->download($filename);
        } catch (DurrbarException $e) {
            throw new DurrbarException(NOT_FOUND);
        }
    }

    /**
     * submitPayment
     *
     * @param  mixed  $request
     *
     * @throws Exception
     */
    public function submitPayment(Request $request): void
    {
        $tracking_number = $request->tracking_number ?? null;
        try {
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point', 'payment_intent'])
                ->findOneByFieldOrFail('tracking_number', $tracking_number);

            $isFinal = $this->checkOrderStatusIsFinal($order);
            if ($isFinal) {
                return;
            }

            switch ($order->payment_gateway) {

                case PaymentGatewayType::STRIPE:
                    $this->stripe($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::PAYPAL:
                    $this->paypal($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::MOLLIE:
                    $this->mollie($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::RAZORPAY:
                    $this->razorpay($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::SSLCOMMERZ:
                    $this->sslcommerz($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::PAYSTACK:
                    $this->paystack($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::PAYMONGO:
                    $this->paymongo($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::XENDIT:
                    $this->xendit($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::IYZICO:
                    $this->iyzico($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::BKASH:
                    $this->bkash($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::FLUTTERWAVE:
                    $this->flutterwave($order, $request, $this->settings);
                    break;
            }
        } catch (DurrbarException $e) {
            throw new DurrbarException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }
}
