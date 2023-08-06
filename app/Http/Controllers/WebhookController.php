<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method

        $data = [
            'order_id' => $request->order_id,
            'subtotal_price' => $request->subtotal_price,
            'merchant_domain' => $request->merchant_domain,
            'discount_code' => $request->discount_code,
            'customer_email' => $request->customer_email,
            'customer_name' => $request->customer_name,
        ];

        (new OrderService($this->orderService))->processOrder($data);

        return JsonResponse::setData($data);
    }
}
