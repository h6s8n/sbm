<?php

namespace App\Http\Controllers\Api\v1\User\Visit;

use App\Enums\CurrencyEnum;
use App\Enums\VisitTypeEnum;
use App\Http\Controllers\Api\v2\top\TopController;
use App\Http\Controllers\Api\v2\vandar\VandarController;
use App\Model\Discount\Discount;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\Secretary\SpecialSecretary;
use App\SendSMS;
use App\Services\Gateways\src\Zibal;
use App\Services\Gateways\src\ZarrinPal;
use App\Traites\CurrencyChangeTrait;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use SoapClient;
use App\Services\Gateways\src\PayStar;


class ReserveController extends Controller
{
    use CurrencyChangeTrait;

    protected $request;
    private $zarrin_merchant;
    private $zibal_merchant;
    private $sms;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->zarrin_merchant = '2f27b240-b085-42d9-815a-04c860d6e39f';
        $this->zibal_merchant = '63f622a118f9346eef70dea4';
//        $this->zibal_merchant = '641610e418f934762da20635';
        $this->sms = new SMSRepository();
        require(base_path('app/jdf.php'));

    }


    public function GetCalender()
    {

        $ValidData = $this->validate($this->request, [
            'visit' => 'required',
        ]);


        $request = DoctorCalender::join('users', 'users.id', '=', 'doctor_calenders.user_id')
            ->where('doctor_calenders.id', str_replace('SB', '', $ValidData['visit']))
            ->select(
                'doctor_calenders.id as key',
                'doctor_calenders.fa_data',
                'doctor_calenders.data',
                'doctor_calenders.time',
                'doctor_calenders.capacity',
                'doctor_calenders.reservation',
                'doctor_calenders.type',
                'doctor_calenders.price',
                'doctor_calenders.dollar_price',
                'users.fullname as doctor_name',
                'users.picture as doctor_image',
                'users.job_title as doctor_job_title',
                'doctor_calenders.partner_id as partner_id',
                DB::raw('(CASE WHEN doctor_calenders.partner_id=0 THEN 0 ELSE 1 END) as is_partner')
            )->first();
        $user = auth()->user();

        $calender_status = false;
        if ($request) {
            $calender_status = (($request->capacity - $request->reservation) > 0) ? true : false;
            $request['exchange_code'] = '978';
            $request['exchange_title'] = 'EUR';
            $request['exchange_cost'] = $request->dollar_price && $request->dollar_price > 0 ? $request->dollar_price :
                $this->ConvertRialTo(CurrencyEnum::EUR, $request->price);
        }


        return success_template([
            'calender_info' => $request,
            'calender_status' => $calender_status,
            'your_credit' => $user->credit
        ]);

    }

	


	
	
	
    public function ReservePay()
    {
		
		//تغییر اول
        $ValidData = $this->validate($this->request, [
            'key' => 'required',
            'credit_pay' => 'nullable',
            'text' => 'nullable',
            'file' => 'nullable',
        ]);


        $requestCalender = DoctorCalender::join('users', 'users.id', '=', 'doctor_calenders.user_id')
            ->where('doctor_calenders.id', str_replace('SB', '', $ValidData['key']))
            ->select('doctor_calenders.*')->first();

        $user = auth()->user();
        if (\request()->has('name') &&
            \request()->input('name') &&
            \request()->has('family') &&
            \request()->input('family')) {
            $user->name = \request()->input('name');
            $user->family = \request()->input('family');
            $user->fullname = \request()->input('name') . ' ' . \request()->input('family');
            $user->save();
        }
		
        $calender_status = false;
		///test
		 if($requestCalender->expiry < Carbon::now()){
            $requestCalender->pending_user = 0;
            $requestCalender->save();
        }
		///test
        
         if ($requestCalender) {
            $calender_status = (($requestCalender->capacity -$requestCalender->pending_user- $requestCalender->reservation) >= 0) ? true : false;
        }

        if (!$calender_status || !$requestCalender ) return error_template('نمیتوانید رزرو انجام دهید. '.$requestCalender->pending_user.'نفر در صف پرداخت هستند,لطفا دقایقی دیگر تلاش کنید');


        $start_date = date('Y-m-d', strtotime($requestCalender->data));

        if (Carbon::now()->format('Y-m-d') > $start_date)
            return error_template('تاریخ انتخابی صحیح نیست');

        if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date)))
            && (((int)date('H')) == $requestCalender->time) && (((int)date('i')) >= 44)) {
            return error_template('ساعت این ویزیت روبه پایان است لطفا زمان دیگری را انتخاب کنید.');
        }

        $transactionReserve = TransactionReserve::where('user_id', $user->id)
            ->where('calender_id', $requestCalender->id)
            ->where('status', 'paid')->first();

        if ($transactionReserve) {
					    
            return error_template('این نوبت قبلا برای شما ثبت شده است. در صورت موجود مشکل با پشتیبانی تماس بگیرید.');
        }
		

        $requestEvent = EventReserves::where('user_id', $user->id)
            ->where('doctor_id', $requestCalender->user_id)
            ->where('status', 'active')
            ->where('visit_status', 'not_end')
            ->orderBy('created_at', 'desc')->first();


        if ($requestEvent && $requestEvent->visit_status == 'not_end') {
            if ($requestEvent->calender_id == $requestCalender->id) {
		
			
                return error_template('این نوبت قبلا برای شما ثبت شده است. در صورت موجود مشکل با پشتیبانی تماس بگیرید' );
            }
            if ($requestEvent->visit_status == 'not_end') {
                return error_template('در حال حاضر نوبت قبلی شما با این پزشک به پایان نرسیده است،');
            } else {
                return error_template('در حال حاضر نوبت قبلی شما با این پزشک به پایان نرسیده است،');
            }
        }

        $token = Str::random(30);

        $newTrans = new TransactionReserve();
        $newTrans->user_id = $user->id;
        $newTrans->doctor_id = $requestCalender->user_id;
        $newTrans->calender_id = $requestCalender->id;

        if (\request()->has('affiliate_tag')) {
            $affiliate = User::where('token', \request()->get('affiliate_tag'))->first();
            if ($affiliate)
                $newTrans->affiliate_id = $affiliate->id;
        }

        if ($requestEvent) $newTrans->event_id = $requestEvent->id;

        $newTrans->token = $token;
        $discount_amount = 0;

        if (\request()->has('discount') && \request()->input('discount') && $requestCalender->type != 4) {
            $discount = Discount::where('code', \request()->input('discount'))
                ->where('flag', 1)
                ->whereDate('active_till', '>=', Carbon::now()->format('Y-m-d'))
                ->first();
            if ($discount) {
                $temp = TransactionReserve::where('user_id', auth()->id())
                    ->where('discount_id', $discount->id)
                    ->where('status', 'paid')
                    ->first();
                if ($temp)
                    return error_template('شما قبلا از این کد تخفیف استفاده کرده اید');

                switch ($discount->type) {
                    case 1:
                    {
                        $discount_amount = $discount->amount;
                        break;
                    }
                }
                $newTrans->discount_id = $discount->id;
                $newTrans->discount_amount = $discount_amount;
            } else return error_template('کد تخفیف وارد شده صحیح نیست');
        }
        if ($requestCalender->partner_id) {
            $newTrans->amount = $requestCalender->price + OurBeneficiary($requestCalender->type) - $discount_amount;
        } else {
            $newTrans->amount = $requestCalender->price + OurBeneficiary($requestCalender->type) - $discount_amount;
        }
        if ($ValidData['credit_pay'] && $ValidData['credit_pay'] == 'true') {
            $amount_paid = $newTrans->amount - $user->credit;
            if ($user->credit > $newTrans->amount)
                $newTrans->used_credit = $newTrans->amount;
            elseif ($user->credit <= $newTrans->amount)
                $newTrans->used_credit = $user->credit;
            $newTrans->amount_paid = ($amount_paid > 0) ? $amount_paid : 0;
        } else {
            $newTrans->used_credit = 0;
            $newTrans->amount_paid = $newTrans->amount;
        }
         
		
		
		
		
         ++$requestCalender->pending_user;
         $requestCalender->save();
