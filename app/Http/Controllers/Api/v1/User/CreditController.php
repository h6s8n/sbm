<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Model\Visit\TransactionCredit;
use App\Services\Gateways\src\PayStar;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class CreditController extends Controller
{
    // create inner payment url
    public function NewPay(Request $request)
    {
        $ValidData = $this->validate($request, [
            'price' => 'required_if:settlement_type,rial|numeric|min:10000|',
        ]);
        $user = auth()->user();
        $token = Str::random(30);

        $newTrans = new TransactionCredit();
        $newTrans->user_id = $user->id;
        $newTrans->amount = $ValidData['price'];
        $newTrans->token = $token;
        $newTrans->save();

        return success_template(['pay_link' => url('payment/credit/' . $token)]);
    }

    // create payment gateway pay url
    public function gateway($token)
    {

		$signKey = 'F641CBB767C412455976E2F4405F0C8325AEE4149E22707836D15551B91126063FFF76DEB6D954932769E0261EF76DBDC7F6A4FB9A6276DBA8134BDCDE355B7BB98AEC7EBF48FB322966E8472645203E61FF5047095A339C358C0522F97420D08C31D29A54333917FD7A44A6936D4437D155950B9413EC0200AE399B0B058019';
        $gatewayId = 'x7ddeyxg8n48k9';
        // $token is created token in NewPay method
        $payInfo = TransactionCredit::where('token', $token)->first();

        if (!$payInfo)
            return redirect(get_ev('cp_live') . '/user/credit_fail/' . $token);
        
        $user = User::where('id', $payInfo->user_id)->first();

        //اینجا اطلاعات رو میدیم و یه توکن میگیریم
        $pay = new PayStar($signKey, $gatewayId);
        $payment = $pay->create(
            (int)$payInfo->amount,
            $token, // order_id
            url('condition_pay/increase_credit/' . $token), // callback_url
            $user->mobile,
            'افزایش اعتبار کاربر: ' . $user->fullname // desc
        );
		

        // اینجا با توکنی که گرفته شده لینک پرداخت درست میشه
        $payment_url = $pay->createURL($payment);

        echo($payment_url);
        if ($payment_url)
        {
            return redirect()->to($payment_url);
        }
        else
        {
            return redirect()->to("https://cp.sbm24.com/user/reserve_fail/{$token}");
        }
        
        return 'Redirecting ...';
    }

    // verify calledback user from payment gateway after pay
    public function verify($token)
    {
        $redirect_fail = fn($_token) => redirect(get_ev('cp_live') . '/user/credit_fail/' . $_token);

        // get returned data from payment gateway after pay
        $payment = (object) request()->all();
         
        $payInfo = TransactionCredit::where('token', $token)->first();
        if (!$payInfo) return $redirect_fail($token);
        
        $user = User::where('id', $payInfo->user_id)->first();
		$signKey = 'F641CBB767C412455976E2F4405F0C8325AEE4149E22707836D15551B91126063FFF76DEB6D954932769E0261EF76DBDC7F6A4FB9A6276DBA8134BDCDE355B7BB98AEC7EBF48FB322966E8472645203E61FF5047095A339C358C0522F97420D08C31D29A54333917FD7A44A6936D4437D155950B9413EC0200AE399B0B058019';
        $gatewayId = 'x7ddeyxg8n48k9';
        if ($payment->status == "1") {
			
            // order_id,ref_num,card_number,tracking_code
			
            $pay = new PayStar($signKey, $gatewayId);
			
            $verify_payment = $pay->verify($payInfo->amount, $payment);


            if ($verify_payment->status == "1") {
                // save pay info as paid.
                $payInfo->message = 'OK-PayStar';  // *** change 'OK' to 'OK-PayStar' to determine which 'factorNumber' is for which payment gateway
                $payInfo->status = 'paid';
                $payInfo->factorNumber = $verify_payment->data->ref_num;
                $payInfo->save();

                // update user credit
                $user->credit += $payInfo->amount;
                $user->save();

                return redirect(get_ev('cp_live') . '/user/increase-credit');
            }
        }

        // save pay info as cancel
        $payInfo->message = 'تراکنش لغو شد';
        $payInfo->status = 'cancel';
        $payInfo->save();

        return $redirect_fail($token);
    }
}
