<?php

namespace App\Http\Controllers\Api\v2\Visit\Doctor;

use App\Enums\LanguageEnum;
use App\Enums\UserActivityLogEnum;
use App\Enums\VisitTypeEnum;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;
use App\Repositories\v2\Visit\VisitLogInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
//use Illuminate\Pagination\Paginator;


class VisitController extends Controller
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


    public function MeetingList3($status = null)
    {
        $user = auth()->user();

        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::LoadFirstPage);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.user_id')
            ->where('event_reserves.doctor_id', $user->id)
//            ->where('event_reserves.status', 'active')
            ->whereNotIn('event_reserves.visit_status', ['refunded'])
            ->orderBy('event_reserves.reserve_time', 'desc')
            ->leftJoin('doctor_calenders' , function ($leftJoin_){
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            });

        if (\request()->has('lang') && \request()->input('lang'))
            $EventList = $EventList->leftJoin('user_dictionaries', function ($leftJoin) {
                $leftJoin->on('users.id', 'user_dictionaries.user_id')
                    ->where('user_dictionaries.language_id', LanguageEnum::getIdBySlug(request()->input('lang')));
            })->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as user_name'),
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image',
                'doctor_calenders.type',
                'doctor_calenders.type as type'
            );
        else
            $EventList = $EventList->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                'users.fullname as user_name',
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image',
                'doctor_calenders.type as type'
            );
//            ->orderBy('visit_status', 'desc')
        if ($status) {
            if ($status === "end")
                $EventList = $EventList->whereIn('event_reserves.visit_status', ['end','cancel'])->get();
            elseif($status === "all")
                $EventList = $EventList->whereIn('event_reserves.visit_status',
                    ['end','not_end','cancel'])->get();

        } else {
            $EventList = $EventList->where('event_reserves.visit_status', 'not_end')->get();
        }

        $UserInt = [];
        $UserList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {

                if (!in_array($evant['user_username'], $UserInt)) {

                    $UserInt[] = $evant['user_username'];

                    $chat_count = Message::where('seen_audience', 0)
                        ->where('user_id', $evant['user_key'])
                        ->where('audience_id', $user->id)->count();

                    $UserList[$evant['user_username']] = [
                        'user_info' => [
                            'user_image' => $evant['user_image'],
                            'user_username' => $evant['user_username'],
                            'user_name' => $evant['user_name'],
                            'user_ncode' => $evant['nationalcode'],
                            'user_key' => $evant['user_key'],
                            'user_mobile' => ($evant['user_show_phone']) ? $evant['user_mobile'] : '',
                            'user_gender' => ($evant['user_gender'] == 0) ? 'مرد' : 'زن',
                            'user_birthday' => ($evant['user_birthday']) ? $evant['user_birthday'] : '',
                            'chat_count' => $chat_count,
                        ]
                    ];

                }
                $UserList[$evant['user_username']]['event_list'][] = $this->EvantFormat($evant, $user);

            }
        }


        return success_template($UserList);


    }

    public function MeetingList2($status = null)
    {
        $user = auth()->user();

        $search = \request()->get('search',NULL);

        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::LoadFirstPage);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.user_id')
            ->where('event_reserves.doctor_id', $user->id)
//            ->where('event_reserves.status', 'active')
            ->whereNotIn('event_reserves.visit_status', ['refunded','cancel'])
            ->leftJoin('doctor_calenders' , function ($leftJoin_) {
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            })->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                'users.fullname as user_name',
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image',
                'doctor_calenders.type as type',
                'doctor_calenders.reservation as reservation'
            )
            ->orderBy('event_reserves.reserve_time', 'desc')
            ->orderBy('event_reserves.id', 'desc')
        ;