$expiryTime = Carbon::now()->addMinutes(5);
    $requestCalender->expiry = $expiryTime;
    $requestCalender->save();
        $newTrans->save();

//        if ($ValidData['file'] || $ValidData['text']) {
//
//            $new = new Message();
//
//            if ($ValidData['file']) {
//                $file = $this->uploadImageCt('file', 'images');
//                $new->file = $file;
//            }
//
//            $new->user_id = $user->id;
//            $new->audience_id = $requestCalender->user_id;
//            $new->type = "dossierText";
//            $new->message = $ValidData['text'];
//            $new->save();
//
//        }
//        if ($this->request->has('cellphone') && $this->request->input('cellphone')) {
//            $user->phone = $this->request->input('cellphone');
//            $user->save();
//        } else {
//            $user->phone = $user->mobile;
//            $user->save();
//        }

        return success_template(['pay_link' => url('payment/reserve/' . $token)]);

    }

    public function gateway()
    {
        $request = TransactionReserve::where('token', $this->request->token)->first();
		
//        if ($request->user_id == 2796 || $request->user_id == 152827) {
            return $this->zibalPay();

//        }
//        return $this->zarrinPay();



    }

    public function ConditionPay()
    {


        $request = TransactionReserve::where('token', $this->request->token)->first();
        if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $this->request->token);

        $user = User::where('id', $request->user_id)->first();
        $doctor = User::where('id', $request->doctor_id)->first();

        $calender = DoctorCalender::where('id', $request->calender_id)->first();
        if (!$calender) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $this->request->token);


        $MerchantID = get_ev('zarin_key'); //Required
        //$Amount = $request->amount_paid; //Amount will be based on Toman
        $Amount = $request->amount_paid / 10; //Amount will be based on Toman
        //$Authority = $_GET['Authority'];
        $Authority = '';

        $result = [];
        if (isset($_POST) && $_POST['refid']) {

            $Authority = $_POST['refid'];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.payping.ir/v2/pay/verify/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\n  \"refId\": \"{$Authority}\",\n  \"amount\": {$Amount}\n}",
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "authorization: bearer 619d9ab6f1e0f6fbb8134eede2eb5f9f02509e97e7158a3f72d30f95e3743d2e",
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: b6536ed4-afd1-7b23-92ae-c240fd613c2d"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if (!$err) {
                $result = json_decode($response);
            }

        }


        /*if ($_GET['Status'] == 'OK') {

            $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

            $result = $client->PaymentVerification(
                [
                    'MerchantID' => $MerchantID,
                    'Authority' => $Authority,
                    'Amount' => ($Amount / 10),
                ]
            );

        }*/


        if ($result && isset($result->cardNumber)) {

            ++$calender->reservation;
            $calender->save();

            $request->factorNumber = $Authority;
            $request->message = 'OK';
            $request->status = 'paid';
            $request->save();

            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');


            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $calender->user_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }


            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $request->doctor_id;
            $newVisit->calender_id = $request->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();
