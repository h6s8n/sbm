<?php

namespace App\Http\Controllers\Api\v1\User\Visit;

use App\Enums\LanguageEnum;
use App\Enums\UserActivityLogEnum;
use App\Enums\VisitLogEnum;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\StarRate;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{

    protected $request;
    private $log;
    private $ActivityLog;

    public function __construct(Request $request,
                                VisitLogInterface $visitLog,
                                UserActivityLogInterface $activityLog)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->ActivityLog = $activityLog;
        $this->log = $visitLog;

        require(base_path('app/jdf.php'));

    }


    public function MeetingList()
    {

        $user = auth()->user();
        $this->ActivityLog->CreateLog($user,UserActivityLogEnum::LoadFirstPage);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.doctor_id')
            ->where('event_reserves.user_id', $user->id)
            ->where('event_reserves.status', 'active')
            ->whereNotIn('event_reserves.visit_status', ['refunded','cancel'])
            ->leftJoin('doctor_calenders' , function ($leftJoin_){
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            })
            ->orderBy('visit_status', 'desc')
            ->orderBy('event_reserves.fa_data', 'desc');
        if ($this->request->has('lang') && \request()->input('lang')){
            $EventList = $EventList->leftJoin('user_dictionaries', function ($leftJoin) {
                $leftJoin->on('users.id', 'user_dictionaries.user_id')
                    ->where('user_dictionaries.language_id',
                        LanguageEnum::getIdBySlug(request()->input('lang')));})->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.calender_id',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.doctor_id as doctor_key',
                'users.username as doctor_username',
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as doctor_name'),
                'users.picture as doctor_image',
                DB::raw('coalesce(user_dictionaries.job_title,users.job_title) as doctor_job_title'),
                DB::raw('coalesce(user_dictionaries.prefix,users.doctor_nickname) as doctor_nickname'),
                'doctor_calenders.type as type'
            )->get();
        }else
        $EventList=$EventList->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.calender_id',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.doctor_id as doctor_key',
                'users.username as doctor_username',
                'users.fullname as doctor_name',
                'users.picture as doctor_image',
                'users.job_title as doctor_job_title',
                'users.doctor_nickname',
                'doctor_calenders.type as type'
            )->get();

        $DoctorInt = [];
        $DoctorList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {

                if (!in_array($evant['doctor_username'], $DoctorInt)) {

                    $DoctorInt[] = $evant['doctor_username'];

                    $chat_count = Message::where('status', 'active')->where('seen_audience', 0)->where('user_id', $evant['doctor_key'])->where('audience_id', $user->id)->count();

                    $has_comment = StarRate::where('votable_id', $evant['doctor_key'])->where('user_id',$user->id)->first() ? true : false;

                    $doctor_name = $evant['doctor_name'];
                    if ($evant['doctor_nickname']) {
                        $doctor_name = $evant['doctor_nickname'] . ' ' . $doctor_name;
                    }

                    $DoctorList[$evant['doctor_username']] = [
                        'dr_info' => [
                            'doctor_job_title' => $evant['doctor_job_title'],
                            'doctor_image' => $evant['doctor_image'],
                            'doctor_username' => $evant['doctor_username'],
                            'doctor_name' => $doctor_name,
                            'doctor_key' => $evant['doctor_key'],
                            'chat_count' => $chat_count,
                            'has_comment' => $has_comment
                        ]
                    ];

                }


                $DoctorList[$evant['doctor_username']]['event_list'][] = $this->EvantFormat($evant, $user);

            }
        }


        return success_template($DoctorList);


    }

    public function getMeeting()
    {
        $user = auth()->user();
        $Event = EventReserves::join('users', 'users.id', '=', 'event_reserves.doctor_id')
            ->where('event_reserves.token_room', \request()->input('token'))
            ->leftJoin('doctor_calenders' , function ($leftJoin_){
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            })
            ->orderBy('visit_status', 'desc')
            ->orderBy('event_reserves.fa_data', 'desc');

        $Evant = $Event->select(
            'event_reserves.id as key',
            'event_reserves.token_room',
            'event_reserves.fa_data',
            'event_reserves.data',
            'event_reserves.calender_id',
            'event_reserves.time',
            'event_reserves.last_activity_doctor',
            'event_reserves.last_activity_user',
            'event_reserves.calender_id',
            'event_reserves.visit_status',
            'event_reserves.reserve_time',
            'event_reserves.doctor_id as doctor_key',
            'users.username as doctor_username',
            'users.fullname as doctor_name',
            'users.picture as doctor_image',
            'users.job_title as doctor_job_title',
            'users.doctor_nickname',
            'doctor_calenders.type as type'
        )->first();

        $DoctorInt = [];
        $DoctorList = [];

        if (!in_array($Evant['doctor_username'], $DoctorInt)) {

            $DoctorInt[] = $Evant['doctor_username'];

            $chat_count = Message::where('status', 'active')->where('seen_audience', 0)->where('user_id', $Evant['doctor_key'])->where('audience_id', $user->id)->count();

            $has_comment = StarRate::where('votable_id', $Evant['doctor_key'])->where('user_id',$user->id)->first() ? true : false;

            $doctor_name = $Evant['doctor_name'];
            if ($Evant['doctor_nickname']) {
                $doctor_name = $Evant['doctor_nickname'] . ' ' . $doctor_name;
            }

            $DoctorList[$Evant['doctor_username']] = [
                'dr_info' => [
                    'doctor_job_title' => $Evant['doctor_job_title'],
                    'doctor_image' => $Evant['doctor_image'],
                    'doctor_username' => $Evant['doctor_username'],
                    'doctor_name' => $doctor_name,
                    'doctor_key' => $Evant['doctor_key'],
                    'chat_count' => $chat_count,
                    'has_comment' => $has_comment
                ]
            ];

        }


        $DoctorList[$Evant['doctor_username']]['event_list'][] = $this->EvantFormat($Evant, $user);


        return success_template($DoctorList);

    }

    public function EvantFormat($evant, $user)
    {

        $dossiers_count = Dossiers::where('status', 'active')->where('event_id', $evant['key'])->where('seen_audience', 0)->where('audience_id', $user->id)->count();

        $capacity_time = 15;
        $min = 15;
        $my_reserf = 0;


        $calender = DoctorCalender::where('id', $evant['calender_id'])->first();
        if ($calender) {

            $capacity = (int)$calender->capacity;
            if ($capacity > 0) {

                $capacity = 60 / $capacity;
                $capacity = round($capacity, 0, PHP_ROUND_HALF_DOWN);
                $capacity_time = $capacity;

                $count = 1;
                $my_number_transaction = 1;
                $transaction_reserves = TransactionReserve::where('calender_id', $evant['calender_id'])->get();
                if ($transaction_reserves) {
                    foreach ($transaction_reserves as $item) {
                        if ($user->id === $item['user_id']) {
                            $my_number_transaction = $count;
                            $my_reserf = $count;
                        }
                        $count++;
                    }
                }

                if ($my_number_transaction > 1) $my_number_transaction = $my_number_transaction - 1;

                $min = $capacity * $my_number_transaction;

            }

        }

        $new_hours = (int)$evant['time'];
        if ($new_hours == 0) $new_hours = 24;

        $start_min = $min - $capacity_time;

        $start = Carbon::parse($evant['data'])->addHours($new_hours)->addMinutes($start_min);
        $st_start = false;
        if (jdate('Y-m-d', strtotime($start)) == jdate('Y-m-d')) {

            if (change_number(jdate('H', strtotime($start))) == change_number(jdate('H'))) {
                $st_start = true;


                $new_min = change_number(jdate('i'));
                $new_min = $new_min + 15;
                $start = Carbon::parse($evant['data'])->addHours($new_hours)->addMinutes(($start_min + $new_min));
                $start_hours = jdate('H:i', strtotime($start));
                $start_date = jdate('l - d F Y', strtotime($start));

            }

        }

        if (!$st_start) {
            $start_hours = jdate('H:i', strtotime($start));
            $start_date = jdate('l - d F Y', strtotime($start));
        }

        if ($evant['reserve_time']) {
            $start_hours = jdate('H:i', strtotime($evant['reserve_time']));
            $start_date = jdate('l - d F Y', strtotime($evant['reserve_time']));
        }

        $finish = Carbon::parse($evant['data'])->addHours($new_hours)->addMinutes($min);
        $finish_hours = jdate('H:i', strtotime($finish));
        $finish_date = jdate('l - d F Y', strtotime($finish));


        return [
            'token' => $evant['token_room'],
            'fa_data' => $evant['fa_data'],
            'data' => $evant['data'],
            'time' => $evant['time'],
            'start_date' => $start_date,
            'start_hours' => $start_hours,
            'finish_date' => $finish_date,
            'finish_hours' => $finish_hours,
            'min' => $min,
            'capacity_time' => $capacity_time,
            'last_activity_doctor' => $evant['last_activity_doctor'],
            'last_activity_user' => $evant['last_activity_user'],
            'visit_status' => $evant['visit_status'] == 'not_end' ? $evant['visit_status'] : 'end',
            'dossiers_count' => 0,
            'type' => $evant['type']
        ];
    }

    public function absenceOfDoctor(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);
        $user = auth()->user();
//        if (!$user->account_sheba)
//            return error_template('لطفا شماره شبای خود را تکمیل کنید سپس درخواست خود را ثبت نمایید');
        try {
            $event = EventReserves::where('token_room', $request->input('token'))
                ->where('visit_status', 'not_end')->first();
            $now = Carbon::now();
            $date = Carbon::create($event->reserve_time);
            if ($now >= $date->addHours(1)) {
                $event->visit_status = 'absence_of_doctor';
                $event->last_activity_user = Carbon::now();
                $event->save();
                $this->log->createLog($event,auth()->id(),VisitLogEnum::RefundRequest);
            } else
                return error_template('لطفا 1 ساعت پس از نوبت ویزیت درخواست خود را ثبت نمایید');
        } catch (\Exception $exception) {
            return error_template($exception->getMessage());
        }
        return success_template([
            'message' => 'درخواست شما با موفقیت ثبت شد'
        ]);
    }

    public function leftRoom($token)
    {
        $event = EventReserves::where('token_room',$token)->first();
        $event->duration = $this->request->input('visitTime') ?: 0;
        $event->save();
        $this->log->createLog($event,$event->user_id,VisitLogEnum::DoctorExit);

        return success_template(true);
    }


}
