<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Enums\VisitTypeEnum;
use App\Events\SMS\SetTimeNotificationEvent;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionReserve;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));

    }

    public function getInfo()
    {


        $date = change_number(jdate('Y-m-d'));
        $dateTime = change_number(jdate('H'));

        $user = auth()->user();
        $request = DoctorCalender::where('user_id', $user->id)
            ->leftJoin('partners',function($join) {
                $join->on('partners.id', '=', 'doctor_calenders.partner_id');
            })
            ->where('fa_data', '>=', $date)
//            ->where('partner_id', 0)
            ->orderBy('data', 'ASC')
            ->select('doctor_calenders.id as key', 'fa_data', 'data', 'time', 'capacity','partners.name as partner_name' ,'reservation', 'price','type',

            )->get();
        $request_new = [];
        foreach ($request as $v) {
            if ($v['fa_data'] == $date) {
                if ($v['time'] >= $dateTime) {
                    $request_new[] = $v;
                }
            } else {
                $request_new[] = $v;
            }
            $v['day'] = DayOfWeek(\Carbon\Carbon::parse($v['data'])->dayOfWeek);
        }

        return success_template($request_new);

    }

    public function getTimes()
    {
        $date = change_number($this->request->get('date'));
        $doctor = auth()->user();

        $request = DoctorCalender::where('user_id', $doctor->id)
            ->where('fa_data', '=', $date)
            ->where(function ($q){
                $q->where('capacity' ,DB::raw('reservation'))
                    ->orWhere('type','!=',VisitTypeEnum::type('in-person'));
            })
            ->orderBy('data', 'ASC')
            ->pluck('time');
        return success_template($request);

    }

    public function setTimes()
    {
        $ValidData = $this->validate($this->request, [
            'payment_type' => 'required|in:online,cash,pos,card_to_card',
            'price' => 'required|numeric',
            'description' => 'nullable',
            'user_id' => 'required',
            'tracking_code' => 'nullable',
            'time' => 'required',
            'date' => 'required',
        ]);

        $doctor = auth()->user();

        $requestEvent = EventReserves::where('event_reserves.user_id', $ValidData['user_id'])
            ->join('doctor_calenders','doctor_calenders.id' , '=','event_reserves.calender_id')
            ->where('doctor_calenders.type',VisitTypeEnum::type('in-person'))
            ->where('doctor_id', $doctor->id)
            ->where('visit_status', 'not_end')
            ->whereDate('event_reserves.fa_data', date('Y-m-d',strtotime($ValidData['date'])))
            ->orderBy('event_reserves.created_at', 'desc')->first();

        if ($requestEvent){
           return error_template('از قبل نوبت حضوری در این تاریخ برای این بیمار ثبت شده است.');
        }


        $firstAvailableTime = DoctorCalender::where('user_id' , $doctor->id)
            ->whereDate('fa_data',date('Y-m-d',strtotime($ValidData['date'])))
            ->where('time', $ValidData['time'])
//            ->where('capacity' ,'>',DB::raw('reservation'))
//            ->where('type',VisitTypeEnum::type('in-person'))
            ->first();

        if ($firstAvailableTime && $firstAvailableTime->type != VisitTypeEnum::type('in-person')){
            return error_template('ویزیت این ساعت حضوری نیست');
        }

        $fa_date_part = explode('-', $this->request->get('date'));
        $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

        if (!$firstAvailableTime){

            if ($this->request->get('price') < 100000)
                return error_template('مبلغ ویزیت حضوری نباید کمتر از ۱۰۰,۰۰۰ ریال  باشد');

            $firstAvailableTime = new DoctorCalender();
            $firstAvailableTime->user_id = $doctor->id;
            $firstAvailableTime->fa_data = $this->request->get('date');
            $firstAvailableTime->data = date('Y-m-d',strtotime($en_date));
            $firstAvailableTime->time = $ValidData['time'];
            $firstAvailableTime->original_price = $this->request->get('price');
            $firstAvailableTime->price = $this->request->get('price');
            $firstAvailableTime->type = VisitTypeEnum::type('in-person');
            $firstAvailableTime->created_user_id = auth()->id();

            $firstAvailableTime->capacity = 6;
            $firstAvailableTime->reservation = 0;
            $firstAvailableTime->off_price =  0;
            $firstAvailableTime->off_dollar_price =  0;
            $firstAvailableTime->original_dollar_price = 0;
            $firstAvailableTime->dollar_price = 0;
            $firstAvailableTime->partner_price = 0;

            $firstAvailableTime->save();
        }

        if (($firstAvailableTime->capacity - $firstAvailableTime->reservation) <= 0){
            return error_template('ظرفیت این ساعت پر شده');
        }

        return $this->reserveEvent($firstAvailableTime,$this->request);

    }

    public function reserveEvent(DoctorCalender $calender,$request)
    {
        $user = User::find($request->user_id);

        if ($request->payment_type == 'online'){

            $pay_link = 'https://cp.sbm24.com/user/reserve/SB' . $calender->id;
            $params = array(
                "token"  =>$pay_link,
                "token2" => $calender->doctor->fullname,
                "token3" => $calender->price + OurBeneficiary($calender->type),
            );

            SendSMS::send($user->mobile,'payLink',$params);

            return success_template(['message' => 'تا لحظاتی دیگر لینک پرداخت برای بیمار پیامک می شود.']);
        }

        $capacity_mints = 60 / $calender->capacity;

        $start_date = date('Y-m-d', strtotime($calender->data));
        $start = Carbon::parse($start_date)->addHours($calender->time);

        $getevents = EventReserves::where('doctor_id', $request->doctor_id)
            ->where('fa_data', $calender->fa_data)
            ->where('time', $calender->time)
            ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
        if ($getevents) {
            $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);
        }

        $reserve_time = date('Y-m-d H:i', strtotime($start));

        $tokenRoom = Str::random(15);
        $newVisit = new EventReserves();
        $newVisit->user_id = $request->user_id;
        $newVisit->doctor_id = $calender->user_id;
        $newVisit->calender_id = $calender->id;
        $newVisit->token_room = $tokenRoom;
        $newVisit->fa_data = $calender->fa_data;
        $newVisit->data = $calender->data;
        $newVisit->time = $calender->time;
        $newVisit->reserve_time = $reserve_time;
        $newVisit->save();

        return $this->payEvent($calender, $newVisit, $request);
    }

    public function payEvent(DoctorCalender $calender,EventReserves $event,$request)
    {

        $token = Str::random(30);

        $newTrans = new TransactionReserve();
        $newTrans->user_id = $event->user->id;
        $newTrans->doctor_id = $calender->user_id;
        $newTrans->calender_id = $calender->id;
        $newTrans->event_id = $event->id;
        $newTrans->token = $token;
        $newTrans->amount = $calender->price + OurBeneficiary($calender->type);
        $newTrans->used_credit = 0;
        $newTrans->amount_paid = $calender->price + OurBeneficiary($calender->type);
        $newTrans->payment_type = $request->payment_type;
        $newTrans->factorNumber = $request->tracking_code;
        $newTrans->message = $request->description;
        $newTrans->status = 'paid';
        $newTrans->save();

        ++$calender->reservation;
        $calender->save();

        $this->send_user_notification($event->doctor, $event->user, $calender, $event);

        return success_template(['message' => 'نوبت با موفقیت ثبت شد']);
    }

    public function Create()
    {


        $ValidData = $this->validate($this->request, [
            'day' => 'required|numeric',
            'month' => 'required|numeric',
            'sum_date' => 'required|numeric',
            'time' => 'required',
            'date_time' => 'required',
            'price' => 'nullable|numeric',
            'price_off' => 'nullable|numeric|min:50000',
            'capacity' => 'required|numeric|max:20',
            'dollar_price' => 'nullable|numeric',
            'dollar_priceـoff' => 'nullable|numeric|min:5',
            'partner_price' => 'nullable|numeric|min:50000',
        ]);
        if (\request()->has('price') && \request()->input('price') > 0) {
            if (\request()->input('price') < 198000)
                return error_template("حداقل میلغ ویزیت ۱۹۸۰۰۰ ریال می باشد");
        }
        if (\request()->has('type') && \request()->input('type') == 4){
            if (\request()->input('price') > 100000)
                return error_template("حداکثر مبلغ تفسیرآزمایش ۱۰۰,۰۰۰ ریال می باشد");
        }
        if (!$ValidData['price']) $this->request['price'] = 0;
        if (!$ValidData['dollar_price']) $this->request['dollar_price'] = 0;


        $date = change_number(jdate('Y')) . '/' . $ValidData['month'] . '/' . $ValidData['day'];

        $dateTime = jalali_to_gregorian(change_number(jdate('Y')), $ValidData['month'], $ValidData['day'], '/');

        $user = auth()->user();

        $dateTimeFull = json_decode($ValidData['date_time']);
        $timeFull = json_decode($ValidData['time']);

        $dateTimeFullNew = [];
        if ($dateTimeFull) {
            foreach ($dateTimeFull as $item) {
                $dateTimeFullNew[] = $item->value;
            }

            $dateTimeFull = $dateTimeFullNew;
        }


        $partner_price = ($this->request->get('partner_price')) ? $this->request->get('partner_price') : 0;

        $off_price = $this->request->get('price_off');
        $original_price = $this->request->get('price');
        $price = $original_price;
        if ($off_price && ($off_price < $original_price)) {
            $price = $off_price;
        }

        $dollar_priceـoff = $this->request->get('dollar_priceـoff');
        $original_dollar_price = $this->request->get('dollar_price');
        $dollar_price = $original_dollar_price;
        if ($dollar_priceـoff && ($dollar_priceـoff < $original_dollar_price)) {
            $dollar_price = $dollar_priceـoff;
        }

        if ($off_price && ($off_price >= $original_price)) {
            return error_template('فیمت با تخفیف باید کمتر از قیمت اصلی باشد.');
        }

        if ($dollar_priceـoff && ($dollar_priceـoff >= $original_dollar_price)) {
            return error_template('فیمت با تخفیف باید کمتر از قیمت اصلی باشد.');
        }


        if ($timeFull) {

            foreach ($timeFull as $time) {

                if (!is_numeric($time->value)) return error_template('ساعت باید به شکل عدد وارد شود.');

                $i = 0;
                for ($i; $i < $ValidData['sum_date']; $i++) {
                    $dateTimeNew = Carbon::parse($dateTime)->addDays($i);
                    $en_date = date('Y-m-d', strtotime($dateTimeNew));
                    $fa_date = jdate('Y-m-d', strtotime($dateTimeNew));
                    $well_date = jdate('l', strtotime($dateTimeNew));

                    if (in_array($well_date, $dateTimeFull)) {
                        $request = DoctorCalender::where('user_id', $user->id)
                            ->where('fa_data', $fa_date)
                            ->where('time', $time->value)->first();
                        if ($request) {
                            $request->capacity = $ValidData['capacity'];
                            $request->save();
                        }
                        if (!$request) {
                            $newTime = new DoctorCalender();
                            $newTime->user_id = $user->id;
                            $newTime->fa_data = $fa_date;
                            $newTime->data = $dateTimeNew;
                            $newTime->time = $time->value;
                            $newTime->capacity = $ValidData['capacity'];
                            $newTime->reservation = 0;
                            $newTime->off_price = ($off_price) ? $off_price : 0;
                            $newTime->original_price = $original_price;
                            $newTime->price = $price;
                            $newTime->type = \request()->has('type') && \request()->input('type') ? \request()->input('type') : 1;
                            $newTime->created_user_id = auth()->id();

                            $newTime->off_dollar_price = ($dollar_priceـoff) ? $dollar_priceـoff : 0;
                            $newTime->original_dollar_price = $original_dollar_price;
                            $newTime->dollar_price = $dollar_price;
                            $newTime->partner_price = $partner_price;

                            $newTime->save();
                            if ($i == 0)
                                SetTimeNotificationEvent::dispatch($newTime);

                        }

                    }

                }


            }

        } else {
            return error_template('ساعت را وارد کنید.');
        }

        return $this->getInfo();


    }

    public function delete()
    {


        $ValidData = $this->validate($this->request, [
            'delete_items' => 'required',
        ]);

        $Full = json_decode($ValidData['delete_items']);
        $user = auth()->user();

        if ($Full) {

            foreach ($Full as $item) {


                $request = DoctorCalender::where('user_id', $user->id)->where('id', $item)->first();
                if ($request) {
                    if ($request->reservation > 0) {
                        $request->capacity = $request->reservation;
                        $request->save();
                    } else
                        $request->delete();

                }

            }

        } else {
            return error_template('چیزی برای حذف انتخاب نکردید.');
        }

        return $this->getInfo();

    }

    public function online()
    {

        $ValidData = $this->validate($this->request, [
            'time' => 'required|numeric',
            'capacity' => 'required|numeric',
            'price' => 'nullable|numeric',
            'price_off' => 'nullable|numeric|min:50000',
            'dollar_price' => 'nullable|numeric',
            'dollar_priceـoff' => 'nullable|numeric|min:5',
            'partner_price' => 'nullable|numeric|min:50000',
        ]);

        if (!$ValidData['price']) $this->request['price'] = 0;
        if (!$ValidData['dollar_price']) $this->request['dollar_price'] = 0;

        $user = auth()->user();

        $partner_price = ($this->request->get('partner_price')) ? $this->request->get('partner_price') : 0;

        $off_price = $this->request->get('price_off');
        $original_price = $this->request->get('price');
        $price = $original_price;
        if ($off_price && ($off_price < $original_price)) {
            $price = $off_price;
        }

        $dollar_priceـoff = $this->request->get('dollar_priceـoff');
        $original_dollar_price = $this->request->get('dollar_price');
        $dollar_price = $original_dollar_price;
        if ($dollar_priceـoff && ($dollar_priceـoff < $original_dollar_price)) {
            $dollar_price = $dollar_priceـoff;
        }

        if ($off_price && ($off_price >= $original_price)) {
            return error_template('فیمت با تخفیف باید کمتر از قیمت اصلی باشد.');
        }

        if ($dollar_priceـoff && ($dollar_priceـoff >= $original_dollar_price)) {
            return error_template('فیمت با تخفیف باید کمتر از قیمت اصلی باشد.');
        }

        $full_time = [];
        for ($i = 0; $i < $ValidData['time']; $i++) {

            $dateTime = Carbon::now()->addHours($i);
            $Time = $dateTime->hour;
            $dateTime = $dateTime->year . '/' . $dateTime->month . '/' . $dateTime->day;
            $en_date = date('Y-m-d', strtotime($dateTime));
            $fa_date = jdate('Y-m-d', strtotime($dateTime));

            $full_time[] = $Time;
            $request = DoctorCalender::where('user_id', $user->id)
                ->where('fa_data', $fa_date)
                ->where('time', $Time)->first();
            if (!$request) {

                $newTime = new DoctorCalender();
                $newTime->user_id = $user->id;
                $newTime->fa_data = $fa_date;
                $newTime->data = $en_date;
                $newTime->time = $Time;
                $newTime->capacity = $ValidData['capacity'];
                $newTime->reservation = 0;
                $newTime->off_price = ($off_price) ? $off_price : 0;
                $newTime->original_price = $original_price;
                $newTime->price = $price;
                $newTime->off_dollar_price = ($dollar_priceـoff) ? $dollar_priceـoff : 0;
                $newTime->original_dollar_price = $original_dollar_price;
                $newTime->dollar_price = $dollar_price;
                $newTime->partner_price = $partner_price;
                $newTime->type = 2;
                $newTime->save();

            } else {
                $request->type = 2;
                $request->save();
            }

        }

        return success_template(['dr_visit_status' => true]);

    }

    public function offline()
    {

        $user = auth()->user();

        $dateTime = Carbon::now();
        $Time = $dateTime->hour;
        $dateTime = $dateTime->year . '/' . $dateTime->month . '/' . $dateTime->day;
        $fa_date = jdate('Y-m-d', strtotime($dateTime));

        $full_time[] = $Time;
        $requests = DoctorCalender::where('user_id', $user->id)
            ->where('fa_data', $fa_date)
            ->where('type',2)->get();
        foreach ($requests as $request) {
            if ($request) {
                if ($request->reservation == 0)
                    $request->delete();
                else {
                    $request->capacity = $request->reservation;
                    $request->save();
                }

            }
        }

        return success_template(['dr_visit_status' => false]);

    }

    public static function get_status()
    {

        $user = auth()->user();

        require(base_path('app/jdf.php'));

        $dateTime = Carbon::now();
        $Time = $dateTime->hour;
        $dateTime = $dateTime->year . '/' . $dateTime->month . '/' . $dateTime->day;
        $fa_date = date('Y-m-d', strtotime($dateTime));

        $request = DoctorCalender::where('user_id', $user->id)
            ->where('data', $fa_date)
            ->where('time', $Time)->first();

        return ($request) ? true : false;

    }

    public function send_user_notification($doctor, $user, $calender, $evant)
    {

        if ($doctor && $user && $evant) {

            $start_hours = jdate('H:i', strtotime($evant->reserve_time));
            $start_date = jdate('d-m-Y', strtotime($evant->reserve_time));
            //  $reserve_date = Carbon::instance($evant->reserve_time)->format('Y-m-d');
            $reserve_date = date('Y-m-d', strtotime($evant->reserve_time));

            if ($user->mobile) {
                if (!$calender->type || $calender->type == 1)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'عادی','۱ تا ۳ ساعت','newVisitBimar');
                elseif ($calender->type == 2)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'فوری','۱۵ دقیقه','newVisitBimar');
                elseif ($calender->type == 3)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'آفلاین','۲۴ تا ۴۸ ساعت','newVisitBimar');
                elseif ($calender->type == 4)
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'تفسیرآزمایش','۱ تا ۳ ساعت','newVisitBimar');
                elseif ($calender->type == 5)
                    SendSMS::sendTemplateFive($user->mobile, $doctor->fullname, $doctor->address, $start_hours,
                        $start_date,'حضوری','newInPersonPatient');
                else
                    SendSMS::sendTemplateFive($user->mobile, $start_date, $doctor->fullname, $start_hours,
                        'عادی','۱ تا ۳ ساعت','newVisitBimar');
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
