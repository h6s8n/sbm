<?php
namespace App\Http\Controllers;
use App\Model\Wallet\DoctorWallet;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Model\Visit\TransactionDoctor;
class FinancialManagementController extends Controller
{
    public function index($showSuccessMessage = false)
    {
        $doctor = auth()->user();
        $rial_amount = $this->accountBalance($doctor);
        $gold_amount = $doctor->gold;
        $gold_price = $this->goldPrice();
        $shebas = DB::table('doctors_sheba')->where('user_id', $doctor->id)->take(3)->get();
        $exchange_logs = DB::table('exchange_logs')->where('user_id', $doctor->id)->get();
        $withdraw_logs = DB::table('withdraw_log')->where('user_id', $doctor->id)->get();
        $sum = TransactionDoctor::where('doctor_id', $doctor->id)->where('status', 'pending')->sum('Amount');
        /*$visit_logs = TransactionDoctor::select('users.fullname as PatientFullname', 'transaction_doctors.status','transaction_doctors.amount','transaction_doctors.id', 
'transaction_doctors.created_at', 'transaction_doctors.updated_at')
        ->join('users', 'users.id', '=', 'transaction_doctors.user_id')
        ->where('transaction_doctors.doctor_id', $doctor->id)
        ->where('transaction_doctors.amount', '<>', 0)
		->latest('transaction_doctors.created_at')
        ->take(20)
        ->get(); */
		$totalAmount = DB::table('visit_wallet_transaction')->where('doctor_id', $doctor->id)->sum('amount');

		$visit_log_wallet =  DB::table('visit_wallet_transaction')
    ->join('users', 'visit_wallet_transaction.user_id', '=', 'users.id')
    ->select('visit_wallet_transaction.user_id', 'visit_wallet_transaction.doctor_id', 'users.fullname', 'visit_wallet_transaction.amount', 'visit_wallet_transaction.recipt', 'visit_wallet_transaction.created_at', 'visit_wallet_transaction.type')
    ->get();


		
		
        return response()->json([
            'ok' => true,
			
            'message' => $showSuccessMessage ?? 'index data',
			'doctormobile' =>$doctor->mobile,
            'user_name' => $doctor->fullname,
            'rial_amount' => $rial_amount,
            'gold_amount' => $gold_amount,
            'gold_price' => $gold_price,
            'shebas' => $shebas,
            'exchange_logs' => $exchange_logs,
            'withdraw_logs' => $withdraw_logs,
            'visit_amount'=> $totalAmount,
			
			'visit_wallet'=> $visit_log_wallet,
        ]);
        // return view('path.to.view.in.resources.views', compact('doctor', 'rial_amount', 'gold_amount', 'gold_price', 'logs', 'showSuccessMessage'));
    }
	
	 function GetSetToken(Request $request) {
    
    $lang = $request->user_lang;

    // تبدیل متغیرهای `$ip` و `$lang` به باینری
    
    $binaryLang = pack('H*', bin2hex($lang));

    $result = $binaryLang;

    $stringResult = implode('', unpack('H*', $result));

       return response()->json([
            'ok' => true,
            'request_token' => $stringResult,
		   'lang' => $lang,
            
        ]);
}
	
	
	
    function addSheba(Request $request)
    {
        $doctor = auth()->user();
        $shebas = DB::table('doctors_sheba')->where('user_id', $doctor->id);

        if ($shebas->count() < 3) {
            if (preg_match('/^(?:IR)(?=.{24}$)[0-9]*$/', $request->sheba)) {

                DB::table('doctors_sheba')->insert([
                    'uuid' => Str::uuid(),
                    'user_id' => $doctor->id,
                    'sheba' => $request->sheba,
					'status' => 0,
					'name' => $request->name
                ]);

                return $this->index('شبا اضافه شد.');
            } else {
                return response()->json([
                    'ok' => false,
                    'message' => 'شبا وارد شده اشتباه است.',
                ]);
            }
        } else {
            return response()->json([
                'ok' => false,
                'message' => 'نمیتوانید بیشتر از 3 شبا اضافه کنید.',
            ]);
        }
    }



