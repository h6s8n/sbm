<?php

namespace App\Http\Controllers\Api\v2\vandar;

use App\Http\Controllers\Api\v1\User\Visit\ReserveController;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Vandar\VandarToken;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Vandar\Laravel\Facade\Vandar;

class VandarController extends Controller
{

    private $mobile;
    private $password;


    public function __construct()
    {
        if (!function_exists('jalali_to_gregorian')){
            require(base_path('app/jdf.php'));
        }

        $this->mobile ='09123358157';
        $this->password='jgrWQtAK3Ayw';
    }

    public function pay($data)
    {

        $amount = $data['amount'] ?? 10000;
        $callback = route('vandar.verify');
        $mobile = $data['mobile'] ?? null;
        $factorNumber = $data['factorNumber'];
        $description = $data['$description'] ?? null;

//        Vandar::request($amount,$callback);
        $result = Vandar::request($amount,$callback,
            $mobile, $factorNumber,
            $description);

        if ($result['status'])
        {
            Vandar::redirect();
        }
        redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

    }

    public function verify()
    {
        $token=$_GET['token'];
        $status = $_GET['payment_status'];

        if ($status == 'OK') {
            $result = Vandar::verify($token);
            $factorNumber = $result['factorNumber'];
            $transId= $result['transId'];

            $request = TransactionReserve::where('factorNumber', $factorNumber)->first();
            if ($result['status']==1){
                if ($request) {

                    $request->message = 'OK';
                    $request->status = 'paid';
                    $request->transId = $transId;
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

//                    return redirect(get_ev('cp_live') . '/user/reserve_save/' . $token.'?token_room='.$tokenRoom);
                    return redirect(get_ev('cp_live') . '/user/dashboard?token_room='.$newVisit->token_room);

                } elseif ($request->status != 'paid') {

                    $request->message = 'تراکنش لغو شد';
                    $request->status = 'cancel';
                    $request->save();
                    return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $token);
                }
            }else{
                $request->message = 'تراکنش لغو شد';
                $request->status = 'cancel';
                $request->save();
                return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $token);
            }
        }
        return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $token);
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
                        'عادی','۱ تا ۳ ساعت','newVisitBimar');
                elseif ($calender->type == 2)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'فوری','۱۵ دقیقه','newVisitBimar');
                elseif ($calender->type == 3)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'آفلاین','۲۴ تا ۴۸ ساعت','newVisitBimar');
                elseif ($calender->type == 4)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'تفسیرآزمایش','۱ تا ۳ ساعت','newVisitBimar');
                else
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->family, $start_hours,
                        'عادی','۱ تا ۳ ساعت','newVisitBimar');
            }

            if ($doctor->mobile && $reserve_date == Carbon::now()->format('Y-m-d')) {
                $type='عادی';
                switch ($calender->type){
                    case 1:{
                        $type='عادی';
                        break;
                    }
                    case 2:{
                        $type='فوری';
                        break;
                    }
                    case 3:{
                        $type='آفلاین';
                        break;
                    }
                    case 4:{
                        $type='تفسیرآزمایش';
                        break;
                    }
                }

                try {
                    SendSMS::sendTemplateTree($doctor->mobile,$user->fullname,
                        $start_hours, $type,'todayVisitReserve');
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

    public function return_token()
    {
        $token = VandarToken::whereDate('expired','>',Carbon::now()->format('Y-m-d'))->first();
        if ($token)
            return $token->token;
        else{
            $auth=[
                'mobile'=>$this->mobile,
                'password'=>$this->password
            ];
            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.vandar.io/v3/login');
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                json_encode($auth));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $token = curl_exec($ch);
            curl_close($ch);
            $token = json_decode($token,true);
            $token=$token['access_token'];
            VandarToken::create([
                'token'=>$token,
                'expired'=>Carbon::now()->addDays(4)->format('Y-m-d H:i:s')
            ]);
            return $token;
        }
    }

}