//            return success_template($request);
            if ($newVisit->id && $request->affiliate_id) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary($calender->type) * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }

            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $this->send_user_notification($doctor, $user, $calender, $newVisit);

            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $this->request->token);

        } else if ($request->status != 'paid') {

            $request->message = 'تراکنش لغو شد';
            $request->status = 'cancel';
            $request->save();

        }


        return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $this->request->token);

    }

    public function vandarPay()
    {

        $factorNumber = rand(111111, 999999) . time();

        $request = TransactionReserve::where('token', $this->request->token)->first();
//        dd($this->request->token);
        if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

        $user = User::where('id', $request->user_id)->first();
        $doctor = User::where('id', $request->doctor_id)->first();

        $calender = DoctorCalender::where('id', $request->calender_id)->first();
        if (!$calender) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        if ($calender->capacity <= $calender->reservation) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

        if ($request->amount_paid <= 0) {
            $request->message = 'هزینه ای دریافت نشد';
            $request->status = 'paid';
            $request->save();

            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $request->doctor_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }
            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $request->doctor_id;
            $newVisit->calender_id = $request->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            ++$calender->reservation;
            $calender->save();

            $request->event_id = $newVisit->id;
            $request->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();

            if ($newVisit->id &&
                $request->affiliate_id &&
                $request->amount > 0) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary($calender->type) * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }
            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $this->send_user_notification($doctor, $user, $calender, $newVisit);

