<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    protected Merchant $merchant;

    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $req = $request->all();

        $order = Order::whereBetween('created_at', [$req['from'], $req['to']]);

        $noAffiliate = Order::where('affiliate_id', null)->first();

        return response()->json([
            'count' => $order->count(),
            'revenue' => $order->sum('subtotal'),
            'commissions_owed' => $order->sum('commission_owed') -  $noAffiliate->commission_owed
        ]);
    }
}
