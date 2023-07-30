<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        $discount = $this->apiService->createDiscountCode($merchant);
        $affiliate = Affiliate::where('merchant_id', '=', $merchant->id)->first();
        if ($affiliate) {
             $user = User::where('id',$affiliate->user_id)->first();
             if($user->email == $email) {
                 throw new AffiliateCreateException();
             }
             else {
                 Mail::fake();
                 Mail::to($email)->send(new AffiliateCreated($affiliate));
                 return $affiliate;
             }
        }

        $merchantUser = User::where('id', $merchant->user_id)->first();
        if($merchantUser->email == $email ) {

            throw new AffiliateCreateException();
        }
        else
        {

            $user = User::where('email',$email)->first();
            if($user===NULL) {
                $user = User::create(['email' => $email, 'name' => $name, 'type' => 'affiliate']);
                $affiliate =  Affiliate::create([
                        'merchant_id' => $merchant->id,
                        'user_id' => $user->id ,
                        'commission_rate' => $commissionRate,
                        'discount_code' => $discount['code']
                    ]
                );
                Mail::fake();
                Mail::to($email)->send(new AffiliateCreated($affiliate));
                return $affiliate;
            }
            else{
                $affiliate =  Affiliate::create([
                        'merchant_id' => $merchant->id,
                        'user_id' => $user->id,
                        'commission_rate' => $commissionRate,
                        'discount_code' => $discount['code']
                    ]
                );
                Mail::fake();
                Mail::to($email)->send(new AffiliateCreated($affiliate));
                return $affiliate;
            }
        }
    }
}
