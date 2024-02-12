<?php

namespace App\Http\Controllers\Admin\MoneyTransfer;

use App\Model\Visit\TransactionCredit;
use App\Repositories\v1\MoneyTransfer\Gateway;
use App\Repositories\v1\MoneyTransfer\PayPing;
use App\SendSMS;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct()
    {
        require(base_path('app/jdf.php'));
    }
    public function initialize(User $user)
    {
        if (auth()->user()->approve!=10)
            return redirect()->back()->with(['error'=>'Permission Denied !']);
        if (!$user->account_sheba)
            return redirect()->back()->with(['error'=>'شماره شبای کاربر وارد نشده است']);
        return view('admin.MoneyTransfer.transfer',compact('user'));

    }

    public function transfer(User $user,Request $request)
    {
        $amount =str_replace(',', '',$request->amount);
        $request->validate([
            'amount'=>'required'
        ]);
        if ($amount > $user->credit)
            return redirect()->back()->with(['error'=>'مبلغ درخواستی بیش از اعتبار کاربر است']);
        $payPing = new PayPing($amount, $user->account_sheba,$request->description);
        $gatewaye = new Gateway($payPing);
        $response = $gatewaye->transfer();
        if (!$response['error']) {
            $result_sh = json_decode($response['response']);
            if ($result_sh && isset($result_sh->code)) {
                try {
                    DB::beginTransaction();
                    $tr = new TransactionCredit();
                    $tr->status = 'paid';
                    $tr->message = $result_sh->code . 'کاهش اعتبار و انتقال به شبا با کد ';
                    $tr->amount = $amount;
                    $tr->user_id = $user->id;
                    $tr->token = str_random(20);
                    $tr->save();
                    $user->credit = $user->credit - $amount;
                    $user->save();
                    DB::commit();
                }
                catch(\Exception $exception){
                    DB::rollBack();
                }
                if($user->mobile){
                    SendSMS::sendTemplateTwo($user->mobile, jdate('Y-m-d', strtotime($request->reserve_time)), $result_sh->code, 'CheckoutDr');
                }
                return back()->with('success' ,  'صورت حساب پرداخت شد، کد شناسایی ' . $result_sh->code)->withInput();
            }else {
                return back()->with('success', $response)->withInput();
            }
        }
    }
}
