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

        $user = User::create([
            'email' =>  $data['email'],
            'name' =>  $data['name'],
            'type' => 'merchant',
            'password' => $data['api_key']
        ]);

        return Merchant::create([
                'user_id' => $user->id,
                'domain' => $data['domain'],
                'display_name' => $data['name']
            ]
        );
        // TODO: Complete this method
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
        User::where('id',$user->id)->update([
            'email' =>  $data['email'],
            'name' =>  $data['name'],
            'type' => 'merchant',
            'password' => $data['api_key']
        ]);

        Merchant::where('user_id',$user->id)->update([
                'domain' => $data['domain'],
                'display_name' => $data['name']
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
        return Merchant::whereHas('user', function($q) use($email) {
            $q->where('email', $email);
        })->first();
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
        $orders = Order::where('affiliate_id',$affiliate->id)->where('payout_status','unpaid')->get();
        foreach ($orders as $row){
            dispatch(new PayoutOrderJob($row));
        }

    }
}
