<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Models\Order;
use Modules\Order\Resources\OrderCollection;
use Modules\Order\Services\OrderService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderController extends Controller
{
    use AuthorizesRequests;

    private const CACHE_PUBLIC_ORDERS = 'public_orders_';
    private const CACHE_ADMIN_ORDERS = 'admin_orders_';
    private const CACHE_FEATURED_ORDERS = 'featured_orders';
    private const CACHE_LATEST_ORDERS = 'latest_orders';

    // Error messages
    private const ERROR_CREATE = 'Failed to create order';
    private const ERROR_UPDATE = 'Failed to update order';
    private const ERROR_DELETE = 'Failed to delete order';
    private const ERROR_FEATURED = 'Failed to retrieve featured orders';
    private const ERROR_LATEST = 'Failed to retrieve latest orders';

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->is('api/v1/dashboard/orders*');
        $cacheKey = sprintf('%s%s', $isAdmin ? self::CACHE_ADMIN_ORDERS : self::CACHE_PUBLIC_ORDERS, $request->query('page', 1));
        $cacheDuration = now()->addMinutes(config('cache.durations'));

        $orders = Cache::remember($cacheKey, $cacheDuration, function () use ($isAdmin) {
            $query = QueryBuilder::for(Order::class)
            ->allowedFields('')->with(['customer', 'items', 'invoice', 'payment', 'delivery']);

            if ($isAdmin) {
                $query->allowedFilters([AllowedFilter::exact('')])->allowedSorts('created_at');
            }

            return $query->paginate(10);
        });

        return response()->json(['orders' => new OrderCollection($orders)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        try {
            // Authorize the action using policies
            $this->authorize('create');

            $orderData = $request->validated();

            $order = $this->orderService->createOrder($orderData);

        } catch (\Exception $e) {
            return $this->handleError(self::ERROR_CREATE . ': ' . $e->getMessage(), $request);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('order::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('order::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Handle error responses.
     *
     * @param string $message The error message to be logged and returned in the response.
     * @param Request|null $request The HTTP request that triggered the error, if available.
     * @param int $statusCode The HTTP status code for the response (default is 500).
     * @return JsonResponse A JSON response containing the success status and error message.
     */
    protected function handleError(string $message, ?Request $request = null, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        Log::error($message, [
            'request' => $request ? $request->all() : [],
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
