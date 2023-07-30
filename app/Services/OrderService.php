<?php

namespace App\Services;

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
        $user = User::where('email', '=', $data['customer_email'])->first();
        if ($user === null) {
            $user = User::create([
                'email' => $data['customer_email'],
                'name' => $data['customer_name'],
                'type' => 'merchant'
            ]);
        }


        $merchant = Merchant::where('domain', '=', $data['merchant_domain'])->first();
        if ($merchant === null) {
            $merchant = Merchant::create([
                    'user_id' => $user->id,
                    'domain' => $data['merchant_domain'],
                    'display_name' => $data['customer_name']
                ]
            );

        }


        $affiliate = $this->affiliateService->register(
            $merchant,
            $data['customer_email'],
            $data['customer_name'],
            0.1
        );

        $order = Order::where('external_order_id', $data['order_id'])->first();
        if ($order === null) {
            Order::create([
                'merchant_id' => $merchant->id,
                'affiliate_id' => $affiliate->id,
                'subtotal' => $data['subtotal_price'],
                'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
                'discount_code' => $data['discount_code'],
                'payout_status' => 'unpaid',
                'external_order_id'=> $data['order_id']
            ]);
        }


    }
}
