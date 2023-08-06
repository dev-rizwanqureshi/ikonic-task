<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method

        $order = Order::with('merchant')->find($data['order_id']);
        if($order) {

            $order->subtotal = $data['subtotal_price'];
            $order->discount_code = $data['discount_code'];

            $user = User::with('affiliate')->find($data['customer_email']);
            if($user && ! $user->affiliate()->exists()) {

                $user->affiliate()->create(
                    [
                        'merchant_id' => $order->merchant_id,
                        'commission_rate' => $order->merchant->default_commission_rate,
                        'discount_code' => $data['discount_code'],
                    ]
                );

            }

            if($user) {
                $order->affiliate_id = $user->affiliate()->id;
            }


            $order->save();
        }
    }
}
