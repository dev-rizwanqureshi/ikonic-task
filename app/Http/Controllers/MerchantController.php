<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
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

        $ordersQuery = Order::query();
        if($request->filled('from')) {
            $from = Carbon::parse($request->from);
            $ordersQuery = $ordersQuery->where('created', '>=', $from);
        }

        if($request->filled('to_date')) {
            $to_date = Carbon::parse($request->to_date);
            $ordersQuery = $ordersQuery->where('created', '<=', $to_date);
        }

        $total = clone $ordersQuery;
        $revenue = clone $ordersQuery;
        $commission_owed = clone $ordersQuery;

        $data = [
            'count' => $total->count(),
            'commission_owed' => $commission_owed->where('payout_status', Order::STATUS_UNPAID)->whereNotNull('affiliate_id')->sum('commission_owed'),
            'revenue' => $revenue->where('payout_status', Order::STATUS_PAID)->sum('subtotal')
        ];

        return JsonResponse::setData($data);
    }
}