//            ->groupBy('event_reserves.user_id');

        if($search){
            $EventList = $EventList->where('users.fullname','LIKE','%'.$search.'%');
        }

        $has_time = true;
        $EventList = $EventList->get();

        $UserInt = [];
        $UserList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {
                if (!in_array($evant['user_username'], $UserInt)) {

                    $UserInt[] = $evant['user_username'];

                    $chat_count = Message::where('seen_audience', 0)
                        ->where('user_id', $evant['user_key'])
                        ->where('audience_id', $user->id)->count();

                    $UserList[$evant['user_username']] = [
                        'user_info' => [
                            'user_image' => $evant['user_image'],
                            'user_username' => $evant['user_username'],
                            'user_name' => $evant['user_name'],
                            'user_ncode' => $evant['nationalcode'],
                            'user_key' => $evant['user_key'],
                            'user_mobile' => ($evant['user_show_phone']) ? $evant['user_mobile'] : '',
                            'user_gender' => ($evant['user_gender'] == 0) ? 'مرد' : 'زن',
                            'user_birthday' => ($evant['user_birthday']) ? $evant['user_birthday'] : '',
                            'chat_count' => $chat_count,
                        ]
                    ];

                    $finish = Carbon::parse($evant['reserve_time'])->addHours(24)->addMinutes(15);
                    $finish_hours = jdate('H:i', strtotime($finish));
                    $finish_date = jdate('l - d F Y', strtotime($finish));

                    $UserList[$evant['user_username']]['event_list'][] = [
                        'token' => $evant['token_room'],
                        'fa_data' => $evant['fa_data'],
                        'data' => $evant['data'],
                        'time' => $evant['time'],
                        'start_date' => jdate('H:i', strtotime($evant['reserve_time'])),
                        'start_hours' => jdate('l - d F Y', strtotime($evant['reserve_time'])),
                        'finish_date' => $finish_date,
                        'finish_hours' => $finish_hours,
                        'min' => 15,
                        'my_reserf' => $evant['reservation'],
                        'capacity_time' => 15,
                        'last_activity_doctor' => $evant['last_activity_doctor'],
                        'last_activity_user' => $evant['last_activity_user'],
                        'visit_status' => $evant['visit_status'],
                        'dossiers_count' => 0,
                        'type'=>$evant['type']
                    ];

                }

            }
        }


        if ($status) {
            $data['data'] = $UserList;
            $e = array_map(static function ($UserList){
                $E['Future'] = [];
                $E['End'] = [];
                $E['Today'] = [];
                $E['Past'] = [];
                foreach ($UserList as $list => $user){
                    if ($user['event_list'][0]['visit_status'] === 'end'){
                        $E['End'][$list]['user_info'] = $user['user_info'];
                        $E['End'][$list]['event_list'][] = $user['event_list'][0];
                    }elseif($user['event_list'][0]['visit_status'] === 'not_end'){
                        if (date('Y-m-d',strtotime($user['event_list'][0]['data'])) == Carbon::now()->format('Y-m-d')){
                            $E['Today'][$list]['user_info'] = $user['user_info'];
                            $E['Today'][$list]['event_list'][] = $user['event_list'][0];
                        }elseif($user['event_list'][0]['data'] > Carbon::now()->format('Y-m-d')){
                            $E['Future'][$list]['user_info'] = $user['user_info'];
                            $E['Future'][$list]['event_list'][] = $user['event_list'][0];
                        }elseif($user['event_list'][0]['data'] < Carbon::now()->format('Y-m-d')){
                            $E['Past'][$list]['user_info'] = $user['user_info'];
                            $E['Past'][$list]['event_list'][] = $user['event_list'][0];
                        }
                    }
                }
                return $E;
            },$data);


            switch ($status) {
                case 'end':
                    $response['list'] = $e['data']['End'];
                    break;
                case 'today':
                    $response['list'] = $e['data']['Today'];
                    if (count($response['list']) === 0) {
                        $response['has_time'] = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '=', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
                case 'past':
                    $response['list'] = $e['data']['Past'];
                    break;
                case 'future':
                    $response['list'] = $e['data']['Future'];
                    if (count($response['list']) === 0) {
                        $response['has_time'] = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '>', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
            }
        }else{
            $response['list'] = $UserList;
        }

        $result = Collection::make($response['list']);

        $result = $result->paginate(\request()->get('per-page',30));

        $data = [
            'data' => $result->items(),
            'last_page' => $result->lastPage(),
            'total' => $result->total(),
            'has_time' => $response['has_time'] ?? null
        ];
        return success_template($data);

    }

    public function MeetingList($status = null)
    {
        $user = auth()->user();

        $search = \request()->get('search',NULL);

        $visit_type = \request()->get('visit_type',NULL);

        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::LoadFirstPage);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.user_id')
            ->where('event_reserves.doctor_id', $user->id)
//            ->where('event_reserves.status', 'active')
            ->whereNotIn('event_reserves.visit_status', ['refunded'])
//            ->orderBy('event_reserves.reserve_time', 'desc')
            ->leftJoin('doctor_calenders' , function ($leftJoin_) {
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            })->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                'users.fullname as user_name',
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image',
                'doctor_calenders.type as type',
                'doctor_calenders.reservation as reservation'
            )
