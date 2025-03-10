<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Order\Http\Controllers\Traits\HandleOrderOperations;
use Modules\Order\Models\Order;
use Modules\Order\Resources\OrderCollection;
use Modules\Order\Services\OrderService;
use Spatie\QueryBuilder\QueryBuilder;

class OrderController extends Controller
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
        $cacheKey = self::CACHE_PUBLIC_ORDERS . $request->query('page', 1);
        $cacheDuration = now()->addMinutes(config('cache.duration'));

        return Cache::remember($cacheKey, $cacheDuration, function ()  {
            return QueryBuilder::for(Order::class)
            ->allowedFields('')->with(['customer', 'items', 'invoice', 'payment', 'delivery'])
            ->paginate(10);
        });

        return response()->json(['orders' => new OrderCollection($orders)]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('order::show');
    }
}
