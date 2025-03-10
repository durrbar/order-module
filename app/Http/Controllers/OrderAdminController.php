<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Common\Facades\ErrorHelper;
use Modules\Order\Http\Controllers\Traits\HandleOrderOperations;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Models\Order;
use Modules\Order\Resources\OrderCollection;
use Modules\Order\Services\OrderService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderAdminController extends Controller
{
    use AuthorizesRequests;
    use HandleOrderOperations;

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
        $cacheKey = self::CACHE_ADMIN_ORDERS . $request->query('page', 1);
        $cacheDuration = now()->addMinutes(config('cache.duration'));

        return Cache::remember($cacheKey, $cacheDuration, function () {
            return QueryBuilder::for(Order::class)
            ->allowedFields('')->with(['customer', 'items', 'invoice', 'payment', 'delivery'])
            ->allowedFilters([AllowedFilter::exact('')])->allowedSorts('created_at')
            ->paginate(10);
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
        // Use the ErrorHelper facade for error handling
        return ErrorHelper::handleError($message, $request, $statusCode);
    }
}
