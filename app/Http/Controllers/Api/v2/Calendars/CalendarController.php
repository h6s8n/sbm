<?php

namespace App\Http\Controllers\Api\v2\Calendars;

use App\Enums\VisitTypeEnum;
use App\Events\SMS\SetTimeNotificationEvent;
use App\Model\Visit\DoctorCalender;
use App\Repositories\v2\Calendar\CalendarInterface;
use App\Repositories\v2\Calendar\CalendarRepository;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\PdfParser\Filter\Flate;

class CalendarController extends Controller
{
    private $calendar;

    public function __construct()
    {
        $this->calendar = new CalendarRepository();
        require(base_path('app/jdf.php'));

    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required',
            'capacity' => 'required|max:6',
            'visit_type' => 'required',
            'office_id' => 'required_if:visit_type,==,5',
            'consultation_type' => 'required_if:visit_type,1,2,3',
        ]);

        $response = $this->calendar->update($request->all());
        if ($response->status)
            return success_template($response->object);
        return error_template($response->message);
    }

    public function getOnlineDoctors()
    {
        return success_template($this->calendar->getOnlineDoctors());
    }

    public function getInterpretationDoctors()
    {
        $response = $this->calendar->getInterpretationDoctors();
        if ($response)
            return success_template($response);
        return error_template();
    }

    public function getInPersonDoctors()
    {

        $response = cache()->remember('getInPersonDoctors1',3600,function (){
            return $this->calendar->getInPersonDoctors();
        });

        if (\request()->get('page') || \request()->get('paginate')){
            $response = $this->calendar->getInPersonDoctors();
        }

        if ($response)
            return success_template($response);
        return error_template();
    }

    public function getSurgeryDoctors()
    {
        $response = cache()->remember('getSurgeryDoctors',3600,function (){
            return $this->calendar->getSurgeryDoctors();
        });

        if (\request()->get('page')){
            $response = $this->calendar->getSurgeryDoctors();
        }

        if ($response)
            return success_template($response);
        return error_template();
    }

    public function getSponsorDoctors()
    {
        $response = $this->calendar->getSponsorDoctors();
        if ($response)
            return success_template($response);
        return error_template();
    }

    public function getPrescriptionsDoctors()
    {
        $response = $this->calendar->getPrescriptionsDoctors();
        if ($response)
            return success_template($response);
        return error_template();
    }

    public function getInfo()
    {
        $now = Verta::now();
        $user = auth()->user();
        $request = DoctorCalender::where('user_id', $user->id)
            ->leftJoin('partners',function($join) {
                $join->on('partners.id', '=', 'doctor_calenders.partner_id');
            })            ->whereDate('data', '>=', Carbon::now()->format('Y-m-d'))
//            ->where('partner_id', 0)
            ->orderBy('data', 'ASC')
            ->select('doctor_calenders.id as key', 'fa_data', 'data', 'time', 'capacity', 'partners.name as partner_name','reservation','price', 'type')->get();
        $request_new = [];
        foreach ($request as $v) {
            if ($v['fa_data'] == $now->format('Y-m-d')) {
                if ($v['time'] >= (int)$now->format('H')) {
                    $request_new[] = $v;
                }
            } else {
                $request_new[] = $v;
            }
        }

        return success_template($request_new);
    }

    public function store(Request $request)
    {
        /**
         *    0 => "Sunday"
         *    1 => "Monday"
         *    2 => "Tuesday"
         *    3 => "Wednesday"
         *    4 => "Thursday"
         *    5 => "Friday"
         *    6 => "Saturday"
         **/

        $request->validate([
            'selectedWeekDays' => 'required',
            'duration' => 'required',
            'selectedCapacity' => 'required',
            'type' => 'required',
//            'price' => 'nullable|numeric',
            'selectedTime' => 'required',
        ]);
        if (Carbon::today()->dayOfWeek != 6)
            $starting_point = Carbon::today()->subDays(Carbon::today()->dayOfWeek + 1);
        else
            $starting_point = Carbon::today();

        $duration = $request->input('duration');
        $capacity = $request->input('selectedCapacity');
        $type = $request->input('type');
        $price = change_number($request->input('price')) ?: 0;
        $days = $request->input('selectedWeekDays');
        $times = $request->input('selectedTime');
        $has_prescription = $request->has('has_prescription') ? \request()->input('has_prescription') : 0;
        $partner_id = $request->has('partner_id') ? \request()->input('partner_id') : 0;
        if ($type == 4 && $price > 100000)
            return error_template('مبلغ ویزیت تفسیر آزمایش نباید بیشتر از ۱۰۰,۰۰۰ ریال  باشد');

        if ($type == 5 && $price < 100000)
            return error_template('مبلغ ویزیت حضوری نباید کمتر از ۱۰۰,۰۰۰ ریال  باشد');

        if ($price != 0 && $price < 100000){
            return error_template('مبلغ ویزیت نباید کمتر از ۱۰۰,۰۰۰ ریال  باشد');
        }
        if ((int)filter_var($request->input('savePattern'), FILTER_VALIDATE_BOOLEAN)) {
            /* @var User $user */

            $user = auth()->user();
            if ($user->pattern()->where('type',$type)->first())
                $user->pattern()->where('type',$type)->delete();

            try {

                $user->pattern()->create([
                    'duration' => $duration,
                    'selectedCapacity' => $capacity,
                    'type' => $type,
                    'price' => $price,
                    'selectedWeekDays' => $days,
                    'selectedTime' => $times,
                    'partner_id' => $partner_id,
                    'has_prescription' => $has_prescription
                ]);
            }catch (\Exception $exception){
                return error_template($exception->getMessage());
            }
        }


        $days = json_decode($days, true);
        $times = json_decode($times, true);

        if (Carbon::today()->dayOfWeek != 6) {
            $flag = true;
            foreach ($days as $day) {
                if ($day['value'] >= Carbon::today()->dayOfWeek && $day['value']!=6) {
                    if ($day['value'] == Carbon::today()->dayOfWeek){
                        foreach ($times as $time){
                            if ($time['value'] < Carbon::now()->hour)
                                continue;
                            else
                            {
                                $flag=false;
                                break;
                            }
                        }
                    }else {
                        $flag = false;
                        break;
                    }
                }
            }
            if ($flag)
                $starting_point = $starting_point->addDays(7);
        }

        DB::beginTransaction();
        try {

            for ($i = 1; $i <= $duration; $i++) {
                for ($j = 1; $j <= 7; $j++) {
                    foreach ($days as $day) {
                        if ($starting_point->dayOfWeek == $day['value']) {
                            foreach ($times as $time) {
                                $temp = DoctorCalender::where('user_id',auth()->id())
                                    ->where('fa_data',Verta::instance($starting_point)->format('Y-m-d'))
                                    ->where('time',$time['value'])
                                    ->where('data',$starting_point->format('Y-m-d'))->first();

                                if (!$temp) {
                                    $calendar = new DoctorCalender();
                                    $calendar->user_id = auth()->id();
                                    $calendar->created_user_id = auth()->id();
                                    $calendar->fa_data = Verta::instance($starting_point)->format('Y-m-d');
                                    $calendar->data = $starting_point->format('Y-m-d');
                                    $calendar->time = $time['value'];
                                    $calendar->capacity = $capacity;
                                    $calendar->reservation = 0;
                                    $calendar->original_price = $price;
                                    $calendar->off_price = 0;
                                    $calendar->price = $price;
                                    $calendar->original_dollar_price = 0;
                                    $calendar->off_dollar_price = 0;
                                    $calendar->dollar_price = 0;
                                    $calendar->type = $type;
                                    $calendar->partner_price = 0;
                                    $calendar->has_prescription = $has_prescription;
                                    $calendar->partner_id = $partner_id;
                                    $calendar->save();
                                    if ($i == 1)
                                        SetTimeNotificationEvent::dispatch($calendar);
                                }
                            }
                        }
                    }
                    $starting_point->addDays(1);
                }
//                $starting_point->addDays(1);
            }
            DB::commit();
            return $this->getInfo();
        } catch (\Exception $exception) {
            return error_template($exception->getMessage());
            DB::rollBack();
        }
    }


    public function datagrid(Request $request)
    {
        $user = auth()->user();
        $order_by = $request->get('order_by' , 'doctor_calenders.fa_data');
        $sort = $request->get('sort','DESC');
        $page = $request->get('page');
        $per_page = $request->get('per_page',7);

        $filter_start_date = $request->get('filter_start_date',change_number(jdate('Y-m-d')));
        $filter_end_date = $request->get('filter_end_date');
        $filter_visit_type = $request->get('filter_visit_type');

        $filter_partner = $request->get('filter_partner_id',null);
        $filter_office = $request->get('filter_office_id',null);

        if($page == null){
            $request->merge(['page'=>1]);
        }
        $doctorCalendars = DoctorCalender::where('user_id', $user->id)
            ->leftJoin('partners',function($join) {
                $join->on('partners.id', '=', 'doctor_calenders.partner_id');
            })
            ->leftJoin('doctor_offices',function($join) {
                $join->on('doctor_offices.id', '=', 'doctor_calenders.office_id');
            })
            ->when($filter_partner,function ($query) use ($filter_partner){
                $query->where('partner_id', $filter_partner);
            })
            ->when($filter_office,function ($query) use ($filter_office){
                $query->where('office_id', $filter_office);
            })
            ->when($filter_start_date,function ($query) use ($filter_start_date){
                $query->whereDate('fa_data', '>=',$filter_start_date);
            })
            ->when($filter_end_date,function ($query) use ($filter_end_date){
                $query->whereDate('fa_data', '<=',$filter_end_date);
            })
            ->orderBy($order_by, $sort)
            ->select('doctor_calenders.id as key', 'fa_data', 'data', 'time', 'capacity'
                     ,'partners.short_name as partner_name','doctor_offices.title as office_name' ,'reservation', 'price','type as visit_type',)
            ->paginate($per_page);

        return success_template($doctorCalendars);

    }

    public function show($id)
    {
        $calendar = DoctorCalender::where('user_id', auth()->id())->findOrFail($id);

        return success_template($calendar);
    }

    public function inPersonStore(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'capacity' => 'required|max:6',
            'price' => 'nullable|numeric|min:100000',
            'office_id' => 'required',
        ]);

        $dates = $request->input('date');
        $capacity = $request->input('capacity');
        $price = $request->input('price');
        $office_id = $request->input('office_id');

        DB::beginTransaction();
        try {
            foreach ($dates as $date) {

                $jdate = explode('-', $date['value']);
                $gdate = jalali_to_gregorian($jdate[0], $jdate[1], $jdate[2], '-');

                foreach ($date['times'] as $time) {
                    $temp = DoctorCalender::where('user_id',auth()->id())
                        ->where('fa_data',$date['value'])->where('time',$time)
                        ->where('data',$gdate)->where('reservation',0)->first();

                    $calendar = $temp ?? new DoctorCalender();

                    $calendar->user_id = auth()->id();
                    $calendar->created_user_id = auth()->id();
                    $calendar->fa_data = $date['value'];
                    $calendar->data = $gdate;
                    $calendar->time = $time;
                    $calendar->capacity = $capacity;
                    $calendar->office_id = $office_id;
                    $calendar->reservation = 0;
                    $calendar->original_price = $price;
                    $calendar->off_price = 0;
                    $calendar->price = $price;
                    $calendar->original_dollar_price = 0;
                    $calendar->off_dollar_price = 0;
                    $calendar->dollar_price = 0;
                    $calendar->type = VisitTypeEnum::type('in-person');
                    $calendar->partner_price = 0;
                    $calendar->has_prescription = 0;
                    $calendar->partner_id = 0;
                    $calendar->save();
                    if ($dates[0]['value'] == $calendar->fa_data && $date['times'][0] == $calendar->time)
                        SetTimeNotificationEvent::dispatch($calendar);

                }
            }
            DB::commit();

        }catch (\Exception $exception){
            return error_template($exception->getMessage());
            DB::rollBack();
        }
        return success_template(['message' => 'با موفقیت ثبت شد']);
    }

    public function onlineStore(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'capacity' => 'required|max:6',
            'visit_type' => 'required',//visit type: immediate = 2, normal = 1 , 48 hours = 3
            'consultation_type' => 'required', // chat:1 ,voice:2 ,video:3
            'price' => 'nullable|numeric|max:1000000',
            'partner_id' => 'nullable',
        ]);

        $dates = $request->input('date');
        $capacity = $request->input('capacity');
        $price = $request->input('price');
        $visit_type = $request->input('visit_type');
        $consultation_type = json_encode($request->input('consultation_type'));
        $partner_id = $request->get('partner_id',0);

        DB::beginTransaction();
        try {
            foreach ($dates as $date) {

                $jdate = explode('-', $date['value']);
                $gdate = jalali_to_gregorian($jdate[0], $jdate[1], $jdate[2], '-');

                foreach ($date['times'] as $time) {
                    $temp = DoctorCalender::where('user_id',auth()->id())
                        ->where('fa_data',$date['value'])->where('time',$time)
                        ->where('data',$gdate)->where('reservation',0)->first();

                    $calendar = $temp ?? new DoctorCalender();

                    $calendar->user_id = auth()->id();
                    $calendar->created_user_id = auth()->id();
                    $calendar->fa_data = $date['value'];
                    $calendar->data = $gdate;
                    $calendar->time = $time;
                    $calendar->capacity = $capacity;
                    $calendar->reservation = 0;
                    $calendar->original_price = $price;
                    $calendar->consultation_type = $consultation_type;
                    $calendar->off_price = 0;
                    $calendar->price = $price;
                    $calendar->original_dollar_price = 0;
                    $calendar->off_dollar_price = 0;
                    $calendar->dollar_price = 0;
                    $calendar->type = $visit_type;
                    $calendar->partner_price = 0;
                    $calendar->has_prescription = 0;
                    $calendar->partner_id = $partner_id;
                    $calendar->save();
                    if ($dates[0]['value'] == $calendar->fa_data && $date['times'][0] == $calendar->time)
                        SetTimeNotificationEvent::dispatch($calendar);
                }
            }
            DB::commit();

        }catch (\Exception $exception){
            return error_template($exception->getMessage());
            DB::rollBack();
        }
        return success_template(['message' => 'با موفقیت ثبت شد']);
    }

    public function extendPattern()
    {
        /**
         *    0 => "Sunday"
         *    1 => "Monday"
         *    2 => "Tuesday"
         *    3 => "Wednesday"
         *    4 => "Thursday"
         *    5 => "Friday"
         *    6 => "Saturday"
         **/

        $pattern = auth()->user()->pattern()->first();
        if ($pattern) {

            if (Carbon::today()->dayOfWeek != 6)
                $starting_point = Carbon::today()->subDays(Carbon::today()->dayOfWeek + 1);
            else
                $starting_point = Carbon::today();

            $duration = $pattern->duration;
            $capacity = $pattern->selectedCapacity;
            $type = $pattern->type;
            $price = $pattern->price;
            $days = $pattern->selectedWeekDays;
            $times = $pattern->selectedTime;
            $partner_id = $pattern->partner_id;

            $days = json_decode($days, true);
            $times = json_decode($times, true);


            if (Carbon::today()->dayOfWeek != 6) {
                $flag = true;
                foreach ($days as $day) {
                    if ($day['value'] >= Carbon::today()->dayOfWeek && $day['value']!=6) {
                        if ($day['value'] == Carbon::today()->dayOfWeek){
                            foreach ($times as $time){
                                if ($time['value'] < Carbon::now()->hour)
                                    continue;
                                else
                                {
                                    $flag=false;
                                    break;
                                }
                            }
                        }else {
                            $flag = false;
                            break;
                        }
                    }
                }
                if ($flag)
                    $starting_point = $starting_point->addDays(7);
            }


            DB::beginTransaction();
            try {
                for ($i = 1; $i <= $duration; $i++) {
                    for ($j = 1; $j <= 7; $j++) {
                        foreach ($days as $day) {
                            if ($starting_point->dayOfWeek == $day['value']) {
                                foreach ($times as $time) {
                                    $temp = DoctorCalender::where('user_id',auth()->id())
                                        ->where('fa_data',Verta::instance($starting_point)->format('Y-m-d'))
                                        ->where('time',$time['value'])
                                        ->where('data',$starting_point->format('Y-m-d'))->first();
                                    if (!$temp) {
                                        $calendar = new DoctorCalender();
                                        $calendar->user_id = auth()->id();
                                        $calendar->created_user_id = auth()->id();
                                        $calendar->fa_data = Verta::instance($starting_point)->format('Y-m-d');
                                        $calendar->data = $starting_point->format('Y-m-d');
                                        $calendar->time = $time['value'];
                                        $calendar->capacity = $capacity;
                                        $calendar->reservation = 0;
                                        $calendar->original_price = $price;
                                        $calendar->off_price = 0;
                                        $calendar->price = $price;
                                        $calendar->original_dollar_price = 0;
                                        $calendar->off_dollar_price = 0;
                                        $calendar->dollar_price = 0;
                                        $calendar->type = $type;
                                        $calendar->partner_price = 0;
                                        $calendar->partner_id = $partner_id;
                                        $calendar->save();
                                        if ($i == 1)
                                            SetTimeNotificationEvent::dispatch($calendar);
                                    }

                                }
                            }
                        }
                        $starting_point->addDays(1);
                    }
                }
                DB::commit();
                return $this->getInfo();
            } catch (\Exception $exception) {
                return error_template($exception->getMessage());
                DB::rollBack();
            }
        }
    }

    public function getExtendPattern()
    {
        $status = auth()->user()->pattern()->first() ? true : false;
        return success_template(['status'=>$status]);
    }

    public function increase()
    {
//        $id = \request()->input('id');
//        $calendar = DoctorCalender::find($id);
//
//        if ($calendar){
//
//            if ($calendar->capacity >= $calendar->reservation)
//            {
//
//            }
//        }
    }
}
