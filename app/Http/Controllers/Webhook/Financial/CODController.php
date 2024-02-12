<?php

namespace App\Http\Controllers\Webhook\Financial;

use App\Model\User\UserCodes;
use Illuminate\Support\Facades\Hash;
use App\Model\Wallet\DoctorWallet;
use App\Model\Doctor\DoctorContract;
use App\SendSMS;
use App\User;
use App\Services\Gateways\src\Zibal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Vandar\Laravel\Facade\Vandar;

class CODController extends Controller
{

    protected $request;
    private $user;
    private $wallet_id;
    private $TERMINAL_ID;
    private $sign;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        require_once(base_path('app/jdf.php'));
        $this->request = $request;
        $this->wallet_id = '1604881';
    }

    public function increase(){

        $signature = (string)\request()->headers->get('signature');

        $ip = \request()->ip();

        if ($signature == '5KMkwSZlATqMTxzkkl1gfaRPDMhT2j' && $ip) {
            $ValidData = $this->validate($this->request, [
                'amount' => 'required',
                'paidAt' => 'required',
                'refNumber' => 'required',
                'cardNumber' => 'required',
                'terminalId' => 'required',
                'posId' => 'required',
                'deliveredAt' => 'required',
            ]);

            $contract = DoctorContract::where([
                'terminal_id' => $this->request->get('terminalId'),
            ])->orderBy('created_at', 'DESC')->first();

            $percent = $contract ? $contract->percent : 0.01;
            $doctorWallet = DoctorWallet::where(['doctor_id' => $contract->user_id,'transId' => $this->request->get('refNumber')])->first();
            if (!$doctorWallet) {
                $newTrans = new DoctorWallet();
                $newTrans->doctor_id = $contract->user_id;
                $newTrans->amount = $this->request->get('amount');
                $newTrans->service_wage = $this->request->get('amount') * $percent;
                $newTrans->bank_wage = 0;
                $newTrans->type = 'increase';
                $newTrans->settlement_type = 'rial';
                $newTrans->payment_type = 'COD';
                $newTrans->status = 'paid_increase';
                $newTrans->transId = $this->request->get('refNumber');
                $newTrans->paid_at = $this->request->get('paidAt');
                $newTrans->transaction_date = $this->request->get('deliveredAt');
                $newTrans->description = $this->request->get('cardNumber');

                $newTrans->save();
            }

            return success_template(['message' => 'success']);
        }
    }


    public function report()
    {
//        dd('l');
        $terminalIds = DoctorContract::where(['category' => 'cod'])->where('terminal_id'  , '!=' , null)->pluck( 'terminal_id','user_id' );

//        dd($terminalIds);
        foreach ($terminalIds as $doctor_id => $terminalId) {
            $contract = DoctorContract::where([
                'user_id' => $doctor_id,
                'terminal_id' => $terminalId,
            ])->orderBy('created_at','DESC')->first();
            $percent = $contract ? $contract->percent : 0.01;

            $gateway = new Zibal();

            $Y = Carbon::now()->subDay(1)->format('Y');
            $m = Carbon::now()->subDay(1)->format('m');
            $d = Carbon::now()->subDay(1)->format('d');
            $date = gregorian_to_jalali($Y,$m,$d);

            $data = $date[0].$date[1].$date[2]. '-' . $contract->terminal_id;
//            $data = '14011116-' . $contract->terminal_id;

            $result = $gateway->CODInquiry($data,$this->wallet_id);
//dump($result);
            if($result->result == 1 && isset($result->data->data)){
                $doctorWallet = DoctorWallet::where(['doctor_id' => $doctor_id,'transId' => $result->data->data])->first();
                if (!$doctorWallet) {
                    $newTrans = new DoctorWallet();
                    $newTrans->doctor_id = $doctor_id;
                    $newTrans->amount = $result->data->amount;
                    $newTrans->service_wage = $result->data->amount * $percent;
                    $newTrans->transId = $result->data->data;
                    $newTrans->bank_wage = 0;
                    $newTrans->type = 'increase';
                    $newTrans->settlement_type = 'rial';
                    $newTrans->payment_type = 'COD';
                    $newTrans->status = 'paid_increase';
                    $newTrans->paid_at = Carbon::now()->subDay(1)->format('Y-m-d H:i:s');
                    $newTrans->description = $result->data->data . ' گزارش تجمیعی روزانه دستگاه پوز ';

                    $newTrans->save();
                }
            }
        }
    }

    public function decrease()
    {

        $ValidData = $this->validate($this->request, [
            'amount' => 'required_if:settlement_type,rial|numeric|min:5000000|max:2000000000',
            'settlement_type' => 'required|in:rial,currency_remit,currency_cash,other',
            'account_sheba' => 'required_if:settlement_type,rial|numeric',
            'wallet_address' => 'required_if:settlement_type,other|string',
            'wallet_address_QR' => 'nullable',
            'code' => 'nullable',
            'description' => 'nullable',
        ],
            [
                'amount.required' => 'ورود مبلغ الزامی است',
                'amount.numeric' => 'مبلغ نامعتبر',
                'account_sheba.numeric' => 'شبا نامعتبر',
                'amount.min' => 'حداقل مبلغ ۵،۰۰۰،۰۰۰ ریال',
                'amount.max' => 'حداکثر مبلغ ۲،۰۰۰،۰۰۰،۰۰۰ ریال',
                'settlement_type.in' => 'نوع تسویه نا معتبر'
            ]);

        $doctor = auth()->user();

        $origin = (string)\request()->headers->get('origin');

        if ($origin !== "https://sbm24.com/"
            && $origin !== "https://sbm24.com"
            && $origin !== "https://cp.sbm24.com/"
            && $origin !== "https://cptest.sbm24.com/"
            && $origin !== "https://cp.sbm24.com"
            && $origin !== "http://localhost:3000"
            && $origin !== "https://cptest.sbm24.com") {
            $ValidData = $this->validate($this->request, [
                'code' => 'required',
            ],
                [
                    'code.required' => 'کد الزامی است',
                ]);
            $SentCode = change_number(\request()->input('code'));
            $code = UserCodes::where('mobile', $doctor->mobile)->first();
            if (!$code)
                return error_template('شماره موبایل یافت نشد');
            if (!Hash::check($SentCode, $code->code)) {
                return error_template('کد وارد شده صحیح نیست');
            }
        }
        $settlement_type = $this->request->get('settlement_type');

        if (in_array(jdate('d') ,[28,29,30,31]) && $settlement_type == 'rial'){
            return error_template('امکان برداشت ریالی از روز ۲۸ تا پایان هر ماه امکان پذیر نیست');
        }

        $account_id = $this->request->get('account_sheba') ?? $this->request->get('wallet_address');
        $amount = $this->request->get('amount');

        $payment = new Zibal();

        $bank_wage = $payment->wageInquiry((int)$amount,$this->wallet_id)->data->wage;

        $balance = json_decode($this->accountBalance()->getContent())->data;

        if (($settlement_type == 'rial' && abs($amount) > ($balance->account_accessible - $bank_wage))) {
            return error_template('مبلغ وارد شده بیشتر از مبلغ قابل برداشت است');
        }

        if (($settlement_type == 'other' && abs($amount) > ($balance->non_rial_account_accessible))){
            return error_template('مبلغ وارد شده بیشتر از مبلغ قابل برداشت است');
        }

        $token = Str::random(30);

        $newTrans = new DoctorWallet();

        if($settlement_type == 'rial') {

            $account_info = json_decode($this->shebaInquiry()->getContent());

            if ($account_info->status == 'success') {
                $newTrans->account_id_info = json_encode($account_info->data->data);
            }
        }
        if (\request()->hasFile('wallet_address_QR')) {
            $file = $this->uploadImageCt('wallet_address_QR' );
            $newTrans->account_id_QR = $file;
        }

        $newTrans->doctor_id = $doctor->id;
        $newTrans->amount = -abs($amount);
        $newTrans->type = 'decrease';
        $newTrans->payment_type = 'COD';
        $newTrans->status = 'pending_decrease';
        $newTrans->account_id = $account_id;
        $newTrans->settlement_type = $settlement_type;
        $newTrans->description = $this->request->get('description',null);
        $newTrans->bank_wage = $bank_wage;
        $newTrans->token = $token;

        $newTrans->save();

        $operators = ['09123358157','09124091863','09039458207','09201941196'];

        if($settlement_type == 'rial') {
            try {
                $data = [
                    'amount' => abs($newTrans->amount),
                    'bankAccount' => 'IR' . $newTrans->account_id,
                    'wallet_id' => $this->wallet_id,
                    'description' => $newTrans->description,
                    'uniqueCode' => (string)uniqid('', false),
                ];

                $result = $payment->checkout($data);

                if ($result->result == 1) {
                    $newTrans->status = 'paid_decrease';
                    $newTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newTrans->transId = $result->data->id;
                    $newTrans->receipt_link = $result->data->receipt;
                    $newTrans->save();
                }

            } catch (\Exception $e) {

            }
            $start_hours = jdate('H:i', strtotime($newTrans->paid_at));
            $start_date = jdate('d-m-Y', strtotime($newTrans->paid_at));
            $balance = json_decode($this->accountBalance()->getContent())->data;

            SendSMS::send($doctor->mobile,"CheckoutWalletDr",[
                "token" => number_format(abs($newTrans->amount)),
                "token2" => $start_date . ' ساعت: ' . $start_hours,
                "token3" => number_format($balance->account_accessible),
            ]);
        }elseif('other'){
            try {
                $payment = new Zibal();
                $data = [
                    'amount' => abs($newTrans->amount),
                    'bankAccount' => 'IR120190000000117636383004',
                    'wallet_id' => $this->wallet_id,
                    'description' => $newTrans->description,
                    'delay' => '-1',
                    'uniqueCode' => (string)uniqid('', false),
                ];

                $result = $payment->checkout($data);

                if ($result->result == 1) {
                    $newTrans->status = 'paid_decrease';
                    $newTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newTrans->transId = $result->data->id;
                    $newTrans->receipt_link = $result->data->receipt;
                    $newTrans->save();
                }

            } catch (\Exception $e) {

            }
            $start_hours = jdate('H:i', strtotime($newTrans->paid_at));
            $start_date = jdate('d-m-Y', strtotime($newTrans->paid_at));
            $balance = json_decode($this->accountBalance()->getContent())->data;

            foreach ($operators as $operator) {
                SendSMS::send($operator, "WalletCryptoRequest", [
                    "token" => $doctor->fullname . ' - ' . $doctor->mobile,
                    "token2" => $newTrans->transId,
                    "token3" => number_format(abs($newTrans->amount)),
                    "token10" => $start_date . ' ساعت: ' . $start_hours,
                    "token20" => $newTrans->account_id,
                ]);
            }
            SendSMS::send($doctor->mobile,"CheckoutWalletDr",[
                "token" => number_format(abs($newTrans->amount)),
                "token2" => $start_date . ' ساعت: ' . $start_hours,
                "token3" => number_format($balance->account_accessible),
            ]);

            return success_template(['message' => 'درخواست شما با موفقیت ثبت گردید، بین ساعت 18 الی 20 روز کاری بعدی به آدرس ولت (کیف پول)تتر اعلام شده توسط شما واریز می گردد']);
        }else{
            try {
                $payment = new Zibal();
                $data = [
                    'amount' => abs($newTrans->amount),
                    'bankAccount' => 'IR120190000000117636383004',
                    'wallet_id' => $this->wallet_id,
                    'description' => $newTrans->description,
                    'delay' => '-1',
                    'uniqueCode' => (string)uniqid('', false),
                ];

                $result = $payment->checkout($data);

                if ($result->result == 1) {
                    $newTrans->status = 'paid_decrease';
                    $newTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newTrans->transId = $result->data->id;
                    $newTrans->receipt_link = $result->data->receipt;
                    $newTrans->save();
                }

            } catch (\Exception $e) {

            }
            $start_hours = jdate('H:i', strtotime($newTrans->paid_at));
            $start_date = jdate('d-m-Y', strtotime($newTrans->paid_at));
            $balance = json_decode($this->accountBalance()->getContent())->data;

            foreach ($operators as $operator) {
                SendSMS::send($operator, "WalletCurrencyRequest", [
                    "token" => $doctor->fullname,
                    "token2" => $doctor->mobile,
                    "token3" => number_format(abs($newTrans->amount)),
                    "token10" => $start_date . ' ساعت: ' . $start_hours,
                    "token20" => $newTrans->transId,
                ]);
            }
            SendSMS::send($doctor->mobile,"CheckoutWalletDr",[
                "token" => number_format(abs($newTrans->amount)),
                "token2" => $start_date . ' ساعت: ' . $start_hours,
                "token3" => number_format($balance->account_accessible),
            ]);
            return success_template(['message' => 'درخواست شما با موفقیت ثبت گردید منتظر تماس کارشناسان ارزی ما بمانید.']);
        }
        return success_template(['message' => 'درخواست شما با موفقیت ثبت شد']);
    }

    public function overview($status){
        $doctor_id = auth()->id();

        $day = Carbon::now()->format('Y-m-d');
        $week = Carbon::now()->subWeeks(1)->format('Y-m-d');
        $month = Carbon::now()->subMonths(1)->format('Y-m-d');

        $doctorWallet = DoctorWallet::where([
            'doctor_id'=>$doctor_id , 'payment_type'=>'COD' , 'status' => $status
        ]);


        $countMonth = $doctorWallet->whereDate('paid_at', '>=', $month)->count();
        $amountMonth = $doctorWallet->whereDate('paid_at', '>=', $month)->sum('amount');

        $countWeek = $doctorWallet->whereDate('paid_at', '>=', $week)->count();
        $amountWeek = $doctorWallet->whereDate('paid_at', '>=', $week)->sum('amount');

        $countDay = $doctorWallet->whereDate('paid_at' , $day)->count();
        $amountDay = $doctorWallet->whereDate('paid_at' , $day)->sum('amount');

        return success_template([
            'day_count' => $countDay,
            'day_amount' => $amountDay,
            'week_count' => $countWeek,
            'week_amount' => $amountWeek,
            'month_count' => $countMonth,
            'month_amount' => $amountMonth,
        ]);

    }

    public function updateCOD($id)
    {
        $ValidData = $this->validate($this->request, [
            'wallet_address' => 'required',
        ], [
            'wallet_address.required' => ' آدرس کیف پول الزامی است',
        ]);

        $wallet = DoctorWallet::where('doctor_id',auth()->id())->findOrFail($id);
        $wallet->account_id = $this->request->get('wallet_address');
        $wallet->save();
        return success_template(['message' => 'آدرس کیف پول با موفقیت ویرایش شد.']);
    }

    public function accountBalance($id = null)
    {
        $doctor_id = auth()->id() ?? $id;

        $paid_increase = $this->calculateWallet($doctor_id,['paid_increase']);

        $paid_decrease = $this->calculateWallet($doctor_id,['paid_decrease']);

        $bank_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease','pending_decrease'] , 'bank_wage');

        $service_wages = $this->calculateWallet($doctor_id,['paid_increase'] , 'service_wage');

        $pending_decrease = $this->calculateWallet($doctor_id,['pending_decrease']);

        $net = $paid_increase + $paid_decrease - $bank_wages;

        $account_accessible = $net + $pending_decrease - abs($service_wages);
        $bank_settlement_wage = $this->calculateBankSettlementWage($account_accessible);

        return success_template([
            'account_balance' => abs($net - abs($service_wages) ),
            'account_accessible' => abs($account_accessible - $bank_settlement_wage),
            'non_rial_account_accessible' => abs($account_accessible - $bank_settlement_wage),
            'pending_decrease' => abs($pending_decrease)
        ]);
    }

    public function calculateBankSettlementWage($amount, $checkoutDelay = 0)
    {
        if($checkoutDelay == 0){
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.002;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        }elseif($checkoutDelay == -1){
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.003;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        }
        return $bankWage;
    }

    public function calculateWallet($doctor_id,$status,$column = 'amount')
    {
        return DoctorWallet::where(['doctor_id'=>$doctor_id , 'payment_type' => 'COD'])
            ->whereIn('status',$status)->sum($column);
    }

    public function showInvoice()
    {
        $request = DoctorWallet::with(['user:id,name,family,nationalcode','doctor:id,fullname'])
            ->where('token' , $this->request->token)->first();

        return success_template($request);
    }

    public function shebaInquiry()
    {
        $ValidData = $this->validate($this->request, [
            'account_sheba' => 'required|digits:24',
        ], [
            'account_sheba.required' => 'ورود شبا الزامی است',
            'account_sheba.digits' => 'فرمت شماره شبای وارد شده معتبر نیست شماره شبا باید۲۴رقم و بدون فاصله باشد',
        ]);

        $account_sheba = 'IR'.$this->request->get('account_sheba');

        $gateway = new Zibal();

        $result = $gateway->shebaInquiry($account_sheba);

        if($result->result == 1){
            return success_template($result);
        }
        return error_template('شماره شبای وارد شده معتبر نیست شماره شبا باید۲۴رقم و بدون فاصله باشد');
    }

    public function shebaList()
    {
        $doctor_id = auth()->id();

        $shebaList = DoctorWallet::where(
            ['type' => 'decrease','doctor_id' => $doctor_id, 'settlement_type' => 'rial']
        )->where('account_id_info' , '!=' , null)
            ->select('account_id',
                'account_id_info')->groupBy('account_id')
            ->orderByDesc('created_at')->get();

        $result = [];
        foreach ($shebaList as $item) {
            $result[] = ['account_id' => $item->account_id, 'account_id_info' => json_decode($item->account_id_info)];
        }

        return success_template($result);

    }

    public function updateInvoice()
    {
        $ValidData = $this->validate($this->request,[
            'name' => 'required',
            'family' => 'required',
            'token' => 'required',
            'nationalcode' => 'required',
        ],
            [
                'name.required' => 'ورود نام الزامی است',
                'family.required' => ' نام خانوادگی الزامی است',
                'nationalcode.required' => 'ورود کدملی الزامی است'
            ]);


        $request = DoctorWallet::where('token' , $this->request->get('token'))->first();

        $user = User::find($request->user_id);

        $user->name = $this->request->get('name');
        $user->family = $this->request->get('family');
        $user->nationalcode = $this->request->get('nationalcode');
        $user->save();

        return success_template(['pay_link' => url('payment/service/' . $request->token)]);

    }

    public function gateway(){

        $factorNumber = rand(111111, 999999);

        $request = DoctorWallet::where('token' , $this->request->token)->where('status','pending_increase')->first();
        if(!$request) return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);

        $user = User::where('id' , $request->user_id)->first();

        $CallbackURL = url('payment/increase_service/' . $this->request->token);


        $pay = new Zibal();