//            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $this->request->token.'?token_room='.$newVisit->token_room);
            return redirect(get_ev('cp_live') . '/user/dashboard?token_room=' . $newVisit->token_room);

        }

        $request->factorNumber = $factorNumber;
        $request->save();

        $pay = new VandarController();
        $Amount = ($request->amount_paid);
        $data['amount'] = $Amount;
        $data['factorNumber'] = $factorNumber;

        $pay->pay($data);
    }

    public function zibalPay()
    {
        $factorNumber = rand(111111, 999999) . time();
        $request = TransactionReserve::where('token', $this->request->token)->first();
        $calender = DoctorCalender::where('id', $request->calender_id)->first();
		 
        if (!$request){
           return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber); 
        } 
        $user = User::where('id', $request->user_id)->first();
        $doctor = User::where('id', $request->doctor_id)->first();
        if (!$calender){  
            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        }
        if ($calender->capacity <= $calender->reservation){   
             return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        }
        if ($request->amount_paid <= 0) {
            $request->message = 'هزینه ای دریافت نشد';
            $request->status = 'paid';
            $request->save();
            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');
            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;
            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }
            $getevents = EventReserves::where('doctor_id', $request->doctor_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {
                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);
            }
            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }
            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $request->doctor_id;
            $newVisit->calender_id = $request->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();
            
      
            ++$calender->reservation;
            $calender->save();
            
            

           

            $request->event_id = $newVisit->id;
            $request->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();

            if ($newVisit->id &&
                $request->affiliate_id &&
                $request->amount > 0) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary($calender->type) * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }
            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $this->send_user_notification($doctor, $user, $calender, $newVisit);

            return redirect(get_ev('cp_live') . '/user/dashboard?token_room=' . $newVisit->token_room);
        }

        $request->factorNumber = $factorNumber;
        $tokens = $this->request->token;
        $CallbackURL = url('/user/reserve_fail/' . $this->request->token);
        $request->save();
		  	$signKey = 'F641CBB767C412455976E2F4405F0C8325AEE4149E22707836D15551B91126063FFF76DEB6D954932769E0261EF76DBDC7F6A4FB9A6276DBA8134BDCDE355B7BB98AEC7EBF48FB322966E8472645203E61FF5047095A339C358C0522F97420D08C31D29A54333917FD7A44A6936D4437D155950B9413EC0200AE399B0B058019';
        $gatewayId = 'x7ddeyxg8n48k9';
        $pay = new PayStar($signKey, $gatewayId);
		
		
		///update price function
		$finalamountt = 0 ;
        if($request->used_credit > 0 ){
            $finalamountt = $request->amount - $request->used_credit;
        }else{
			$finalamountt = $request->amount;
		}
		
		///update price function
		
        $payment = $pay->create(
            (int)$finalamountt,
            $request->token, // order_id
           route('zibal.verify2'), // callback_url
            $user->mobile,
            ' بیمار: ' . $user->fullname . ' - پزشک: ' . $doctor->fullname . ' - تاریخ ویزیت: ' . jdate('d-m-Y', strtotime($calender->data)) . ' - ساعت ویزیت: ' . $calender->time . ' - نوع ویزیت: ' . VisitTypeEnum::name($calender->type),
        );
		 
		
		  
        $payment_url = $pay->createURL($payment);
    
        if ($payment_url)
        {
            return redirect()->to($payment_url);
        }
        else
        {
			return redirect()->to("https://sbm24.com/payment_fail?token= . $tokens ");
        }
        return 'Redirecting ...';
    }
    public function zarrinPay()
    {
        $factorNumber = rand(111111, 999999) . time();

        $request = TransactionReserve::where('token', $this->request->token)->first();

        if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

        $user = User::where('id', $request->user_id)->first();
        $doctor = User::where('id', $request->doctor_id)->first();

        $calender = DoctorCalender::where('id', $request->calender_id)->first();
        if (!$calender) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        if ($calender->capacity <= $calender->reservation) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

        if ($request->amount_paid <= 0) {
            $request->message = 'هزینه ای دریافت نشد';
            $request->status = 'paid';
            $request->save();

            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $request->doctor_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }
            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $request->doctor_id;
            $newVisit->calender_id = $request->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            ++$calender->reservation;
            $calender->save();

            $request->event_id = $newVisit->id;
            $request->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();

            if ($newVisit->id &&
                $request->affiliate_id &&
                $request->amount > 0) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary($calender->type) * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }
            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $this->send_user_notification($doctor, $user, $calender, $newVisit);