    function removeSheba(Request $request)
    {
        $doctor = auth()->user();
        
        // حذف رکورد با استفاده از شبا و شناسه کاربر
        $deletedRows = DB::table('doctors_sheba')
                        ->where('user_id', $doctor->id)
                        ->where('sheba', $request->sheba)
                        ->where('uuid', $request->uuid)
                        ->delete();
    
        if ($deletedRows > 0) {
            return $this->index('شبا با موفقیت حذف شد.');
        } else {
            return response()->json([
                'ok' => false,
                'message' => 'شبا مورد نظر یافت نشد.',
            ]);
        }
    }


    public function withdrawMoney(Request $request)
    {
        $doctor = auth()->user();
		$amount = (int) str_replace(',','',$request->amount);
        $sheba_uuid = $request->sheba;
        $custom_sheba = $request->custom_sheba;
        $type = $request->type;
        $account_balance = $this->accountBalance($doctor);

        // specify type of amount
        if ($type == 'selected') {
            if (empty($amount)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'مقدار انتخابی برای برداشت نباید خالی باشد.',
                ]);
            }
            if ($amount <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'مقدار انتخاب شده برای برداشت نباید کمتر از صفر باشد.',
                ]);
            }
            if ($amount > $account_balance) {
                return response()->json([
                    'ok' => false,
                    'message' => 'مقدار انتخاب شده برای برداشت بیشتر از دارایی ریالی شما است.',
                ]);
            }
            $finally_amount = $amount;
        } elseif ($type == 'full') {
            return response()->json([
                'ok' => false,
                'message' => 'برداشت کامل غیر فعال شده است.',
            ]);
            $finally_amount = $account_balance;
        } else {
            return response()->json([
                'ok' => false,
                'message' => 'خطا در نوع برداشت.',
            ]);
        }

        // specify sheba
        if($sheba_uuid == 'empty') {
            return response()->json([
                'ok' => false,
                'message' => 'شبا را مشخص کنید.',
            ]);
        } elseif ($sheba_uuid !== 'custom') {
            $sheba_row = DB::table('doctors_sheba')->where('uuid', $sheba_uuid)->first();

            if (!$sheba_row) {
                return response()->json([
                    'ok' => false,
                    'message' => 'شبای انتخاب شده یافت نشد.',
                ]);
            }
            $finally_sheba = $sheba_row->sheba;
            $wage = 0;
        } elseif($sheba_uuid !== 'empty') {
            return response()->json([
                'ok' => false,
                'message' => 'شبا را مشخص کنید.',
            ]);
        } else {
            if (preg_match('/^(?:IR)(?=.{24}$)[0-9]*$/', $custom_sheba)) {
                $finally_sheba = $custom_sheba;
                $wage = 0;
            } else {
                return response()->json([
                    'ok' => false,
                    'message' => 'شبای وارد شده اشتباه است.',
                ]);
            }
        }

        $withdraw = $this->withdraw($finally_sheba, $finally_amount - $wage);

        if (!$withdraw->ok) {
            return response()->json([
                'ok' => false,
                'message' => $withdraw->message,
            ]);
        }
        $bank_wage = $withdraw->amount_with_wage - $withdraw->data->amount;
        $this->decreaseRial(
            $doctor,
            $finally_amount,
            "FMP: واریز به شبای $finally_sheba",
            $bank_wage
        );

        $this->registerWithdrawLog($doctor, $finally_amount, $wage, $finally_sheba, $withdraw->data->destination_firstname . ' ' . $withdraw->data->destination_lastname);

        return $this->index(
            $finally_amount == $account_balance
                ? 'واریز تمام موجودی ریالی به حساب بانکی شما انجام شد.'
                : 'واریز بخشی از موجودی ریالی به حساب بانکی شما انجام شد.'
        );
    }
    public function checkoutVisit(Request $request)
    {
        $doctor = auth()->user();
        $id = $request->id;
        $sheba_uuid = $request->sheba;
        $custom_sheba = $request->custom_sheba;

        // specify sheba
        if($sheba_uuid == 'empty') {
            return response()->json([
                'ok' => false,
                'message' => 'شبا را مشخص کنید.',
            ]);
        } elseif ($sheba_uuid !== 'custom') {
            $sheba_row = DB::table('doctors_sheba')->where('uuid', $sheba_uuid)->first();

            if (!$sheba_row) {
                return response()->json([
                    'ok' => false,
                    'message' => 'شبای انتخاب شده یافت نشد.',
                ]);
            }
            $finally_sheba = $sheba_row->sheba;
            $isShebaCustom = false;
        } else {
            if (preg_match('/^(?:IR)(?=.{24}$)[0-9]*$/', $custom_sheba)) {
                $finally_sheba = $custom_sheba;
                $isShebaCustom = true;
            } else {
                return response()->json([
                    'ok' => false,
                    'message' => 'شبای وارد شده اشتباه است.',
                ]);
            }
        }


        // specify visit info
        $visit = TransactionDoctor::where('id', $id)->where('doctor_id', $doctor->id)->where('status', 'pending');
		
        if(!$visit->exists())
        {
            return response()->json([
                'ok' => false,
                'message' => 'ویزیت معتبر برای تسویه یافت نشد.',
            ]);
        }
        $visit = $visit->first();
        $amount = (int) $visit->amount;

        $withdraw = $this->visitWithdraw($finally_sheba, $isShebaCustom ? $amount - ($amount/100) : $amount);

        if (!$withdraw->ok) {
            return response()->json([
                'ok' => false,
                'message' => $withdraw->message,
            ]);
        }

        $visit->status = 'paid';
        $visit->save();

        return $this->index('ویزیت تسویه شد.');
    }
    public function exchange(Request $request)
    {
        $doctor = auth()->user();
        $amount = $request->amount;
        $type = $request->type;
        $method = 'exchanging' . Str::studly($type);

        if (empty($amount) || $amount <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'مقدار مبادله باید بیشتر از صفر باشد.',
            ]);
        }
        if (empty($type)) {
            return response()->json([
                'ok' => false,
                'message' => 'نوع مبادله باید مشخص شود.',
            ]);
        }
        if (method_exists($this, $method)) {
            return response()->json([
                'ok' => false,
                'message' => 'نوع تبادل تعریف نشده است.',
            ]);
        }
        return $this->{$method}($doctor, $amount, $type);
    }
    private function withdraw($sheba, $amount)
    {
        $result = (new \App\Services\Gateways\src\PayStarWallet1())->pay(
            Str::random(30),
            $amount,
            '' . $sheba,
            '', // firstname
            '', // lastname
        );
        $res = (object)[];
        $res->ok = $result->ok;
        $res->status = $result->status;
        $res->message = $result->message;
        $res->message_fa = $result->message_fa;

        if (isset($result->data) && isset($result->data->created_settlements) && isset($result->data->created_settlements[0])) {
            $res2 = $result->data->created_settlements[0];
            $res->id = $res2->id;
            $res->amount_with_wage = $res2->amount_with_wage;
            $res->data = $res2->data;
        }
        return $res;
    }

    private function visitWithdraw($sheba, $amount)
    {
        $result = (new \App\Services\Gateways\src\PayStarWallet())->pay(
            Str::random(30),
            $amount,
            '' . $sheba,
            '', // firstname
            '', // lastname
        );

        $res = (object)[];
        $res->ok = $result->ok;
        $res->status = $result->status;
        $res->message = $result->message;
        $res->message_fa = $result->message_fa;

        if (isset($result->data) && isset($result->data->created_settlements) && isset($result->data->created_settlements[0])) {
            $res2 = $result->data->created_settlements[0];

            $res->id = $res2->id;
            $res->amount_with_wage = $res2->amount_with_wage;
            $res->data = $res2->data;
        }

        return $res;
    }

    private function exchangingRialToGold($doctor, $amount, $type)
    {
        $gold_price = $amount * $this->goldPrice();
        $account_balance = $this->accountBalance($doctor);

        if ($gold_price > $account_balance) {
            return response()->json([
                'ok' => false,
                'message' => "دارایی ریالی برای خرید $amount گرم طلا کافی نمی باشد.",
            ]);
            // return back()->withErrors(['account_balance' => "دارایی ریالی برای خرید $amount گرم طلا کافی نمی باشد."]);
        }

        $this->decreaseRial(
            $doctor,
            $gold_price,
            "دریافت $amount گرم طلا."
        );
        $this->increaseGold(
            $doctor,
            $amount,
        );

        $this->registerExchangeLog($doctor, $type, $amount, $gold_price, $this->goldPrice());

        return $this->index("مقدار $amount گرم طلا خریداری شد.");
    }

    private function exchangingGoldToRial($doctor, $amount, $type)
    {
        $gold_price = $amount * $this->goldPrice();

        if ($amount > $doctor->gold) {
            return response()->json([
                'ok' => false,
                'message' => "دارایی طلای شما برای فروش $amount گرم طلا کافی نمی باشد.",
            ]);
            // return back()->withErrors(['account_balance' => "دارایی طلای شما برای فروش $amount گرم طلا کافی نمی باشد."]);
        }

        $this->decreaseGold(
            $doctor,
            $amount,
        );
        $this->increaseRial(
            $doctor,
            $gold_price,
            "فروش $amount گرم طلا."
        );

        $this->registerExchangeLog($doctor, $type, $gold_price, $amount, $this->goldPrice());

        return $this->index("مقدار $amount گرم طلا فروخته شد.");
    }

    private function goldPrice()
    {
        static $result;
        if (!$result) {
            $result = json_decode(file_get_contents('http://108.165.128.108/api/gold.json'));
        }
        return $result;
    }

    private function increaseRial($doctor, $amount, $desc)
    {
        $doctor = auth()->user();

        $token = Str::random(30);
        $newTrans = new DoctorWallet();

        $newTrans->doctor_id = $doctor->id;
        $newTrans->user_id = $doctor->id;

        $newTrans->amount = $amount;
        $newTrans->description = $desc;
        $newTrans->bank_wage = 0;
        $newTrans->service_wage = 0;
        $newTrans->type = 'increase';
        $newTrans->status = 'paid_increase';
        $newTrans->service = 'exchange';
        $newTrans->token = $token;

        return $newTrans->save();
    }

    private function decreaseRial($doctor, $amount, $desc, $bank_wage = 0)
    {
        $token = Str::random(30);
        $newTrans = new DoctorWallet();
        $newTrans->doctor_id = $doctor->id;
        $newTrans->user_id = $doctor->id;
        $newTrans->amount = $amount * -1;
        $newTrans->description = $desc;
        $newTrans->bank_wage = $bank_wage;
        $newTrans->service_wage = 0;
        $newTrans->type = '';
        $newTrans->status = 'paid_decrease';
        $newTrans->service = 'exchange';
        $newTrans->token = $token;
        return $newTrans->save();

    }

    private function increaseGold($doctor, $amount)
    {
        return $doctor->update([
            'gold' => $doctor->gold + $amount
        ]);
    }

    private function decreaseGold($doctor, $amount)
    {
        return $doctor->update([
            'gold' => $doctor->gold + $amount
        ]);
    }

    private function accountBalance($doctor)
    {
        $doctor_id = $doctor->id;
        $paid_increase = $this->calculateWallet($doctor_id, ['paid_increase']);
        $paid_decrease = $this->calculateWallet($doctor_id, ['paid_decrease']);
        $bank_wages = $this->calculateWallet($doctor_id, ['paid_increase', 'paid_decrease', 'pending_decrease'], 'bank_wage');
        $service_wages = $this->calculateWallet($doctor_id, ['paid_increase'], 'service_wage');
        $pending_decrease = $this->calculateWallet($doctor_id, ['pending_decrease']);
        $net = $paid_increase + $paid_decrease - $bank_wages;
        $account_accessible = $net + $pending_decrease - abs($service_wages);
        return $account_accessible;
    }

    private function calculateWallet($doctor_id, $status, $column = 'amount')
    {
        return DoctorWallet::where(['doctor_id' => $doctor_id, 'payment_type' => 'Wallet'])
            ->whereIn('status', $status)->sum($column);
    }

    private function registerExchangeLog($doctor, $exchange_type, $amount_received, $amount_given, $unit_price)
    {
        return;
        return DB::table('exchange_logs')->insert([
            'user_id' => $doctor->id,
            'exchange_type' => $exchange_type,
            'amount_received' => $amount_received,
            'amount_given' => $amount_given,
            'unit_price' => $unit_price,
            'created_at' => Carbon::now()
        ]);
    }

    private function registerWithdrawLog($doctor, $amount, $wage, $sheba, $person)
    {
        return DB::table('withdraw_log')->insert([
            'user_id' => $doctor->id,
            'amount' => $amount,
            'wage' => $wage,
            'sheba' => $sheba,
            'person' => $person,
            'created_at' => Carbon::now()
        ]);
    }
}
