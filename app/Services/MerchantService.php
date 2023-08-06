<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method

        $user = User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['api_key'],
                'type' => User::TYPE_MERCHANT,
            ]
        );

        $user->merchant()->create(
            [
                'domain' => $data['domain'],
                'display_name' => $data['name'],
                'turn_customers_into_affiliates' => isset($data['turn_customers_into_affiliates']) ? $data['turn_customers_into_affiliates'] : true,
                'default_commission_rate' => isset($data['default_commission_rate']) ? $data['default_commission_rate'] : 0.1,
            ]
        );


//        comments will be remove after testing

//        $merchant = new Merchant();
//        $merchant->user_id = $user->id;
//        $merchant->domain = $data['domain'];
//        $merchant->display_name = $data['name'];
//        $merchant->turn_customers_into_affiliates = isset($data['turn_customers_into_affiliates']) ? $data['turn_customers_into_affiliates'] : true;
//        $merchant->default_commission_rate = isset($data['default_commission_rate']) ? $data['default_commission_rate'] : 0.1;
//        $merchant->save();


//        $merchant = new Merchant([
//            'domain' => $data['domain'],
//            'display_name' => $data['name'],
//            'turn_customers_into_affiliates' => true,
//            'default_commission_rate' => 0.1,
//        ]);
//
//        $user->merchant()->save($merchant);


        return $user->merchant();

    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['api_key'];
        $user->save();

        $user->merchant()->update(
            [
                'domain' => $data['domain'],
                'display_name' => $data['name'],
                'turn_customers_into_affiliates' => $data['turn_customers_into_affiliates'],
                'default_commission_rate' => $data['default_commission_rate'],
            ]
        );

    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method

        $user = User::with('merchant')->where('email', $email)->first();
        return $user->merchant();
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        $orders = Order::with('merchant', 'affiliate.user:id,name,email')->where('affiliate_id', $affiliate->id)->where('payout_status', Order::STATUS_UNPAID)->orderBy('id')->get();
        if($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                new PayoutOrderJob($order);
            }
        }
    }
}
