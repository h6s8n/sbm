<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Model\User\UserCodes;
use App\Model\Wallet\DoctorWallet;
use App\Model\Doctor\DoctorContract;
use App\SendSMS;
use App\User;
use App\Services\Gateways\src\Zibal;
use App\Services\Gateways\src\PayStar;
use App\Services\Gateways\src\ZarrinPal;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use SoapClient;
use Vandar\Laravel\Facade\Vandar;
use Illuminate\Support\Facades\DB;



class WalletController extends Controller
{

    protected $request;
    private $TERMINAL_ID;
    private $sign;
    private $zibal_merchant;
    private $zarrin_merchant;
    private $zarrin_terminal_id;
    private $wallet_id;


    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        require_once(base_path('app/jdf.php'));
        $this->request = $request;
        $this->wallet_id = '1629021';
        $this->TERMINAL_ID = "67m4630gm9wdzn";
        //      visit  $this->zarrin_merchant = '2f27b240-b085-42d9-815a-04c860d6e39f';
        //        $this->zibal_merchant = '6415b50718f934455fa13b4a';
        //        $this->zibal_merchant = '64522ebf18f9342ceb56ab3f';
        $this->zibal_merchant = '';
        $this->zarrin_merchant = '362e7e23---a204-d604a8e5beaa';
        $this->zarrin_terminal_id = '';
        $this->sign = "";
    }

    public function increase()
    {
		


        $ValidData = $this->validate(
            $this->request,
            [
                'amount' => 'required|numeric|min:1000|max:1000000000',
                'mobile' => 'required|digits:11|starts_with:09',
                'service' => 'required|in:visit,surgery,other',
                'name' => 'required',
                'family' => 'required',
                'payment_method' => 'required|in:sms,redirect,qr',
                'description' => 'nullable',
            ],
            [
                'mobile.required' => 'ورود شماره همراه الزامی است',
                'mobile.starts_with' => ' شماره همراه نامعتبر است',
                'mobile.digits' => ' شماره همراه نامعتبر است',
                'amount.required' => 'ورود مبلغ الزامی است',
                'name.required' => 'ورود نام الزامی است',
                'family.required' => 'ورود نام خانوادگی الزامی است',
                'amount.min' => 'حداقل مبلغ ۵۰۰،۰۰۰ ریال',
                'amount.max' => 'حداکثر مبلغ ۱،۰۰۰،۰۰۰،۰۰۰ ریال',
                'service.in' => 'خدمت نا معتبر'
            ]
        );

        $doctor = auth()->user();
        $user = User::whereMobile($this->request->get('mobile'))->first();
        $contract = DoctorContract::where([
            'user_id' => $doctor->id,
        ])->orderBy('created_at', 'DESC')->first();
        $percent = $contract ? $contract->percent : 0.01;
        if (!$user) {
            $user_token = str_random(6);
            $username = str_random(6);
            $password = str_random(6);
            $user = User::create([
                'token' => $user_token,
                'username' => $username,
                'name' => $this->request->get('name', NULL),
                'family' => $this->request->get('family', NULL),
                'fullname' => $this->request->get('name', NULL) . ' ' . $this->request->get('family', NULL),
                'password' => $password,
                'mobile' => $this->request->get('mobile'),
                'approve' => 2,
                'visit_condition' => null
            ]);
        } else {
            $user->name = $this->request->get('name', null);
            $user->family = $this->request->get('family', null);
            $user->fullname = $this->request->get('name') . ' ' . $this->request->get('family', null);
            $user->save();
        }
        $token = Str::random(30);
        $expiration = Carbon::now()->addHours(8);
        $fa_date = jdate('Y-m-d ساعت H:i', strtotime($expiration));
        $newTrans = new DoctorWallet();
        $newTrans->doctor_id = $doctor->id;
        $newTrans->expiration = $expiration;
        $newTrans->user_id = $user->id;
        $newTrans->amount = $this->request->get('amount');
        $newTrans->bank_wage = $newTrans->bank_wage = 74900;
        $newTrans->service_wage = $this->request->get('amount') * $percent;
        $newTrans->type = 'increase';
        $newTrans->status = 'pending_increase';
        $newTrans->service = $this->request->get('service');
        $newTrans->description = $this->request->get('description', null);
        $newTrans->token = $token;
        $newTrans->save();
        $mapping = [
            'visit' => 'ویزیت',
            'surgery' => 'عمل جراحی',
            'other' => 'خدمات سلامت'
        ];

        $service = $mapping[$this->request->get('service')];
        $pay_link = 'https://hesab.link/?token=' . $token;
        if ($this->request->get('payment_method') == 'sms') {
            $params = array(
                "token" => $pay_link,
                "token2" => $doctor->fullname,
                "token3" => $fa_date,
                "token10" => $fa_date,
            );

            $am = $this->request->get('amount');
            $expiration1 = Carbon::now();
            $fa_date1 = jdate('Y-m-d ساعت H:i', strtotime($expiration1));
            $params1 = array(
                "token" => $am,
                "token2" => $fa_date1 . " توسط " . $user->fullname,
                "token3" =>  $doctor->fullname,
            );
            SendSMS::send($user->mobile, 'payLink', $params);

            return success_template(['message' => 'لینک پرداخت به بیمار پیامک شد.']);
        }
        return success_template(['link' => $pay_link]);
    }

    public function decrease()
    {
	


        $ValidData = $this->validate(
            $this->request,
            [
                'amount' => 'required_if:settlement_type,rial|numeric|min:10000|max:2000000000',
                'settlement_type' => 'required|in:rial,currency_remit,currency_cash,other',
                'account_sheba' => 'required_if:settlement_type,rial|numeric',
                'wallet_address' => 'required_if:settlement_type,other|string',
                'wallet_address_QR' => 'nullable',
                'description' => 'nullable',
            ],
            [
                'amount.required' => 'ورود مبلغ الزامی است',
                'code.required' => 'ورود کد الزامی است',
                'amount.numeric' => 'مبلغ نامعتبر',
                'account_sheba.numeric' => 'شبا نامعتبر',
                'amount.min' => 'حداقل مبلغ ۵،۰۰۰،۰۰۰ ریال',
                'amount.max' => 'حداکثر مبلغ ۲،۰۰۰،۰۰۰،۰۰۰ ریال',
                'settlement_type.in' => 'نوع تسویه نا معتبر'
            ]
        );
        $doctor = auth()->user();
		
		
        $origin = (string)\request()->headers->get('origin');
        if (
            $origin !== "https://sbm24.com/"
            && $origin !== "https://sbm24.com"
            && $origin !== "https://cp.sbm24.com/"
            && $origin !== "https://cptest.sbm24.com/"
            && $origin !== "https://cp.sbm24.com"
            && $origin !== "http://localhost:3000"
            && $origin !== "https://cptest.sbm24.com"
        ) {
            $ValidData = $this->validate($this->request, [
                'code' => 'required',
            ], ['code.required' => 'کد الزامی است',]);
            $SentCode = change_number(\request()->input('code'));
            $code = UserCodes::where('mobile', $doctor->mobile)->first();

            if (!$code)
                return error_template('شماره موبایل یافت نشد');
            if (!Hash::check($SentCode, $code->code)) {
                return error_template('کد وارد شده صحیح نیست');
            }
        }

      

        $settlement_type = $this->request->get('settlement_type');
        $account_id = $this->request->get('account_sheba');
        $amount = $this->request->get('amount');
		   $max_withdraw_amount = DoctorWallet::selectRaw('-1 * SUM(amount) AS total_amount')
    ->where('status', 'paid_decrease')
    ->where('paid_at', '>=', DB::raw('CURRENT_DATE'))
    ->where('doctor_id', $doctor->id)
    ->value('total_amount');


		
		$litewage = $amount / 1000000000;
		
		
				$bank_wage = 75000;
		
        $balance = json_decode($this->accountBalance()->getContent())->data;
        if (($settlement_type == 'rial' && abs($amount) > ($balance->account_accessible - $bank_wage))) {
            return error_template('مبلغ وارد شده بیشتر از مبلغ قابل برداشت است');
        }
		if($max_withdraw_amount + abs($amount) > 2000000000){
            return error_template("سقف برداشت فعال است.برداشت شما : " +  $max_withdraw_amount);
        }
        if (($settlement_type == 'other' && abs($amount) > ($balance->non_rial_account_accessible))) {
            return error_template('مبلغ وارد شده بیشتر از مبلغ قابل برداشت است');
        }
        $token = Str::random(30);
        $newTrans = new DoctorWallet();
        if (\request()->hasFile('wallet_address_QR')) {
            $file = $this->uploadImageCt('wallet_address_QR');
            $newTrans->account_id_QR = $file;
        }

        if ($settlement_type == 'rial') {
            $account_info = json_decode($this->shebaInquiry()->getContent());
            if ($account_info->status == 'success') {
                $newTrans->account_id_info = json_encode($account_info->data->data);
            }
        }
        $newTrans->doctor_id = $doctor->id;
        $newTrans->amount = -abs($amount);
        $newTrans->type = 'decrease';
        $newTrans->status = 'pending_decrease';
        $newTrans->account_id = $account_id;
        $newTrans->settlement_type = $settlement_type;
        $newTrans->description = $this->request->get('description', null);
        $newTrans->bank_wage = $bank_wage;
        $newTrans->token = $token;
        $newTrans->save();
        $operators = ['09123358157', '09124091863', '09039458207', '09201941196'];
		
		  $previousTrans = DoctorWallet::where('doctor_id', $doctor->id)
                ->where('amount', -abs($amount))
                ->where('type', 'decrease')
                ->where('status', 'pending_decrease')
                ->where('account_id', $account_id)
                ->where('settlement_type', $settlement_type)
                ->where('description', $this->request->get('description', null))
                ->where('bank_wage', $bank_wage)
                ->where('token', $token)
                ->first();
		
		
		
		
		
		
		
		
		
        if ($settlement_type == 'rial') {
            try {
                $payemnt = $this->paystarWithdraw('IR' . $account_id, abs($newTrans->amount));

                if ($payemnt->ok) {
					
					$previousTrans->status = 'paid_decrease';
					$previousTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
					$previousTrans->transId = $payemnt->id;
                    $previousTrans->receipt_link = 'https://my.paystar.ir/wallets/settlement-receipt/' . $payemnt->id;
                    $previousTrans->save();
					
				
                } else {
                    return  error_template($payemnt->message_fa);
                }
            } catch (\Exception $e) {
                return  error_template($e->getMessage());
            }
            $this->sendSMS($doctor->mobile, $newTrans);
        } elseif ('other') {

            try {
                $payemnt = $this->paystarWithdraw('IR730120020000009536248302', abs($newTrans->amount));

                if ($payemnt->ok) {
                    $newTrans->status = 'paid_decrease';
                    $newTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newTrans->transId = $payemnt->id;
                    $newTrans->receipt_link = 'https://my.paystar.ir/wallets/settlement-receipt/' . $payemnt->id; 
                    $newTrans->save();
                } else {
                    return  error_template($payemnt->message_fa);
                }
            } catch (\Exception $e) {
            }
            $start_hours = jdate('H:i', strtotime($newTrans->paid_at));
            $start_date = jdate('d-m-Y', strtotime($newTrans->paid_at));
            $balance = json_decode($this->accountBalance()->getContent())->data;

            foreach ($operators as $operator) {
                SendSMS::send($operator, "WalletCryptoRequest", [
                    "token" => $doctor->fullname . ' - ' . $doctor->mobile,
                    "token2" => $newTrans->receipt_link,
                    "token3" => number_format(abs($newTrans->amount)),
                    "token10" => $start_date . ' ساعت: ' . $start_hours,
                    "token20" => $newTrans->account_id,
                ]);
            }
            $this->sendSMS($doctor->mobile, $newTrans);

            return success_template(['message' => 'درخواست شما با موفقیت ثبت گردید، بین ساعت 18 الی 20 روز کاری بعدی به آدرس ولت (کیف پول)تتر اعلام شده توسط شما واریز می گردد']);
        } else {
            try {

                $payemnt = $this->paystarWithdraw('IR170560082181004061441001', abs($newTrans->amount));

                if ($payemnt->ok) {
                    $newTrans->status = 'paid_decrease';
                    $newTrans->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newTrans->transId = $payemnt->id;
                    $newTrans->receipt_link = 'https://my.paystar.ir/wallets/settlement-receipt/' . $payemnt->id; // $result->data->receipt;
                    $newTrans->save();
                } else {
                    return  error_template($payemnt->message_fa);
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
                    "token30" => $newTrans->receipt_link,
                ]);
            }
            SendSMS::send($doctor->mobile, "CheckoutWalletDr", [
                'token'=> 'مبلغ'. number_format($newTrans->amount).'ریال در تاریخ'.$start_date . ' ساعت: ' . $start_hours,
                "token2" => $newTrans->receipt_link,
                "token3" => number_format($balance->account_accessible),
                

            ]);
            return success_template(['message' => 'درخواست شما با موفقیت ثبت گردید منتظر تماس کارشناسان ارزی ما بمانید.']);
        }
        return success_template(['message' => 'درخواست شما با موفقیت ثبت شد']);
    }

    public function rial_decrease(DoctorWallet $wallet)
    {
        

        $cl = new Client(['headers' => ["Accept" => "application/json",]]);
        $bank_account = json_decode($cl->get(route("graphql", ['type' => 'bank_account_add', 'data' => 'IR' . $wallet->account_id]))->getBody()->getContents(), true);

        if ($bank_account['errors']) {
            $bank_accounts = json_decode($cl->get(route("graphql", ['type' => 'bank_accounts']))->getBody()->getContents(), true)['data']['BankAccounts'];

            foreach ($bank_accounts as $account) {
                if ($account['iban'] == 'IR' . $wallet->account_id) {
                    $bank_account_id = $account['id'];
                }
            }
        } else {
            $bank_account_id = $bank_account['data']['0']['BankAccountAdd']['id'];
        }

        $payout = json_decode($cl->get(route("graphql", [
            'type' => 'payout', 'data' =>
            $this->zarrin_terminal_id . '&' . $bank_account_id . '&' . abs($wallet->amount)
        ]))->getBody()->getContents(), true);

        if (isset($payout['errors'])) {
            return error_template($payout['errors']);
        } else {

            $result = $payout['data']['PayoutAdd'];
            $wallet->status = 'paid_decrease';
            $wallet->paid_at = Carbon::now()->format('Y-m-d H:i:s');
            $wallet->transId = $result['id'];
            $wallet->receipt_link = 'https://next.zarinpal.com/payout/receipt/' . $result['url_code'];
            $wallet->save();
        }

   

    }

    public function other_decrease(DoctorWallet $wallet)
    {
        try {

            $cl = new Client(['headers' => ["Accept" => "application/json",]]);
            $bank_account = json_decode($cl->get(route("graphql", ['type' => 'bank_account_add', 'data' => 'IR170560082181004061441001']))->getBody()->getContents(), true);

            if ($bank_account['errors']) {
                $bank_accounts = json_decode($cl->get(route("graphql", ['type' => 'bank_accounts']))->getBody()->getContents(), true)['data']['BankAccounts'];

                foreach ($bank_accounts as $account) {
                    if ($account['iban'] == 'IR170560082181004061441001') {
                        $bank_account_id = $account['id'];
                    }
                }
            } else {
                $bank_account_id = $bank_account['data']['0']['BankAccountAdd']['id'];
            }

            $payout = json_decode($cl->get(route("graphql", [
                'type' => 'instant_payout_add', 'data' =>
                $this->zarrin_terminal_id . '&' . $bank_account_id . '&' . abs($wallet->amount)
            ]))->getBody()->getContents(), true);

            if (isset($payout['errors'])) {
            } else {
                $result = $payout['data']['PayoutAdd'];
                $wallet->status = 'paid_decrease';
                $wallet->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                $wallet->transId = 'https://next.zarinpal.com/payout/receipt/' . $result['id'];
                $wallet->receipt_link = $result['url_code'];
                $wallet->save();
            }
        } catch (\Exception $e) {
        }
    }


    public function sendSMS($to, $data)
    {
        $start_hours = jdate('H:i', strtotime($data->paid_at));
        $start_date = jdate('d-m-Y', strtotime($data->paid_at));
        $balance = json_decode($this->accountBalance()->getContent())->data;

      
		 SendSMS::send($to, "CheckoutWalletDr", [
                'token'=> 'مبلغ'. number_format($data->amount).'ریال در تاریخ'.$start_date . ' ساعت: ' . $start_hours,
                "token2" => $data->receipt_link,
                "token3" => number_format($balance->account_accessible),
                

            ]);
		
    }

    public function zibal_decrease(DoctorWallet $newTrans)
    {
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
            return  error_template($e->getMessage());
        }
    }
    public function overview($status)
    {
        $doctor_id = auth()->id();

        $day = Carbon::now()->format('Y-m-d');
        $week = Carbon::now()->subWeeks(1)->format('Y-m-d');
        $month = Carbon::now()->subMonths(1)->format('Y-m-d');

        $doctorWallet = DoctorWallet::where([
            'doctor_id' => $doctor_id, 'payment_type' => 'Wallet', 'status' => $status
        ]);


        $countMonth = $doctorWallet->whereDate('paid_at', '>=', $month)->count();
        $amountMonth = $doctorWallet->whereDate('paid_at', '>=', $month)->sum('amount');

        $countWeek = $doctorWallet->whereDate('paid_at', '>=', $week)->count();
        $amountWeek = $doctorWallet->whereDate('paid_at', '>=', $week)->sum('amount');

        $countDay = $doctorWallet->whereDate('paid_at', $day)->count();
        $amountDay = $doctorWallet->whereDate('paid_at', $day)->sum('amount');

        return success_template([
            'day_count' => $countDay,
            'day_amount' => $amountDay,
            'week_count' => $countWeek,
            'week_amount' => $amountWeek,
            'month_count' => $countMonth,
            'month_amount' => $amountMonth,
        ]);
    }

    public function updateWallet($id)
    {
        $ValidData = $this->validate($this->request, [
            'wallet_address' => 'required',
        ], [
            'wallet_address.required' => ' آدرس کیف پول الزامی است',
        ]);

        $wallet = DoctorWallet::where('doctor_id', auth()->id())->findOrFail($id);
        $wallet->account_id = $this->request->get('wallet_address');
        $wallet->save();
        return success_template(['message' => 'آدرس کیف پول با موفقیت ویرایش شد.']);
    }

    public function accountBalance($id = null)
    {
        $doctor_id = auth()->id() ?? $id;

        $paid_increase = $this->calculateWallet($doctor_id, ['paid_increase']);

        $paid_decrease = $this->calculateWallet($doctor_id, ['paid_decrease']);

        $bank_wages = $this->calculateWallet($doctor_id, ['paid_increase', 'paid_decrease', 'pending_decrease'], 'bank_wage');

        $service_wages = $this->calculateWallet($doctor_id, ['paid_increase'], 'service_wage');

        $pending_decrease = $this->calculateWallet($doctor_id, ['pending_decrease']);
		
		

        $net = $paid_increase + $paid_decrease - $bank_wages;
		
		
		

        $account_accessible = $net + $pending_decrease - abs($service_wages);
		
        $bank_settlement_wage = $this->calculateBankSettlementWage($account_accessible);

return success_template([
    'account_balance' => $net - abs($service_wages),
    'account_accessible' => $account_accessible - abs($bank_settlement_wage),
    'non_rial_account_accessible' => $account_accessible - abs($bank_settlement_wage),
    'pending_decrease' => abs($pending_decrease)
]);
    }

    public function calculateBankSettlementWage($amount, $checkoutDelay = 0)
    {
        if ($checkoutDelay == 0) {
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.002;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        } elseif ($checkoutDelay == -1) {
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.003;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        }
        return $bankWage;
    }

    public function calculateWallet($doctor_id, $status, $column = 'amount')
    {
		
        return DoctorWallet::where(['doctor_id' => $doctor_id, 'payment_type' => 'Wallet'])
            ->whereIn('status', $status)->sum($column);
    }

    public function showInvoice()
    {
        $request = DoctorWallet::with(['user:id,name,family,nationalcode', 'doctor:id,fullname'])
            ->where('token', $this->request->token)->first();

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

        $account_sheba = 'IR' . $this->request->get('account_sheba');

        $gateway = new Zibal();

        $result = $gateway->shebaInquiry($account_sheba);

        if ($result->result == 1) {
            return success_template($result);
        }

        return error_template($result->message);
        //        return error_template('شماره شبای وارد شده معتبر نیست شماره شبا باید۲۴رقم و بدون فاصله باشد');
    }

    public function shebaList()
    {
        $doctor_id = auth()->id();

        $shebaList = DoctorWallet::where(
            ['type' => 'decrease', 'doctor_id' => $doctor_id, 'settlement_type' => 'rial']
        )->where('account_id_info', '!=', null)
            ->select(
                'account_id',
                'account_id_info'
            )->groupBy('account_id')
            ->orderByDesc('created_at')->get();

        $result = [];
        foreach ($shebaList as $item) {
            $result[] = ['account_id' => $item->account_id, 'account_id_info' => json_decode($item->account_id_info)];
        }

        return success_template($result);
    }

    public function updateInvoice()
    {
        $ValidData = $this->validate(
            $this->request,
            [
                'name' => 'required',
                'family' => 'required',
                'token' => 'required',
                'nationalcode' => 'required',
            ],
            [
                'name.required' => 'ورود نام الزامی است',
                'family.required' => ' نام خانوادگی الزامی است',
                'nationalcode.required' => 'ورود کدملی الزامی است'
            ]
        );

        $request = DoctorWallet::where('token', $this->request->get('token'))->first();

        $user = User::find($request->user_id);

        $user->name = $this->request->get('name');
        $user->family = $this->request->get('family');
        $user->nationalcode = $this->request->get('nationalcode');
        $user->save();

        return success_template(['pay_link' => url('payment/service/' . $request->token)]);
    }


    public function gateway()
    {
        $timenoew = Carbon::now();
        $factorNumber = rand(111111, 999999);
        $request = DoctorWallet::where('token', $this->request->token)->where('status', 'pending_increase')->first();

        $tokens = $this->request->token;

        if (!$request) return redirect('https://sbm24.com/payment_fail?token=' . $tokens);
        if ($request->expiration < $timenoew) {
            return redirect('https://sbm24.com/payment_fail?token=' . $tokens);
        }
        $user = User::where('id', $request->user_id)->first();
        $CallbackURL = url('payment/increase_service/' . $this->request->token);
        $signKey = '4B521318488B867D4E8AB32CB24C8EE24240A8FD206822A04A03CB99882975A8EAEAE12505CEF9FE556F5E1F54F94848CEEE437252896F6813C4870F24410C2F8C901CE3B2CF2D6A99C3AF40DCACDA1E2818BFDB9D3479746943D0102DE9D63CB085AD309FDEFBE4A08FEED89A16A32E43A742A214E04F3E4D2F0D14FAF3A5EF';
        $gatewayId = 'y9dw0nkl2l8yn';
        $pay =  new PayStar($signKey, $gatewayId);

        $payment = $pay->create(
            (int)$request->amount,
            $tokens, // order_id
            $CallbackURL, // callback_url
            $user->mobile,
            'افزایش اعتبار پزشک: ' . $user->fullname // desc
        );

        $payment_url = $pay->createURL($payment);

        if ($payment_url) {
            return redirect()->to($payment_url);
        } else {
            return redirect()->to("https://sbm24.com/payment_fail?token= . $tokens");
        }
        return 'Redirecting ...';
    }

    public function verify($token)
    {

        $payment = (object) request()->all();
        $payInfo = DoctorWallet::where('token', $token)->first();
        if (!$payInfo) return $redirect_fail($token);
        $user = User::where('id', $payInfo->user_id)->first();
        if ($payment->status == "1") {
            $signKey = '4B521318488B867D4E8AB32CB24C8EE24240A8FD206822A04A03CB99882975A8EAEAE12505CEF9FE556F5E1F54F94848CEEE437252896F6813C4870F24410C2F8C901CE3B2CF2D6A99C3AF40DCACDA1E2818BFDB9D3479746943D0102DE9D63CB085AD309FDEFBE4A08FEED89A16A32E43A742A214E04F3E4D2F0D14FAF3A5EF';
            $gatewayId = 'y9dw0nkl2l8yn';
            $pay =  new PayStar($signKey, $gatewayId);
            $verify_payment = $pay->verify((int)$payInfo->amount, $payment);

            if ($verify_payment->status == 1) {
                $payInfo->status = 'paid_increase';
                $payInfo->factorNumber = $payment->order_id; //$result["Authority"];
                $payInfo->transId = $verify_payment->data->ref_num; //$result["RefID"];;
                $payInfo->paid_at = Carbon::now()->format('Y-m-d H:i:s');
                $payInfo->save();

                $start_hours = jdate('H:i', strtotime($payInfo->paid_at));
                $start_date = jdate('d-m-Y', strtotime($payInfo->paid_at));
                $doctor = User::where('id', $payInfo->doctor_id)->first();
                $balance = json_decode($this->accountBalance($doctor->id)->getContent())->data;
                SendSMS::send($doctor->mobile, "IncreaseWallet", [
                    "token" => number_format(abs($payInfo->amount)),
                    "token2" => $start_date . ' ساعت: ' . $start_hours,
                    "token3" => $user->fullname,
                    "token10" => number_format($balance->account_accessible),
                ]);
                return redirect('https://sbm24.com/payment_success?token=' . $this->request->token);
            }else {
                return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);
            }
            $payInfo->status = 'paid_increase';
            $payInfo->save();
        }else {
            return redirect('https://sbm24.com/payment_fail?token=' . $this->request->token);

        }
    }


    function prepare($url, $parameters, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $header
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }


    private function paystarWithdraw($sheba, $amount)
    {
        $result = (new \App\Services\Gateways\src\PayStarWallet1())->pay(
            Str::random(30),
            $amount,
            $sheba,
            '', 
            '', 
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
}