//            ->groupBy('event_reserves.user_id')
//            ->orderBy('event_reserves.id', 'desc')
//            ->orderBy('event_reserves.reserve_time', 'desc')
        ;

        if($visit_type){
            $EventList = $EventList->where('type',VisitTypeEnum::type($visit_type));
        }

        if ($status){
            switch ($status) {
                case 'end':
                    $EventList = $EventList->whereIn('event_reserves.visit_status',['end','cancel'])
                        ->whereNotIn('event_reserves.user_id' , function ($q) use ($user){
                            $q->select('es.user_id')->from('event_reserves as es')
                                ->where('es.doctor_id',$user->id)
                                ->where('es.visit_status','not_end');
                        })
                        ->groupBy('event_reserves.user_id')
                        ->orderBy('event_reserves.finish_at', 'desc')->paginate(\request()->get('per-page',500));
                    break;
                case 'today':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->whereDate('event_reserves.data' , Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',300));
                    if (count($EventList) === 0) {
                        $has_time = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '=', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
                case 'past':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->whereDate('event_reserves.data' ,'<', Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',500));
                    break;
                case 'future':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->whereDate('event_reserves.data' ,'>', Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',300));
                    if (count($EventList) === 0) {
                        $has_time = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '>', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
            }
        }

        if($search){
            $EventList = $EventList->where('users.fullname','LIKE','%'.$search.'%')->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',30));
        }

        $UserInt = [];
        $UserList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {
                if (!in_array($evant['user_username'], $UserInt)) {

                    $UserInt[] = $evant['user_username'];

                    $chat_count = Message::where('seen_audience', 0)
                        ->where('user_id', $evant['user_key'])
                        ->where('audience_id', $user->id)->count();

                    $UserList[$evant['user_username']] = [
                        'user_info' => [
                            'user_image' => $evant['user_image'],
                            'user_username' => $evant['user_username'],
                            'user_name' => $evant['user_name'],
                            'user_ncode' => $evant['nationalcode'],
                            'user_key' => $evant['user_key'],
                            'user_mobile' => ($evant['user_show_phone']) ? $evant['user_mobile'] : '',
                            'user_gender' => ($evant['user_gender'] == 0) ? 'مرد' : 'زن',
                            'user_birthday' => ($evant['user_birthday']) ? $evant['user_birthday'] : '',
                            'chat_count' => $chat_count,
                        ]
                    ];

                    $finish = Carbon::parse($evant['reserve_time'])->addHours(24)->addMinutes(15);
                    $finish_hours = jdate('H:i', strtotime($finish));
                    $finish_date = jdate('l - d F Y', strtotime($finish));

                    $UserList[$evant['user_username']]['event_list'][] = [
                        'token' => $evant['token_room'],
                        'fa_data' => $evant['fa_data'],
                        'data' => $evant['data'],
                        'time' => $evant['time'],
                        'start_date' => jdate('H:i', strtotime($evant['reserve_time'])),
                        'start_hours' => jdate('l - d F Y', strtotime($evant['reserve_time'])),
                        'finish_date' => $finish_date,
                        'finish_hours' => $finish_hours,
                        'min' => 15,
                        'my_reserf' => $evant['reservation'],
                        'capacity_time' => 15,
                        'last_activity_doctor' => $evant['last_activity_doctor'],
                        'last_activity_user' => $evant['last_activity_user'],
                        'visit_status' => $evant['visit_status'] == 'not_end' ? $evant['visit_status'] : 'end',
                        'dossiers_count' => 0,
                        'type'=>$evant['type']
                    ];

                }

            }
        }

//dd($EventList);
//        $result = Collection::make($UserList);
//
//        $result = $result->paginate(\request()->get('per-page',30));

        $data = [
            'data' => $UserList,
            'last_page' => $EventList->lastPage(),
            'total' => $EventList->total(),
            'has_time' => $has_time ?? null
        ];
        return success_template($data);

    }

    public function secretary_finish()
    {
        $token = $this->request->get('token');
        $event = EventReserves::whereStatus('active')->where('token_room' , $token)->first();

        if ($event){
            $event->status = 'secretary_end';
            $event->save();
            return success_template(['message' => 'ok']);
        }
        return error_template('ویزیت یافت نشد');
    }

    public function secretaryMeetingList($status = null)
    {
        $user = auth()->user();

        $search = \request()->get('search',NULL);

        $visit_type = \request()->get('visit_type',NULL);

        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::LoadFirstPage);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.user_id')
            ->where('event_reserves.doctor_id', $user->id)
//            ->where('event_reserves.status', 'active')
            ->whereNotIn('event_reserves.visit_status', ['refunded','cancel'])
//            ->orderBy('event_reserves.reserve_time', 'desc')
            ->leftJoin('doctor_calenders' , function ($leftJoin_) {
                $leftJoin_->on('event_reserves.calender_id','doctor_calenders.id');
            })->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                'users.fullname as user_name',
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image',
                'doctor_calenders.type as type',
                'doctor_calenders.reservation as reservation'
            )
//            ->groupBy('event_reserves.user_id')
//            ->orderBy('event_reserves.id', 'desc')
//            ->orderBy('event_reserves.reserve_time', 'desc')
        ;

        if($visit_type){
            $EventList = $EventList->where('type',VisitTypeEnum::type($visit_type));
        }

        if ($status){
            switch ($status) {
                case 'end':
                    $EventList = $EventList
//                        ->where('event_reserves.visit_status','end')
                        ->where('event_reserves.status','secretary_end')
//                        ->whereNotIn('event_reserves.user_id' , function ($q) use ($user){
//                            $q->select('es.user_id')->from('event_reserves as es')
//                                ->where('es.doctor_id',$user->id)
//                                ->where('es.visit_status','not_end');
//                        })
                        ->groupBy('event_reserves.user_id')
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',500));
                    break;
                case 'today':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->where('event_reserves.status','active')
                        ->whereDate('event_reserves.data' , Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',300));
                    if (count($EventList) === 0) {
                        $has_time = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '=', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
                case 'past':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->where('event_reserves.status','active')
                        ->whereDate('event_reserves.data' ,'<', Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',500));
                    break;
                case 'future':
                    $EventList = $EventList->where('event_reserves.visit_status','not_end')
                        ->where('event_reserves.status','active')
                        ->whereDate('event_reserves.data' ,'>', Carbon::now()->format('Y-m-d'))
                        ->orderBy('event_reserves.reserve_time', 'desc')->paginate(\request()->get('per-page',300));
                    if (count($EventList) === 0) {
                        $has_time = DB::table('doctor_calenders')->where("user_id", $user->id)
                            ->where("capacity", '>', "reservation")
                            ->whereDate('data', '>', Carbon::now()->format('Y-m-d'))->exists();
                    }
                    break;
            }
        }

        if($search){
            $EventList = $EventList->where('users.fullname','LIKE','%'.$search.'%')->paginate(\request()->get('per-page',30));
        }

        $UserInt = [];
        $UserList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {
                if (!in_array($evant['user_username'], $UserInt)) {

                    $UserInt[] = $evant['user_username'];

                    $chat_count = Message::where('seen_audience', 0)
                        ->where('user_id', $evant['user_key'])
                        ->where('audience_id', $user->id)->count();

                    $UserList[$evant['user_username']] = [
                        'user_info' => [
                            'user_image' => $evant['user_image'],
                            'user_username' => $evant['user_username'],
                            'user_name' => $evant['user_name'],
                            'user_ncode' => $evant['nationalcode'],
                            'user_key' => $evant['user_key'],
                            'user_mobile' => ($evant['user_show_phone']) ? $evant['user_mobile'] : '',
                            'user_gender' => ($evant['user_gender'] == 0) ? 'مرد' : 'زن',
                            'user_birthday' => ($evant['user_birthday']) ? $evant['user_birthday'] : '',
                            'chat_count' => $chat_count,
                        ]
                    ];

                    $finish = Carbon::parse($evant['reserve_time'])->addHours(24)->addMinutes(15);
                    $finish_hours = jdate('H:i', strtotime($finish));
                    $finish_date = jdate('l - d F Y', strtotime($finish));

                    $UserList[$evant['user_username']]['event_list'][] = [
                        'token' => $evant['token_room'],
                        'fa_data' => $evant['fa_data'],
                        'data' => $evant['data'],
                        'time' => $evant['time'],
                        'start_date' => jdate('H:i', strtotime($evant['reserve_time'])),
                        'start_hours' => jdate('l - d F Y', strtotime($evant['reserve_time'])),
                        'finish_date' => $finish_date,
                        'finish_hours' => $finish_hours,
                        'min' => 15,
                        'my_reserf' => $evant['reservation'],
                        'capacity_time' => 15,
                        'last_activity_doctor' => $evant['last_activity_doctor'],
                        'last_activity_user' => $evant['last_activity_user'],
                        'visit_status' => $evant['visit_status'],
                        'status' => $evant['status'],
                        'dossiers_count' => 0,
                        'type'=>$evant['type']
                    ];

                }

            }
        }

//dd($EventList);
//        $result = Collection::make($UserList);
//
//        $result = $result->paginate(\request()->get('per-page',30));

        $data = [
            'data' => $UserList,
            'last_page' => $EventList->lastPage(),
            'total' => $EventList->total(),
            'has_time' => $has_time ?? null
        ];
        return success_template($data);

    }


    public function EvantFormat($evant, $user)
    {
//        $dossiers_count = Message::where('audience_id', $user->id)
//            ->where('user_id',$evant['user_id'])
//            ->where('type','dossierFile')
//            ->where('seen_audience', 0)
//            ->count();
//        $dossiers_count = Dossiers::where('status', 'active')
//            ->where('seen_audience', 0)
//            ->where('audience_id', $user->id)->count();
        $dossiers_count = 0;

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
                        if ($evant['user_key'] === $item['user_id']) {
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

        $fa_date_part = explode('-', $evant['fa_data']);
        $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

        $start = Carbon::parse($en_date)->addHours($new_hours)->addMinutes($start_min);
        $st_start = false;
        if (jdate('Y-m-d', strtotime($start)) == jdate('Y-m-d')) {

            if (change_number(jdate('H', strtotime($start))) == change_number(jdate('H'))) {
                $st_start = true;

                $new_min = change_number(jdate('i'));
                $new_min = $new_min + 15;
                $start = Carbon::parse($en_date)->addHours($new_hours)->addMinutes(($start_min + $new_min));
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

        $finish = Carbon::parse($en_date)->addHours($new_hours)->addMinutes($min);
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
            'my_reserf' => $my_reserf,
            'capacity_time' => $capacity_time,
            'last_activity_doctor' => $evant['last_activity_doctor'],
            'last_activity_user' => $evant['last_activity_user'],
            'visit_status' => $evant['visit_status'],
            'dossiers_count' => $dossiers_count,
            'type'=>$evant['type']
        ];
    }
}