//            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $this->request->token.'?token_room='.$newVisit->token_room);
            return redirect(get_ev('cp_live') . '/user/dashboard?token_room=' . $newVisit->token_room);

        }

        $request->factorNumber = $factorNumber;
        $request->save();

        $CallbackURL = route('reserve.verify', ['token' => $factorNumber]);
        $description = ' بیمار: ' . $user->fullname . ' - پزشک: ' . $doctor->fullname . ' - تاریخ ویزیت: ' . jdate('d-m-Y', strtotime($calender->data)) . ' - ساعت ویزیت: ' . $calender->time . ' - نوع ویزیت: ' . VisitTypeEnum::name($calender->type);

        $zp = new ZarrinPal();
        $result = $zp->request($this->zarrin_merchant, (int)$request->amount_paid / 10, $description ?? "-", NULL, $user->mobile, $CallbackURL);


        if (isset($result["Status"]) && $result["Status"] == 100) {
            // Success and redirect to pay
            $zp->redirect($result["StartPay"]);
        } else {
            // error
            header('Location: https://sbm24.com/payment_fail?token=' . $this->request->token);
            die();
        }
    }

    public function verify($token)
    {

        $request = TransactionReserve::where('factorNumber', $token)->first();
        if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $token);

        $zp = new ZarrinPal();
        $result = $zp->verify($this->zarrin_merchant, $request->amount / 10);

        if (isset($result["Status"]) && $result["Status"] == 100) {

            $request->message = 'OK';
            $request->status = 'paid';
            $request->transId = $result["RefID"];
            $request->save();

            $calender = DoctorCalender::find($request->calender_id);
            ++$calender->reservation;
            $calender->save();


            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');


            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) &&
                (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $calender->user_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }

            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $request->user_id;
            $newVisit->doctor_id = $calender->user_id;
            $newVisit->calender_id = $calender->id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            $user = User::find($request->user_id);

            if ($request->used_credit > 0) {
                $credit = $user->credit - $request->used_credit;
                $credit = ($credit <= 0) ? 0 : $credit;
                $user->credit = $credit;
            }
            $user->save();
            if ($newVisit->id && $request->affiliate_id) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary() * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }

            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $doctor = User::find($calender->user_id);

            $this->send_user_notification($doctor, $user, $calender, $newVisit);

//                    return redirect(get_ev('cp_live') . '/user/reserve_save/' . $trackId.'?token_room='.$tokenRoom);
            return redirect(get_ev('cp_live') . '/user/dashboard?token_room=' . $tokenRoom);

        } else {
            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $token);
        }
    }

    public function topPay()
    {

        $factorNumber = rand(111111, 999999);

        $request = TransactionReserve::where('token', $this->request->token)->first();
        if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);


        $user = User::where('id', $request->user_id)->first();
        $doctor = User::where('id', $request->doctor_id)->first();

        $calender = DoctorCalender::where('id', $request->calender_id)->first();
        if (!$calender) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        if ($calender->capacity <= $calender->reservation) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);


        /* is free */

        if ($request->amount_paid <= 0) {
            $request->message = 'هزینه ای دریافت نشد';
            $request->status = 'paid';
            $request->save();

            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $request->doctor_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }

            /*if($user->id === 320){


            }*/


            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $request->doctor_id;
            $newVisit->calender_id = $request->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            $calender->reservation = ($calender->reservation + 1);
            $calender->save();

            $request->event_id = $newVisit->id;
            $request->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();

            if ($newVisit->id &&
                $request->affiliate_id &&
                $request->amount > 0) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary($calender->type) * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }
            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);