//        $pay = new PayStar();
        $data['amount'] = (int)$request->amount;
        $data['description'] = $user->description;
        $data['mobile'] = $user->mobile;
        $data['callback'] = $CallbackURL;
        $data['merchant_key'] = "6255537618f93472e4a35cd9"; //For Zibal
        $data['factorNumber'] = $factorNumber;

        $pay->pay2($data);

    }

    public function verify($token){

        $success = request()->get('success');
        $orderId = request()->get('orderId');
        $trackId = request()->get('trackId');

        if ($success) {

            $parameters = array(
                "merchant" => "6255537618f93472e4a35cd9",//required
                "trackId" => $trackId,//required
            );

            $result = $this->prepare('https://gateway.zibal.ir/v1/verify', $parameters);

            $request = DoctorWallet::where('token' , $token)->first();
            if(!$request){ return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);}


            $user = User::where('id' , $request->user_id)->first();

            if ($result->result == 100){

                $request->status = 'paid_increase';
                $request->factorNumber = $result->orderId;
                $request->transId = $result->refNumber;
                $request->paid_at = Carbon::now()->format('Y-m-d H:i:s');

                $request->save();

                $start_hours = jdate('H:i', strtotime($request->paid_at));
                $start_date = jdate('d-m-Y', strtotime($request->paid_at));
                $doctor = User::where('id',$request->doctor_id)->first();
                $balance = json_decode($this->accountBalance($doctor->id)->getContent())->data;

                SendSMS::send($doctor->mobile,"IncreaseWallet",[
                    "token" => number_format(abs($request->amount)),
                    "token2" => $start_date . ' ساعت: ' . $start_hours,
                    "token3" => $user->fullname,
                    "token10" => number_format($balance->account_accessible),
                ]);
//                return redirect(get_ev('cp_live') . '/user/payment_success/'.$this->request->token);
                return redirect('https://sbm24.com/payment_success?token='.$this->request->token);
            }

            $request->status = 'pending_increase';
            $request->save();

            return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);
        }
        return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);

    }


    function prepare($url, $parameters,$header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $header
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
}
