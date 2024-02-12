<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Model\Visit\DoctorCalender;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmbedController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }

    public function getembed(){


        $user = User::where('approve' , 1)
            ->where('doctor_status' , 'active')
            ->where('status' , 'active')
            ->where('username' , $this->request->username)
            ->first();

        $full_times = [];

        if($user){

            return view('embed/getcode', ['user' => $user, 'param' => $this->request->param]);

        }

        return '';


    }

    public function GetCalender() {

        $user = User::where('approve' , 1)
            ->where('doctor_status' , 'active')
            ->where('status' , 'active')
            ->where('username' , $this->request->username)
            ->first();

        $full_times = [];

        if($user){

            $date = change_number(jdate('Y-m-d'));
            $dateTime = change_number(jdate('H'));

            $request = DoctorCalender::where('user_id' , $user->id)->where('fa_data'  , '>=', $date)->orderBy('data', 'ASC')
                ->select('id' , 'fa_data' , 'data' , 'time' , 'capacity' , 'reservation' , 'price' , 'original_price')->orderBy('time', 'asc')->get();
            $request_new = [];
            foreach ($request as $v){
                if($v['fa_data'] == $date){
                    if($v['time'] >= $dateTime){
                        $request_new[] = $v;
                    }
                }else{
                    $request_new[] = $v;
                }
            }

            for($i = 0 ; $i < 90 ; $i++){

                $dateTime = Carbon::now()->addDays($i);
                $dateTime = $dateTime->year . '/' . $dateTime->month . '/' . $dateTime->day;
                $fa_date = jdate('Y-m-d', strtotime($dateTime));
                $fa_date_lb = jdate('l - d F', strtotime($dateTime));
                $fa_date_full = jdate('Y/m/d', strtotime($dateTime));
                $weekDayVisit = [];
                $capacity = 0;
                foreach ($request_new as $v){
                    if($v['fa_data'] == $fa_date){
                        $weekDayVisit[] = $v;
                        $capacity_int = $v['capacity'] - $v['reservation'];
                        $capacity = $capacity_int + $capacity;
                    }
                }

                $full_times[] = [
                    'WeekDay' => $fa_date_lb,
                    'DateTimeFa' => $fa_date_full,
                    'DateTime' => $dateTime,
                    'Visits' => $weekDayVisit,
                    'CapacityCount' => $capacity,
                ];

            }

            return view('embed/calender', ['user' => $user, 'full_times' => $full_times]);

        }

        return '';

    }



    public function getAffiliateCode(){


        return view('embed/affiliate_code', ['tag' => $this->request->tag, 'param' => $this->request->param, 'code' => $this->request->code]);


    }

    public function GetAffiliate() {

        $user = User::where('token' , $this->request->code)
            ->first();

        $full_times = [];

        if($user){

            return view('embed/affiliate', ['user' => $user, 'tag' => $this->request->tag, 'code' => $this->request->code]);

        }

        return '';

    }


}