//            if ($waiting)
//                $waiting->update(['sent_message',1]);
            $this->send_user_notification($doctor, $user, $calender, $newVisit);

            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $this->request->token);

        }

        $request->factorNumber = $factorNumber;
        $request->save();
        $pay = new TopController();
        $Amount = ($request->amount_paid);
        $data['Amount'] = $Amount;
        $data['OrderId'] = $factorNumber;
        $result = $pay->pay($data);
        return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $result);
    }

    public function send_user_notification($doctor, $user, $calender, $evant)
    {

        if ($doctor && $user && $evant) {

            /*$capacity_time = 15;
            $min = 15;;

            if($calender){

                $capacity = (int) $calender->capacity;
                if($capacity > 0){

                    $capacity = 60 / $capacity;
                    $capacity = round($capacity , 0, PHP_ROUND_HALF_DOWN);
                    $capacity_time = $capacity;

                    $count = 1;
                    $my_number_transaction = 1;
                    $transaction_reserves = TransactionReserve::where('calender_id', $evant->calender_id)->get();
                    if($transaction_reserves){
                        foreach ($transaction_reserves as $item){
                            if($user->id === $item['user_id']){
                                $my_number_transaction = $count;
                            }
                            $count++;
                        }
                    }

                    if($my_number_transaction > 1) $my_number_transaction = $my_number_transaction - 1;

                    $min = $capacity * $my_number_transaction;

                }

            }

            $new_hours = (int) $evant['time'];
            if($new_hours == 0) $new_hours = 24;

            $start_min = $min - $capacity_time;

            $start = Carbon::parse($evant->data)->addHours($new_hours)->addMinutes($start_min);
            $st_start = false;
            if( jdate('Y-m-d', strtotime($start)) == jdate('Y-m-d')){

                if(change_number(jdate('H', strtotime($start))) == change_number(jdate('H'))){
                    $st_start = true;


                    $new_min = change_number(jdate('i'));
                    $new_min = $new_min + 15;
                    $start = Carbon::parse($evant->data)->addHours($new_hours)->addMinutes(($start_min + $new_min));
                    $start_hours = jdate('H:i', strtotime($start));
                    $start_date = jdate('Y-m-d', strtotime($start));

                }

            }

            if(!$st_start){
                $start_hours = jdate('H:i', strtotime($start));
                $start_date = jdate('Y-m-d', strtotime($start));
            }*/


            $start_hours = jdate('H:i', strtotime($evant->reserve_time));
            $start_date = jdate('d-m-Y', strtotime($evant->reserve_time));
            //  $reserve_date = Carbon::instance($evant->reserve_time)->format('Y-m-d');
            $reserve_date = date('Y-m-d', strtotime($evant->reserve_time));

            if ($user->mobile) {
                if (!$calender->type || $calender->type == 1)
                    SendSMS::send($user->mobile, "newVisitBimar", [
                        "token" => $start_date . ' ساعت: ' . $start_hours,
                        "token2" => 'عادی',
                        "token10" => $doctor->fullname,
                        "token20" => '۱ تا ۳ ساعت',
                        "token3" => '',
                    ]);
                elseif ($calender->type == 2)
                    SendSMS::send($user->mobile, "newVisitBimar", [
                        "token" => $start_date . ' ساعت: ' . $start_hours,
                        "token2" => 'فوری',
                        "token10" => $doctor->fullname,
                        "token20" => '۱۵ دقیقه',
                        "token3" => '',
                    ]);
               elseif ($calender->type == 3)
                    SendSMS::send($user->mobile, 'newVisitOfflineBimar',[
                        "token"=>$start_date,
                        "token2"=>$doctor->fullname,
                        "token3"=>$doctor->username
                    ]);
                elseif ($calender->type == 4)
                    SendSMS::send($user->mobile, "newVisitBimar", [
                        "token" => $start_date . ' ساعت: ' . $start_hours,
                        "token2" => 'تفسیرآزمایش',
                        "token10" => $doctor->fullname,
                        "token20" => '۱ تا ۳ ساعت',
                        "token3" => '',
                    ]);
                elseif ($calender->type == 5) {
                    $params = array(
                        "token" => $doctor->fullname,
                        "token2" => $start_date,
                        "token3" => $start_hours,
                        "token10" => $doctor->address,
                        "token20" => 'حضوری',
                    );
                    SendSMS::send($user->mobile, 'newInPersonPatient', $params);

//                    SendSMS::sendTemplateFive($user->mobile, $doctor->family, $doctor->address, $start_hours, $start_date, 'حضوری', 'newInPersonPatient');
                } elseif ($calender->type == 6)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'مشاوره عمل جراحی', '۱ تا ۳ ساعت', 'newVisitBimar');
                else
                    SendSMS::send($user->mobile, "newVisitBimar", [
                        "token" => $start_date . ' ساعت: ' . $start_hours,
                        "token2" => 'عادی',
                        "token10" => $doctor->fullname,
                        "token20" => '۱ تا ۳ ساعت',
                        "token3" => '',
                    ]);

            }

            if ($doctor->information && $calender->type == 5) {
                $params = array(
                    "token" => $start_date . ' ساعت: ' . $start_hours,
                    "token2" => $user->mobile,
                    "token3" => $doctor->fullname,
                    "token10" => $user->fullname,
                    "token20" => $calender->price,
                );
                SendSMS::send($doctor->information->office_secretary_mobile, 'inPersonForSecretary', $params);
            }

            if ($doctor->mobile && $reserve_date == Carbon::now()->format('Y-m-d')) {
                $type = 'عادی';
                $template = 'todayVisitReserve';
                switch ($calender->type) {
                    case 1:
                    {
                        $type = 'عادی';
                        $template = 'todayVisitReserve';
                        break;
                    }
                    case 2:
                    {
                        $type = 'فوری';
                        $template = 'todayVisitReserve';
                        break;
                    }
                    case 3:
                    {
                        $type = 'آفلاین';
                        $template = 'todayVisitReserve';
                        break;
                    }
                    case 4:
                    {
                        $type = 'تفسیرآزمایش';
                        $template = 'todayVisitReserve';
                        break;
                    }
                    case 6:
                    {
                        $type = 'مشاوره عمل جراحی';
                        $template = 'todayVisitReserve';
                        break;
                    }
                    case 5:
                    {
                        $type = 'حضوری';
                        $template = 'todayInPersonVisitReserve';
                        break;
                    }
                }


                try {
                    SendSMS::sendTemplateTree($doctor->mobile, $user->fullname,
                        $start_hours, $type, $template);

                } catch (\Exception $exception) {

                }
                if ($doctor->email) {
                    $subject = 'ویزیت آنلاین سلامت بدون مرز';
                    Mail::send('emails.reserve', ['start_date' => 'امروز',
                        'start_hour' => $start_hours,
                        'patient_fullname' => $user->fullname],
                        function ($message) use ($subject, $doctor) {
                            $message->from('noreply@sbm24.net', "sbm24");
                            $message->to($doctor->email)->subject($subject);
                        });
                }
            } elseif ($doctor->mobile &&
                $reserve_date == Carbon::now()->addDays(1)->format('Y-m-d') &&
                Carbon::now()->hour == 23 && $calender->type != 5) {
                SendSMS::sendTemplateTree($doctor->mobile,
                    $user->fullname, $start_date,
                    $start_hours, 'newVisitPezaeshk');
                if ($doctor->email) {
                    $subject = 'ویزیت آنلاین سلامت بدون مرز';
                    Mail::send('emails.reserve', ['start_date' => $start_date,
                        'start_hour' => $start_hours,
                        'patient_fullname' => $user->fullname],
                        function ($message) use ($subject, $doctor) {
                            $message->from('noreply@sbm24.net', "sbm24");
                            $message->to($doctor->email)->subject($subject);
                        });
                }
            }

        }

    }

}
