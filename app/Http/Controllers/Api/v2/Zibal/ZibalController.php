<?php

namespace App\Http\Controllers\Api\v2\Zibal;

use App\Http\Controllers\Api\v1\User\Visit\ReserveController;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Vandar\VandarToken;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\Secretary\SpecialSecretary;
use App\SendSMS;
use App\Services\Gateways\src\Zibal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Vandar\Laravel\Facade\Vandar;
use App\Services\Gateways\src\PayStar;


class ZibalController extends Controller
{

    private $zibal_merchant;
    public function __construct()
    {
//        $this->zibal_merchant = '641610e418f934762da20635';
        $this->zibal_merchant = '63f622a118f9346eef70dea4';

        if (!function_exists('jalali_to_gregorian')){
            require(base_path('app/jdf.php'));
        }
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


    public function verify()
    {
        $success = request()->get('success');
        $orderId = request()->get('orderId');
        $trackId = request()->get('trackId');

        if ($success) {

            $parameters = array(
                "merchant" => $this->zibal_merchant,
                "trackId" => $trackId,//required
            );

            $result = $this->prepare('https://gateway.zibal.ir/v1/verify', $parameters);


            $request = TransactionReserve::where('factorNumber', $orderId)->first();

            if ($result->result == 100){

                $factorNumber = $result->orderId;
                $transId= $result->refNumber;

                if ($request) {
                    $request->message = 'OK';
                    if ($request->user_id == 2796){
                        $request->message = json_decode($result);
                    }
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

                    return redirect(get_ev('cp_live') . '/user/reserve_save/' . $trackId.'?token_room='.$tokenRoom);

                } elseif ($request->status != 'paid') {
                    
                    $request->message = 'تراکنش لغو شد';
                    $request->status = 'cancel';
                    $request->save();
                    return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $trackId);
                }
            }else{
                

                $request->message = 'تراکنش لغو شد';
                $request->status = 'cancel';
                $request->save();
                return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $trackId);
            }
        }
       
        return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $trackId);
    }

    public function verify2()
    {
		
	 $redirect_fail = fn($trackId) => redirect(get_ev('cp_live') . '/user/reserve_fail/' . $trackId);
        $payment = (object) request()->all();
		
		
        $request = TransactionReserve::where('token', $payment->order_id)->first();
		
		
        if (!$request) return $redirect_fail($payment->order_id);
        $trackId = $payment->transaction_id;
$signKey = 'F641CBB767C412455976E2F4405F0C8325AEE4149E22707836D15551B91126063FFF76DEB6D954932769E0261EF76DBDC7F6A4FB9A6276DBA8134BDCDE355B7BB98AEC7EBF48FB322966E8472645203E61FF5047095A339C358C0522F97420D08C31D29A54333917FD7A44A6936D4437D155950B9413EC0200AE399B0B058019';
        $gatewayId = 'x7ddeyxg8n48k9';
        

        if($payment->status == "1"){
            $pay = new PayStar($signKey, $gatewayId);
                $verify_payment = $pay->verify($request->amount_paid, $payment);
			
            if($verify_payment->status == "1"){
                
                if($request){
                    $request->status = 'paid';
                    $request->message = 'OK';
                    $request->transId = $payment->ref_num;
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

                    return redirect(get_ev('cp_live') . '/user/dashboard?token_room='.$tokenRoom);


                }

            }
            return $redirect_fail($payment->order_id);
        }
       $request = TransactionReserve::where('factorNumber', $payment->order_id)->first();
        $calender = DoctorCalender::find($request->calender_id);
        --$calender->pending_user;
                    $calender->save();
                    return $redirect_fail($payment->order_id);
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
                    SendSMS::send($user->mobile,"newVisitBimar",[
                        "token"=>$start_date . ' ساعت: ' . $start_hours,
                        "token2"=> 'عادی',
                        "token10"=>$doctor->fullname,
                        "token20"=>'۱ تا ۳ ساعت',
                        
						"token3"=>$user->mobile,
                    ]);
                elseif ($calender->type == 2)
                    SendSMS::send($user->mobile,"newVisitBimar",[
                        "token"=>$start_date . ' ساعت: ' . $start_hours,
                        "token2"=> 'فوری',
                        "token10"=>$doctor->fullname,
                        "token20"=>'۱۵ دقیقه',
                        "token3"=>$user->mobile,
                    ]);
                elseif ($calender->type == 3)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, '',
                        '','','newVisitOfflineBimar');
                elseif ($calender->type == 4)
                    SendSMS::send($user->mobile,"newVisitBimar",[
                        "token"=>$start_date . ' ساعت: ' . $start_hours,
                        "token2"=> 'تفسیرآزمایش',
                        "token10"=>$doctor->fullname,
                        "token20"=>'۱ تا ۳ ساعت',
                        "token3"=>$user->mobile,
                    ]);
                elseif ($calender->type == 6)
                    SendSMS::sendTemplateFive($user->mobile, $start_date . ' ساعت: ' . $start_hours, $doctor->fullname, $start_hours,
                        'مشاوره عمل جراحی', '۱ تا ۳ ساعت', 'newVisitBimar');
                elseif ($calender->type == 5){
                    $params = array(
                        "token" => $doctor->fullname,
                        "token2" => $start_date,
                        
                        "token10" => $doctor->address,
                        "token20" => 'حضوری',
						"token3"=>$user->mobile,
                    );
                    SendSMS::send($user->mobile, 'newInPersonPatient', $params);
                } else
                    SendSMS::send($user->mobile,"newVisitBimar",[
                        "token"=>$start_date . ' ساعت: ' . $start_hours,
                        "token2"=> 'عادی',
                        "token10"=>$doctor->fullname,
                        "token20"=>'۱ تا ۳ ساعت',
                        "token3"=>$user->mobile,
                    ]);
            }

            if ($doctor->information && $calender->type == 5){
                $params = array(
                    "token"  => $start_date . ' ساعت: ' . $start_hours,
                    "token2" => $user->mobile,
                    "token3" => $doctor->fullname,
                    "token10" => $user->fullname,
                    "token20" => $calender->price,
					
                );
                SendSMS::send($doctor->information->office_secretary_mobile, 'inPersonForSecretary', $params);
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
                    case 6:{
                        $type='مشاوره عمل جراحی';
                        $template='todayVisitReserve';
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
                Carbon::now()->hour == 23 && $calender->type != 5) {
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
