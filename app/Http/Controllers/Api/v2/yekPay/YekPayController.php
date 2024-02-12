<?php

namespace App\Http\Controllers\Api\v2\yekPay;

use App\Enums\CurrencyEnum;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\Repositories\v2\YekPay\YekPayRepository;
use App\SendSMS;
use App\Traites\CurrencyChangeTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Description;

class YekPayController extends Controller
{
    use CurrencyChangeTrait;
    protected $request;
    private $sms;
    private $yek;

    /** Currency Codes
     * 978 = EUR
     * 364 = IRR
     * 784 = AED
     * 826 = GBP
     * 949 = TRY
     */

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->sms=new SMSRepository();
        require(base_path('app/jdf.php'));

    }

    public function pay()
    {

        $ValidData = $this->validate($this->request, [
            'key' => 'required',
            'credit_pay' => 'nullable',
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required',
            'mobile' => 'required',
            'address' => 'required',
            'postalCode' => 'required',
            'country' => 'required',
            'city' => 'required',
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
        if ($requestCalender) {
            $calender_status = (($requestCalender->capacity - $requestCalender->reservation) > 0) ? true : false;
        }

        if (!$calender_status || !$requestCalender) return error_template('متاسفانه امکان ثبت نوبت وجود ندارد، لطفا ظرفیت را بررسی کنید.');


        $start_date = date('Y-m-d', strtotime($requestCalender->data));

        if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $requestCalender->time) && (((int)date('i')) >= 44)) {
            return error_template('ساعت این ویزیت روبه پایان است لطفا زمان دیگری را انتخاب کنید.');
        }


        $requestEvent = EventReserves::where('user_id', $user->id)
            ->where('doctor_id', $requestCalender->user_id)
            ->where('status', 'active')
            ->where('visit_status','not_end')
            ->orderBy('created_at', 'desc')->first();

        if ($requestEvent && $requestEvent->visit_status == 'not_end') {
            if ($requestEvent->calender_id == $requestCalender->id) {
                return error_template('این نوبت قبلا برای شما ثبت شده است. در صورت موجود مشکل با پشتیبانی تماس بگیرید.');
            } else if ($requestEvent->visit_status == 'not_end') {
                return error_template('در حال حاضر نوبت قبلی شما با این پزشک به پایان نرسیده است،');
            }
        }

        $invoice_number  = random_int(1000000,9999999);

        $token = Str::random(30);
        $newTrans = new TransactionReserve();
        $newTrans->user_id = $user->id;
        $newTrans->doctor_id = $requestCalender->user_id;
        $newTrans->calender_id = $requestCalender->id;
        $newTrans->payment_type = "EUR";
        $newTrans->factorNumber = $invoice_number;
        $newTrans->amount = $requestCalender->dollar_price && $requestCalender->dollar_price > 0 ? $requestCalender->dollar_price :
            $this->ConvertRialTo(CurrencyEnum::EUR,$requestCalender->price);
        $newTrans->used_credit = 0;
        $newTrans->amount_paid = $newTrans->amount;

        if (\request()->has('affiliate_tag')) {
            $affiliate = User::where('token', \request()->get('affiliate_tag'))->first();
            if($affiliate)
                $newTrans->affiliate_id = $affiliate->id;
        }

        if ($requestEvent) $newTrans->event_id = $requestEvent->id;

        $newTrans->token = $token;
        $newTrans->save();



        if ($newTrans->amount_paid <= 0) {
            $newTrans->message = 'هزینه ای دریافت نشد';
            $newTrans->status = 'paid';
            $newTrans->save();

            $fa_date_part = explode('-', $requestCalender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

            $capacity_mints = 60 / $requestCalender->capacity;
            $max_time = $requestCalender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($requestCalender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) && (((int)date('H')) == $requestCalender->time)) {
                $start = Carbon::parse($start_date)->addHours($requestCalender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($requestCalender->time);
            }

            $getevents = EventReserves::where('doctor_id', $newTrans->doctor_id)
                ->where('fa_data', $requestCalender->fa_data)
                ->where('time', $requestCalender->time)
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
            $newVisit->doctor_id = $newTrans->doctor_id;
            $newVisit->calender_id = $newTrans->calender_id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $requestCalender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $requestCalender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            $requestCalender->reservation = ($requestCalender->reservation + 1);
            $requestCalender->save();

            $newTrans->event_id = $newVisit->id;
            $newTrans->save();

            $credit = $user->credit - $newTrans->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();

            if ($newVisit->id &&
                $newTrans->affiliate_id &&
                $newTrans->amount > 0) {
                $affiliate = User::find($newTrans->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $newTrans->doctor_id,
                    'affiliate_id' => $newTrans->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $newTrans->amount,
                    'amount' => (OurBeneficiary() * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $newTrans->doctor_id)
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
                ->where('doctor_id', $newTrans->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $doctor = User::where('id', $newTrans->doctor_id)->first();

            $this->send_user_notification($doctor, $user, $requestCalender, $newVisit);

            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $invoice_number);
        }



        $this->request->merge(['amount' => $newTrans->amount]);
        $this->yek = new YekPayRepository($invoice_number);
       return $this->yek->pay($this->request->all());
    }

    public function verify()
    {
        $this->yek = new YekPayRepository();
        $result = $this->yek->verify();

        $factorNumber = $result['Order number'] ?? NULL;

        if ($factorNumber ) {
            $transId = $result['Authority'];

            $request = TransactionReserve::where('factorNumber', $factorNumber)->first();

            if (!$request) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

            $user = User::where('id', $request->user_id)->first();
            $doctor = User::where('id', $request->doctor_id)->first();

            $calender = DoctorCalender::where('id', $request->calender_id)->first();

            if (!$calender) return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
            if ($calender->capacity <= $calender->reservation)
                return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

           if ($request){

               $request->message = 'OK';
               $request->status = 'paid';
               $request->transId = $transId;
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

               $this->send_user_notification($doctor, $user, $calender, $newVisit);

               return redirect(get_ev('cp_live') . '/user/reserve_save/' . $factorNumber);
           }

        } else{
            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . 123456);
        }
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
            $start_date = jdate('Y-m-d', strtotime($evant->reserve_time));
            //  $reserve_date = Carbon::instance($evant->reserve_time)->format('Y-m-d');
            $reserve_date = date('Y-m-d', strtotime($evant->reserve_time));

            if ($user->mobile) {
                if (!$calender->type || $calender->type == 1)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'عادی', '۱ تا ۳ ساعت', 'newVisitBimar');
                elseif ($calender->type == 2)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'فوری', '۱۵ دقیقه', 'newVisitBimar');
                elseif ($calender->type == 3)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'آفلاین', '۲۴ تا ۴۸ ساعت', 'newVisitBimar');
                elseif ($calender->type == 4)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'تفسیرآزمایش', '۱ تا ۳ ساعت', 'newVisitBimar');
                elseif ($calender->type == 5)
                    SendSMS::sendTemplateFive($user->mobile, $doctor->family, $doctor->address, $start_hours,
                        $start_date,'حضوری','newInPersonPatient');
                else
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'عادی', '۱ تا ۳ ساعت', 'newVisitBimar');
            }

            if ($doctor->mobile && $reserve_date == Carbon::now()->format('Y-m-d')) {
                $type='عادی';
                $template='todayVisitReserve';
                switch ($calender->type){
                    case 1:{
                        $type='عادی';
                        $template='todayVisitReserve';
                        break;
                    }
                    case 2:{
                        $type='فوری';
                        $template='todayVisitReserve';
                        break;
                    }
                    case 3:{
                        $type='آفلاین';
                        $template='todayVisitReserve';
                        break;
                    }
                    case 4:{
                        $type='تفسیرآزمایش';
                        $template='todayVisitReserve';
                        break;
                    }
                    case 5:{
                        $type='حضوری';
                        $template='todayInPersonVisitReserve';
                        break;
                    }
                }

                try {
                    SendSMS::sendTemplateTree($doctor->mobile,$user->fullname,
                        $start_hours, $type,$template);
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
                Carbon::now()->hour == 23) {
                SendSMS::sendTemplateTree($doctor->mobile,
                    $user->fullname,$start_date,
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
