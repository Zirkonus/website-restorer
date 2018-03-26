<?php

namespace App\Http\Controllers;

use App\Setting;
use App\Transaction;
use App\User;
use App\NanacastLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class CreditController extends Controller
{
    /**
     * Request processing depending on the field u_custom_3.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        //check ip
        if ($request->ip() == "209.59.188.27") {
            // save request to lo nanacast_logs table
            $nanacast = new NanacastLog;
            $nanacast->data = json_encode($request->all());
            $nanacast->save();

            if ($request->mode == 'suspend') {
                $this->suspend($request);
            }

            if (in_array($request->mode, ['decline', 'delete', 'suspend'])) {
                return;
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'u_email' => 'required|email|max:250',
                'u_custom_2' => 'required',
                'u_custom_3' => 'required',
                'u_custom_5' => 'required',
            ]);

            if ($validator->fails()) {
                // TODO validation error.
                return;
            }

            if ($request->u_custom_3 == 0) {
                $this->createLicence($request);
            } else {
                $user = User::where('api_id', $request->id)->first() or $user = User::where('email', $request->u_email)->first();
                if (isset($user->id)) {
                    $this->saveTransaction($request, $user);
                } else {
                    // TODO licence not found error.
                }
            }
        }
    }

    /**
     * Binding a user to a new license.
     *
     * @param $request
     */
    protected function createLicence($request)
    {
        $user = User::where('email', $request->u_email)->first();
        if (isset($user->id)) {
            $user->api_id   = $request->id;
            $this->saveTransaction($request, $user);
        } else {
            // TODO not found user.
        }
    }

    /**
     * Crediting of prepaid loans to the user.
     *
     * @param $request
     */
    protected function saveTransaction($request, $user)
    {
        $user->credits += $request->u_custom_2;
        if($request->u_custom_1 == 1) {
            $user->licence = 'payment';
            $user->u_custom_4 = $request->u_custom_4;
        }else{
            $user->licence = 'add';
        }

        $transaction = new Transaction();
        $transaction->transaction_credits = $request->u_custom_2;
        $transaction->credits_left        = $request->u_custom_2;
        if ($request->u_custom_5 == 0) {
            $transaction->expired_at      = Carbon::now()->addDays(30)->toDateString();
        }
        $transaction->transaction_data    = json_encode($request->all());

        DB::transaction(function() use ($transaction, $user) {
            $user->transactions()->save($transaction);
            $user->save();
        });
    }

    /**
     * Crediting of prepaid loans to the user.
     *
     * @param $request
     */
    protected function suspend($request)
    {
        $user = User::where('api_id', $request->id)->first() or $user =  User::where('email', $request->u_email)->first();
        if($user) {
            $user->licence = 'suspend';
            $user->save();
        }
        return;
    }

    /**
     * Crediting of prepaid loans to the user.
     *
     * @param $request
     */
    protected function reactivate($request)
    {
        //
    }
}

/*
Array (
    [mode] => add
    [id] => 10698248
    [u_access_code] => 07350567475b
    [u_list_id] => 10002382
    [item_name] => SEO Recovery Professional
    [u_subscribe_referer] => http://special.seorecovery.org/
    [u_date_added] => 2017-07-02 06:28:35
    [u_start_date] => 2017-07-02
    [u_last_contact] => 2017-07-02 06:28:35
    [u_last_unsubscribe_reason] =>
    [u_subscribe_ip] => 115.188.142.171
    [u_ip_country] => NEW ZEALAND
    [u_coupon_id] => 0
    [coupon_code] =>
    [alt_pricing_id] => 0
    [u_affiliate_id] => 311526
    [u_affiliate_id_2] => 0
    [u_affiliate_campaign_id] => 0
    [u_affiliate_custom_1] =>
    [u_first_price] => 97.00
    [u_quantity] => 1
    [u_first_aff_comm] => 0.00
    [u_first_aff_comm_2] => 0.00
[u_recurring_price] => 0.00
    [u_recurring_quantity] => 1
    [u_recurring_aff_comm] => 0.00
    [u_recurring_aff_comm_2] => 0.00
    [u_billing_interval] => 0
    [u_installments_needed] => 0
    [u_installments_collected] => 0
    [u_expiration] =>
    [u_external_order_id] =>
    [u_last_transaction_id] => 0
    [u_paypal_email] => clivejpatterson@gmail.com
    [u_paypal_payer_id] => 3C9VNBRPWFA34
    [u_paypal_trans_id] => 15624238RY245781P
    [account_id] => 0
    [feedurl] => http://nanacast.com/ac/07350567475b
[u_email] => clivejpatterson@gmail.com
    [u_title] =>
[u_firstname] => Clive
[u_lastname] => Patterson
    [u_business] =>
    [u_address] =>
    [u_address_2] =>
    [u_city] =>
    [u_state] => --
    [u_zip] =>
    [u_country] => NEW ZEALAND
    [u_phone] =>
    [u_fax] =>
    [u_timezone] => 12:00
    [u_website] =>
[u_username] =>
[u_password] =>
    [u_cc_first_two] => 00
    [u_cc_last_four] => 0000
    [u_cc_exp] =>
    [u_cc_firstname] =>
    [u_cc_lastname] =>
    [u_cc_address] =>
    [u_cc_city] =>
    [u_cc_state] =>
    [u_cc_zip] =>
    [u_cc_country] =>
    [u_cc_phone] =>
    [u_custom_4] => Yes
    [u_custom_5] => Jan-16-Professional
)

*/