<?php

namespace App\Http\Controllers\Api\v1\Search;

use App\Enums\LanguageEnum;
use App\Enums\VisitTypeEnum;
use App\Model\Doctor\DoctorDetail;
use App\Model\Doctor\Specialization;
use App\Model\Platform\State;
use App\Model\User\Skills;
use App\Model\User\Specialties;
use App\Model\Visit\DoctorCalender;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use function foo\func;

class SearchController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        ob_end_clean();
        require(base_path('app/jdf.php'));
    }


    public function search_old()
    {
        $keywords[] = $this->request->input('search');
        $remove_lists = [
            'آقای' => '',
            'خانم' => '',
            'خانوم' => '',
            'پزشک ' => '',
            'دکتر' => '',
            'شدید' => '',
            'حالت' => '',
            'چرا' => '',
            'دکتر های' => '',
            'دکتر ای' => '',
            'دکتر ها' => '',
            'دکتر ای' => '',
            'دکتر' => '',
            'مشاوره آنلاین' => '',
            'متخصص' => '',
            'مشاوره پزشکی آنلاین' => '',
            'مشاوره پزشکی' => '',
            'پزشکی ' => '',
            'پزشکی های' => '',
            'پزشکی ها' => '',
            'پزشکی ای' => '',
            'متخصص های' => '',
            'متخصص ها' => '',
            'متخصص ای' => '',
            'دکتران' => '',
            'دکترها' => '',
            'جون' => '',
            ' جون' => ''
        ];
        $replacing = [
            'یی' => 'ئی',
            'ک' => 'ك',
            'ي' => 'ی',
            'آ' => 'ا',
        ];

        foreach ($remove_lists as $k => $item) {
            $keywords = str_replace($k, $item, $keywords);
        }
        $keywords[0] = trim($keywords[0]);
        foreach ($replacing as $key => $replace) {
            if (strpos($keywords[0], $key))
                array_push($keywords, str_replace($key, $replace, $keywords[0]));
            elseif (strpos($keywords[0], $replace))
                array_push($keywords, str_replace($replace, $key, $keywords[0]));
        }
        array_push($keywords, ' ' . $keywords[0] . ' ');
        //  return success_template($keywords);
//        if (mb_substr($keywords[0], 0, 1) == "ا") {
//            $key = "آ" . mb_substr($keywords[0], 1);
//            array_unshift($keywords,$key);
//        }
        $data = User::
        select(DB::raw('min(data) as data,min(time) as time'),
            DB::raw('AVG(overall) as avg_overall'),
            DB::raw('AVG(quality) as avg_quality'),
            DB::raw('AVG(cost) as avg_cost'),
            DB::raw('AVG(behaviour) as avg_behaviour'),
            'price',
            'badge.plan as badges',
            'users.id',
            'users.name', 'users.family', 'users.fullname', 'users.job_title',
            'users.username', 'users.doctor_nickname',
            'users.picture', 'sp.name as special_name', 'states.state', 'users.gender',
            'sp_gp', 'users.bio', 'tags.items', 'price','type')
            ->where('approve', 1)
            ->where('doctor_status', 'active')
            ->whereIn('status', ['imported', 'active'])
            ->whereNotIn('users.id', TestAccount())
            ->leftJoin('star_rates as stars', function ($leftJoin) {
                $leftJoin->on('stars.votable_id', 'users.id')
                    ->where('votable_type', 'App\User');
            })
            ->where('users.gender', 'LIKE', (($this->request->has('gender')) &&
            ($this->request->input('gender')) ?
                ($this->request->input('gender') == 'زن' ? 1 : 0) : '%'))
            ->leftJoin(DB::raw('(select * from doctor_calenders where DATE(data) >= "' . Carbon::now()->format('Y-m-d') . '" order by DATE(data) ASC) calenders'), function ($leftJoin) {
                $leftJoin->on('calenders.user_id', 'users.id')
                    ->whereDate('calenders.data', '>=', DB::raw(
                        'IF( EXISTS(
                                SELECT id
                                FROM doctor_calenders
                                 WHERE user_id = users.id and DATE(data) = "' . Carbon::now()->format('Y-m-d') . '" and time > ' . Carbon::now()->hour . ' limit 1) ,
                                 "' . Carbon::now()->format('Y-m-d') . '","' . Carbon::now()->addDays(1)->format('Y-m-d') . '")'
                    ))
                    ->where('calenders.time', '>', DB::raw(
                        'IF( EXISTS(
                                SELECT id
                                FROM doctor_calenders
                                 WHERE user_id = users.id and DATE(data) = "' . Carbon::now()->format('Y-m-d') . '" and time > ' . Carbon::now()->hour . ' limit 1) , ' . Carbon::now()->hour . ',-1)'
                    ))
                    ->where('capacity', '>', DB::raw('reservation'));

                if ((request()->has('partner') && \request()->input('partner'))) {
                    $leftJoin->where('partner_id', \request()->input('partner'));
                }

                $leftJoin->orderBy('data', 'ASC')
                    ->orderBy('time', 'ASC');
            })
            ->join('user_specializations as us', 'users.id', 'us.user_id')
            ->join('specializations as sp', function ($join) {
                $join->on('sp.id', 'us.specialization_id')
                    ->Where(function ($query) {
                        $query->where('sp.slug', 'LIKE', request()->has('proficiency') && request()->input('proficiency')
                            ? request()->input('proficiency') : '%')->
                        orWhere('sp.name', 'LIKE', request()->has('proficiency') && request()->input('proficiency')
                            ? request()->input('proficiency') : '%')
                            ->where('language_id',
                                request()->has('lang') && request()->has('lang') ?
                                    LanguageEnum::getIdBySlug(request()->input('lang'))
                                    : LanguageEnum::Farsi);
                    });
            })
            ->leftJoin('tags', function ($join) {
                $join->on('sp.id', 'searchable_id')
                    ->Where('searchable_type', 'App\Model\Doctor\Specialization');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id');
                if (!request()->has('proficiency')
                    || !request()->has('partner')) {
                    $join->where('badge.priority', '>', 500);
                }
            })
            ->join('states', function ($join) {
                $join->on('state_id', 'states.id')
                    ->where('states.id', 'LIKE', request()->has('state') && request()->input('state')
                        ? request()->input('state') : '%');
            })
            ->groupBy('calenders.user_id')
            ->groupBy('users.id')
            ->groupBy('us.user_id')
            ->orderBy('badge.priority' , 'DESC')
            ->orderByRaw('time IS NULL')
            ->orderBy('data', 'ASC');

        if (\request()->has('order') && \request()->input('order')){
            $order = \request()->input('order');
            switch ($order){
                case 'cheapest' : {
                    $data = $data->orderBy('price','ASC');
                    break;
                }
                case 'expensive' : {
                    $data = $data->orderBy('price','DESC');
                    break;
                }
                default :{
                    $data=$data->orderBy('time', 'asc');
                    break;
                }
            }
        }else{
            $data=$data->orderBy('time', 'asc');
        }
        if (request()->has('partner') && \request()->input('partner')) {
            $data = $data->join('partner_doctors as pd', 'users.id', 'pd.user_id')
                ->join('partners as ps', function ($join) {
                    $join->on('ps.id', 'pd.partner_id')
                        ->Where(function ($query) {
                            $query->where('ps.id', request()->input('partner'));
                        });
                });
        }

        if ($this->request->has('search') && \request()->input('search')) {
            $data = $data->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('fullname', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.name', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.family', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.sp_gp', "LIKE", "%" . $keyword . "%")
                        ->orWhere('sp.name', "LIKE", "%" . $keyword . "%")
                        ->orWhere('tags.items', 'LIKE', '%,' . $keyword . ',%')
                        ->orWhere('tags.items', 'LIKE', '%,' . $keyword)
                        ->orWhere('special_json', 'LIKE', '%' . $keyword . '%');
                }
            });
            if ($this->request->has('state') && $this->request->input('state'))
                $data = $data->orderByRaw('state IS NULL')->orderBy('state', 'ASC');
            //$data->paginate(16);
        }
        if ($this->request->has('city') &&
            $this->request->input('city') &&
            $this->request->has('state') &&
            $this->request->input('state'))
            $data = $data->where('city_id', $this->request->input('city'))
            ->groupBy('users.id');

        $data = $data->paginate(\request()->has('per-page') && \request()->input('per-page') ?
            \request()->input('per-page') : 16);
        foreach ($data as $doctor) {
            $doctor->diff = Carbon::parse($doctor->nearest_time)
                ->diffInDays(Carbon::now()->format('Y-m-d'));
        }
        return $data;
    }

    public function search()
    {

        $keywords[] = $this->request->input('search');
        $remove_lists = [
            'آقای' => '',
            'خانم' => '',
            'خانوم' => '',
            'پزشک ' => '',
            'دکتر' => '',
            'شدید' => '',
            'حالت' => '',
            'چرا' => '',
            'دکتر های' => '',
            'دکتر ای' => '',
            'دکتر ها' => '',
            'دکتر ای' => '',
            'دکتر' => '',
            'مشاوره آنلاین' => '',
            'متخصص' => '',
            'مشاوره پزشکی آنلاین' => '',
            'مشاوره پزشکی' => '',
            'پزشکی ' => '',
            'پزشکی های' => '',
            'پزشکی ها' => '',
            'پزشکی ای' => '',
            'متخصص های' => '',
            'متخصص ها' => '',
            'متخصص ای' => '',
            'دکتران' => '',
            'دکترها' => '',
            'جون' => '',
            ' جون' => ''
        ];
        $replacing = [
            'یی' => 'ئی',
            'ک' => 'ك',
            'ي' => 'ی',
            'آ' => 'ا',
        ];

        foreach ($remove_lists as $k => $item) {
            $keywords = str_replace($k, $item, $keywords);
        }
        $keywords[0] = trim($keywords[0]);
        foreach ($replacing as $key => $replace) {
            if (strpos($keywords[0], $key))
                array_push($keywords, str_replace($key, $replace, $keywords[0]));
            elseif (strpos($keywords[0], $replace))
                array_push($keywords, str_replace($replace, $key, $keywords[0]));
        }
        array_push($keywords, ' ' . $keywords[0] . ' ');
        //  return success_template($keywords);
        if (mb_substr($keywords[0], 0, 1) == "ا") {
            $key = "آ" . mb_substr($keywords[0], 1);
            array_unshift($keywords,$key);
        }
//        DB::connection()->enableQueryLog();
        $data = User::select(
        'users.name', 'users.family', 'users.fullname', 'users.job_title',
        'users.username', 'users.doctor_nickname',
        'users.picture', 'sp.name as special_name', 'users.gender','users.state_id','users.city_id',
        'sp_gp', 'users.bio'
    )
    ->where('approve', 1)
    ->where('doctor_status', 'active')
    ->whereIn('status', ['imported', 'active'])
    ->whereNotIn('users.id', TestAccount())
    ->where('users.gender', 'LIKE', ($this->request->has('gender') && $this->request->input('gender')) ?
            ($this->request->input('gender') == 'زن' ? 1 : 0) : '%')
    ->join('user_specializations as us', 'users.id', 'us.user_id')
    ->join('specializations as sp', function ($join) {
        $join->on('sp.id', 'us.specialization_id')
            ->where(function ($query) {
                $query->where('sp.slug', 'LIKE', request()->has('proficiency') && request()->input('proficiency') ?
                        request()->input('proficiency') : '%')
                    ->orWhere('sp.name', 'LIKE', request()->has('proficiency') && request()->input('proficiency') ?
                        request()->input('proficiency') : '%')
                    ->where('language_id', request()->has('lang') && request()->input('lang') ?
                        LanguageEnum::getIdBySlug(request()->input('lang')) :
                        LanguageEnum::Farsi);
            });
    })
    ->groupBy('users.id');

if (\request()->has('order') && \request()->input('order')) {
    $order = \request()->input('order');
    switch ($order) {
        case 'cheapest': {
            $data = $data->orderBy('price', 'ASC');
            break;
        }
        case 'expensive': {
            $data = $data->orderBy('price', 'DESC');
            break;
        }
        default: {
            $data = $data->orderBy('time', 'asc');
            break;
        }
    }
}

if (\request()->has('partner') && \request()->input('partner')) {
    $data = $data->join('partner_doctors as pd', 'users.id', 'pd.user_id')
        ->join('partners as ps', function ($join) {
            $join->on('ps.id', 'pd.partner_id')
                ->where(function ($query) {
                    $query->where('ps.id', request()->input('partner'));
                });
        });
}

if ($this->request->has('search') && \request()->input('search')) {
    $data = $data->where(function ($query) use ($keywords) {
        foreach ($keywords as $keyword) {
            $query->where('fullname', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.family', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.sp_gp', 'LIKE', '%' . $keyword . '%')
                ->orWhere('sp.name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('special_json', 'LIKE', '%' . $keyword . '%');
        }
    });
}

if ($this->request->has('state') && $this->request->input('state')) {
    $data = $data->where('users.state_id', $this->request->input('state'));
}

if ($this->request->has('city') && $this->request->input('city')) {
    $data = $data->where('users.city_id', $this->request->input('city'));
}

$data = $data->paginate(\request()->has('per-page') && \request()->input('per-page') ?
    \request()->input('per-page') : 16);

foreach ($data as $doctor) {
    $doctor->diff = Carbon::parse($doctor->nearest_time)
        ->diffInDays(Carbon::now()->format('Y-m-d'));
}

return $data;
    }

    public function doctors()
    {

        $request = [];
        $where_array = array();

        $filter_name = trim($this->request->get('search'));
        $filter_proficiency = trim($this->request->get('proficiency'));
        $filter_skill = trim($this->request->get('skill'));
        $filter_gender = trim($this->request->get('gender'));
        $filter_state = trim($this->request->get('state'));

        $filter_name = $filter_name . ' ' . $filter_proficiency . ' ' . $filter_skill;

        $filter_gender = trim($this->request->get('gender'));
        if ($filter_gender) {
            switch ($filter_gender) {
                case 'مرد' :
                    $where_array[] = array('gender', 0);
                    break;
                case 'زن' :
                    $where_array[] = array('gender', 1);
                    break;
            }

        }

        $filter_state = trim($this->request->get('state'));
        if ($filter_state) {
            $request = State::where('state', $filter_state)->first();
            if ($request) {
                $where_array[] = array('state_id', $request->id);
            } else {
                $where_array[] = array('state_id', 0);
            }
        }

        $request = User::where('users.approve', 1)
            ->where('doctor_status', 'active')
            ->whereIn('users.status', ['active', 'imported'])
            ->where($where_array)
            ->where(function ($query) use ($filter_name) {
                $query->search($filter_name, false);
            })
            ->select(
                'users.id',
                'users.fullname',
                'users.username',
                'users.gender',
                'users.doctor_nickname',
                'users.bio',
                'users.picture',
                'users.job_title',
                'users.skill_json',
                'users.special_json'
            )
            ->ordered(jdate('Y-m-d'))
            ->latest()
            ->paginate(32);


        $users = [];
        if ($request) {
            foreach ($request as $item) {
                $users[] = $item['id'];
            }
        }

        $online = [];
        $onlineCalender = [];
        $firstVisit = [];
        $firstVisitTIme = [];
        $firstVisitDate = [];
        $online_user = DoctorCalender::whereIn('user_id', $users)->where('fa_data', '>=', change_number(jdate('Y-m-d')))->select('user_id', 'fa_data', 'time')->orderBy('fa_data', 'asc')->orderBy('time', 'desc')->get();

        if ($online_user) {
            foreach ($online_user as $item) {
                if (!in_array($item['user_id'], $onlineCalender)) {
                    $onlineCalender[] = $item['user_id'];
                    $firstVisit[$item['user_id']] = $item['fa_data'] . ' ساعت ' . $item['time'];
                    $firstVisitDate[$item['user_id']] = $item['fa_data'];
                    $firstVisitTIme[$item['user_id']] = (int)$item['time'];
                } else {
                    if (isset($firstVisitTIme[$item['user_id']]) && $firstVisitTIme[$item['user_id']] > ((int)$item['time']) && $firstVisitDate[$item['user_id']] == $item['fa_data']) {
                        if (((int)$item['time']) > ((int)date('H', strtotime('-1 hours')))) {
                            $firstVisit[$item['user_id']] = $item['fa_data'] . ' ساعت ' . $item['time'];
                            $firstVisitTIme[$item['user_id']] = (int)$item['time'];
                        }

                    }
                }
            }
        }

        $online_user = DoctorCalender::whereIn('user_id', $users)->where('fa_data', '=', change_number(jdate('Y-m-d')))->where('time', '<=', '24')->where('time', '>=', date('H'))->select('user_id', 'time')->orderBy('time', 'desc')->get();


        if ($online_user) {
            foreach ($online_user as $item) {
                if (!in_array($item['user_id'], $online)) {
                    if ($item['time'] == date('H')) {
                        $online[] = $item['user_id'];
                    }
                }
            }
        }


        $State = State::orderBy('state', 'ASC')->get();
        $skills = Skills::orderBy('name', 'asc')->select('id as value', 'name as label')->get();
        $specialties = specialties_array();

        return success_template(['search' => $request, 'state' => $State, 'skills' => $skills, 'specialties' => $specialties, 'online' => $online, 'onlineCalender' => $onlineCalender, 'firstVisit' => $firstVisit]);

    }

    public function profile()
    {

        $username = ($this->request->username) ? $this->request->username : '';
        $request = User::where('approve', 1)
            ->where('doctor_status', 'active')
            ->whereIn('status', ['active', 'imported'])
            ->where('username', $username)
            ->select(
                'id',
                'fullname',
                'username',
                'gender',
                'bio',
                'address',
                'picture',
                'job_title',
                'doctor_nickname',
                'specialcode',
                'skill_json',
                'special_json'
            )
            ->first();

        $online = false;
        if ($request) {

            $online_user = DoctorCalender::where('user_id', $request->id)
                ->where('fa_data', '=', change_number(jdate('Y-m-d')))
                ->where('time', '<=', '24')->where('time', '>=', date('H'))
                ->orderBy('time', 'desc')->first();
            if ($online_user) {
                if ($online_user->time == date('H')) {
                    $online = true;
                }
            }
        }
        $details = DoctorDetail::where('user_id', $request->id)->first();
        if ($details) {
            $faqs = $details->user->FAQs()->get();
            $faqs = $faqs->transform(function ($faq) {
                unset($faq->questionable_type);
                return $faq;
            });
        } else
            $faqs = null;

        $details_list = ($details) ? [
            'title' => $details->title,
            'content' => $details->content,
            'description' => $details->description,
            'video_url' => $details->video_url
        ] : null;

        return success_template([
            'data' => $request,
            'details' => $details_list,
            'faq' => $faqs,
            'online' => $online,
            'specializations' => $request->specializations()->get()
        ]);
    }

    public function GetCalender()
    {
        ob_start('ob_gzhandler');
        $user = User::where('approve', 1)
            ->whereIn('doctor_status', ['active', 'imported'])
            ->whereIn('status', ['active', 'imported'])
            ->where('username', $this->request->username)
            ->first();

        $full_times = [];

        if ($user) {

            $date = change_number(jdate('Y-m-d'));
            $dateTime = change_number(jdate('H'));

            $where_array = array();
            $filter_hospital = trim($this->request->get('hospital'));
            if ($filter_hospital) {
                $where_array[] = array('partner_id', $filter_hospital);
            }

            $request = DoctorCalender::where($where_array)
                ->where('user_id', $user->id)->where('fa_data', '>=', $date);

            if ($this->request->has('visit_type')){
                $request = $request->where('doctor_calenders.type',VisitTypeEnum::type($this->request->get('visit_type')))
                ->where('capacity', '>=' , 'reservation');

            }else{
                $request = $request->where('doctor_calenders.type','!=' , 5);
            }

            $request = $request->orderBy('data', 'ASC')
                ->select('doctor_calenders.id', 'doctor_calenders.has_prescription','fa_data', 'data', 'time', 'capacity', 'reservation',
                    'price', 'original_price', 'dollar_price', 'original_dollar_price', 'partners.short_name as short_name',
                    'doctor_calenders.type as type')
                ->leftJoin('partners', 'partners.id', 'doctor_calenders.partner_id')
                ->orderBy('time', 'asc')->get();

            $request_new = [];
            foreach ($request as $v) {
                if ($v['fa_data'] == $date) {
                    if ($v['time'] >= $dateTime) {
                        $request_new[] = $v;
                    }
                } else {
                    $request_new[] = $v;
                }
            }

            for ($i = 0; $i < 90; $i++) {

                $dateTime = Carbon::now()->addDays($i);
                $dateTime = $dateTime->year . '/' . $dateTime->month . '/' . $dateTime->day;
                $fa_date = jdate('Y-m-d', strtotime($dateTime));
                $fa_date_lb = jdate('l - d F', strtotime($dateTime));
                $fa_date_index = jdate('w', strtotime($dateTime));
                $fa_date_day = jdate('d', strtotime($dateTime));
                $fa_date_month = jdate('m', strtotime($dateTime));
                $fa_date_year = jdate('Y', strtotime($dateTime));
                $fa_date_full = jdate('Y/m/d', strtotime($dateTime));
                $weekDayVisit = [];
                $capacity = 0;
                foreach ($request_new as $v) {
                    if ($v['fa_data'] == $fa_date) {
                        if ($i == 0 &&  $v['type'] == 2 ) {
                            $v['display_top_label'] = 'مشاوره فوری';
                            $time = $v['time'];
                            $duration = 60 / $v['capacity'];
                            $reserved = (int)$v['reservation'] * $duration;
                            if ($reserved < 10)
                                $labeled_time = $time.':'.'0'.$reserved;
                            else
                                $labeled_time = $time.':'.$reserved;
                            $v['display_bottom_label'] = ' شروع تقریبی مشاوره '.$labeled_time;

                        }
                        elseif ($v['type'] == 2) {
                            $v['display_top_label'] = ' مشاوره فوری';
                            $v['display_bottom_label'] = 'مدت انتظار حداکثر ۱۵ دقیقه';
                        }
                        elseif ($v['type'] == 1) {
                            $v['display_top_label'] = ' مشاوره آنلاین(معمولی)';
                            $v['display_bottom_label'] = 'مدت انتظار ۱ تا ۳ ساعت';
                        }
                        elseif($v['type'] == 3) {
                            $v['display_top_label'] = 'مشاوره آفلاین';
                            $v['display_bottom_label'] = 'مدت انتظار ۲۴ تا ۴۸ ساعت';
                        }
                        elseif($v['type'] == 4) {
                            $v['display_top_label'] = 'تفسیر آزمایش (متنی)';
                            $v['display_bottom_label'] = 'مدت انتظار ۱ تا ۳ ساعت';
                        }
                        elseif($v['type'] == 5) {
                            $v['display_top_label'] = 'ویزیت حضوری';
                            $v['display_bottom_label'] = 'مدت انتظار در مطب ۱ تا ۳ ساعت';
                        }
                        elseif($v['type'] == 6) {
                            $v['display_top_label'] = 'مشاوره عمل جراحی';
                            $v['display_bottom_label'] = 'مدت انتظار ۱ تا ۳ ساعت';
                        }
                    }
                }

                foreach ($request_new as $v) {
                    if ($v['fa_data'] == $fa_date) {
                        $weekDayVisit[] = $v;
                        $capacity_int = $v['capacity'] - $v['reservation'];
                        $capacity = $capacity_int + $capacity;
                    }
                }

                if ($this->request->has('limit')) {
                    if ($capacity > 0) {

                        $full_times[] = [
                            'Day' => $fa_date_day,
                            'Month' => $fa_date_month,
                            'Year' => $fa_date_year,
                            'WeekDayIndex' => $fa_date_index,
                            'WeekDay' => $fa_date_lb,
                            'DateTimeFa' => $fa_date_full,
                            'DateTime' => $dateTime,
                            'Visits' => $weekDayVisit,
                            'CapacityCount' => $capacity,
                        ];
                    }

                } else {

                    $full_times[] = [
                        'Day' => $fa_date_day,
                        'Month' => $fa_date_month,
                        'Year' => $fa_date_year,
                        'WeekDayIndex' => $fa_date_index,
                        'WeekDay' => $fa_date_lb,
                        'DateTimeFa' => $fa_date_full,
                        'DateTime' => $dateTime,
                        'Visits' => $weekDayVisit,
                        'CapacityCount' => $capacity,
                    ];
                }
                /*if($capacity){
                }*/

            }

        }

        return success_template($full_times);

    }

    public function introduction_doctors()
    {


        $request = User::where('approve', 1)
            ->where('doctor_status', 'active')
            ->whereIn('status', ['active', 'imported'])
            ->select(
                'fullname',
                'username',
                'gender',
                'bio',
                'address',
                'picture',
                'job_title',
                'skill_json',
                'special_json'
            )
            ->inRandomOrder()
            ->limit(15)
            ->get();


        return success_template($request);


    }

    public function specialties_doctors()
    {

        $filter_name = trim($this->request->get('key'));

        $st_gp = '';
        foreach (specialties_array() as $key => $item) {
            if ($item == $filter_name) {
                $st_gp = $item;
            }
        }

        $onlineSearch = [];
        $onlineCalenderSearch = [];
        $online_user = DoctorCalender::where('fa_data', '>=', change_number(jdate('Y-m-d')))->select('user_id', 'time')->orderBy('time', 'desc')->get();

        if ($online_user) {
            foreach ($online_user as $item) {

                if (!in_array($item['user_id'], $onlineCalenderSearch)) {
                    $onlineCalenderSearch[] = $item['user_id'];
                }

            }
        }


        $online_user = DoctorCalender::where('fa_data', '=', change_number(jdate('Y-m-d')))->where('time', '<=', '24')->where('time', '>=', date('H'))->select('user_id', 'time')->orderBy('time', 'desc')->get();


        if ($online_user) {
            foreach ($online_user as $item) {

                if (!in_array($item['user_id'], $onlineSearch)) {
                    if ($item['time'] == date('H')) {
                        $onlineSearch[] = $item['user_id'];
                    }
                }

            }
        }

        $SortSearch = [];
        if ($onlineCalenderSearch) {
            foreach ($onlineCalenderSearch as $item) {

                if (!in_array($item, $onlineSearch)) {
                    $SortSearch[] = $item;
                }

            }
        }
        if ($onlineSearch) {
            foreach ($onlineSearch as $item) {

                if (!in_array($item, $SortSearch)) {
                    $SortSearch[] = $item;
                }

            }
        }


        $ids = array(7, 321);
        $ids_ordered = implode(',', $SortSearch);

        $request = User::where('users.approve', 1)
            ->where('doctor_status', 'active')
            ->whereIn('users.status', ['active', 'imported'])
            ->where('sp_gp', 'LIKE', '%' . $st_gp . '%')
            ->select(
                'users.id',
                'users.fullname',
                'users.username',
                'users.gender',
                'users.doctor_nickname',
                'users.bio',
                'users.picture',
                'users.job_title',
                'users.skill_json',
                'users.special_json'
            )
            ->orderByRaw(DB::raw("FIELD(id, $ids_ordered) DESC"))
            ->orderBy('fullname', 'ASC')
            ->get();

//->orderByRaw("doctor_calenders.data = ':data' DESC", [ 'data' => date('Y-m-d') ])

        $users = [];
        if ($request) {
            foreach ($request as $item) {
                $users[] = $item['id'];
            }
        }

        $online = [];
        $onlineCalender = [];
        $firstVisit = [];
        $firstVisitTIme = [];
        $firstVisitDate = [];

        $online_user = DoctorCalender::whereIn('user_id', $users)->where('fa_data', '>=', change_number(jdate('Y-m-d')))->select('user_id', 'time', 'fa_data')->orderBy('time', 'desc')->get();

        if ($online_user) {
            foreach ($online_user as $item) {

                if (!in_array($item['user_id'], $onlineCalender)) {
                    $onlineCalender[] = $item['user_id'];
                    $firstVisit[$item['user_id']] = $item['fa_data'] . ' ساعت ' . $item['time'];
                    $firstVisitDate[$item['user_id']] = $item['fa_data'];
                    $firstVisitTIme[$item['user_id']] = (int)$item['time'];
                } else {
                    if (isset($firstVisitTIme[$item['user_id']]) && $firstVisitTIme[$item['user_id']] > ((int)$item['time']) && $firstVisitDate[$item['user_id']] == $item['fa_data']) {
                        if (((int)$item['time']) > ((int)date('H', strtotime('-1 hours')))) {
                            $firstVisit[$item['user_id']] = $item['fa_data'] . ' ساعت ' . $item['time'];
                            $firstVisitTIme[$item['user_id']] = (int)$item['time'];
                        }

                    }
                }

            }
        }

        $online_user = DoctorCalender::whereIn('user_id', $users)->where('fa_data', '=', change_number(jdate('Y-m-d')))->where('time', '<=', '24')->where('time', '>=', date('H'))->select('user_id', 'time')->orderBy('time', 'desc')->get();


        if ($online_user) {
            foreach ($online_user as $item) {

                if (!in_array($item['user_id'], $online)) {
                    if ($item['time'] == date('H')) {
                        $online[] = $item['user_id'];
                    }
                }

            }
        }


        return success_template(['search' => $request, 'online' => $online, 'onlineCalender' => $onlineCalender, 'firstVisit' => $firstVisit]);


    }

    public function quickSearch_old()
    {
        $keywords[] = $this->request->input('search');
        $remove_lists = [
            'آقای' => '',
            'خانم' => '',
            'خانوم' => '',
            'پزشک ' => '',
            'دکتر' => '',
            'شدید' => '',
            'حالت' => '',
            'چرا' => '',
            'دکتر های' => '',
            'دکتر ای' => '',
            'دکتر ها' => '',
            'دکتر ای' => '',
            'دکتر' => '',
            'مشاوره آنلاین' => '',
            'متخصص' => '',
            'مشاوره پزشکی آنلاین' => '',
            'مشاوره پزشکی' => '',
            'پزشکی ' => '',
            'پزشکی های' => '',
            'پزشکی ها' => '',
            'پزشکی ای' => '',
            'متخصص های' => '',
            'متخصص ها' => '',
            'متخصص ای' => '',
            'دکتران' => '',
            'دکترها' => '',
            'جون' => '',
            ' جون' => ''
        ];

        $doctor = [];

        $replacing = [
            'یی' => 'ئی',
            // 'ی' => 'ئ',
            'ک' => 'ك',
            'ي' => 'ی',
            'آ' => 'ا',
        ];

        foreach ($remove_lists as $k => $item) {
            $keywords = str_replace($k, $item, $keywords);
        }
        $keywords[0] = trim($keywords[0]);

        foreach ($replacing as $key => $replace) {
            if (strpos($keywords[0], $key))
                array_push($keywords, str_replace($key, $replace, $keywords[0]));
            elseif (strpos($keywords[0], $replace))
                array_push($keywords, str_replace($replace, $key, $keywords[0]));
        }
        array_push($keywords, ' ' . $keywords[0] . ' ');

//        if (mb_substr($keywords[0], 0, 1) == "ا") {
//            $key = "آ" . mb_substr($keywords[0], 1);
//            array_unshift($keywords,$key);
//        }

        $data = User::select('users.id', DB::raw('min(DATE(calenders.data)) as nearest_time'), 'time',
//            DB::raw('min(time)'),
            'users.name', 'users.family', 'users.fullname', 'users.job_title', 'users.username', 'users.doctor_nickname',
            'badge.plan as badges',
            'users.picture', 'sp.name as special_name', 'users.gender', 'sp_gp', 'users.bio', 'tags.items', 't_user.items')
            ->where('approve', 1)
            // ->whereHas('calenders')
            ->where('doctor_status', 'active')
            ->whereIn('status', ['imported', 'active'])
            ->whereNotIn('users.id', TestAccount())
//            ->where('users.gender', 'LIKE', (($this->request->has('gender')) ?
//                ($this->request->input('gender') == 'زن' ? 1 : 0) : '%'))
            ->leftJoin('tags as t_user', function ($leftJoin) {
                $leftJoin->on('users.id', 't_user.searchable_id')
                    ->where('t_user.searchable_type', 'App\User');
            })->leftJoin(DB::raw('(select * from doctor_calenders where DATE(data) >= "' . Carbon::now()->format('Y-m-d') . '" order by DATE(data) ASC) calenders'), function ($leftJoin) {
                $leftJoin->on('calenders.user_id', 'users.id')
                    ->whereDate('calenders.data', '>=', DB::raw(
                        'IF( EXISTS(
                                SELECT id
                                FROM doctor_calenders
                                 WHERE user_id = users.id and DATE(data) = "' . Carbon::now()->format('Y-m-d') . '" and time > ' . Carbon::now()->hour . ' limit 1) ,
                                 "' . Carbon::now()->format('Y-m-d') . '","' . Carbon::now()->addDays(1)->format('Y-m-d') . '")'
                    ))
                    ->where('calenders.time', '>', DB::raw(
                        'IF( EXISTS(
                                SELECT id
                                FROM doctor_calenders
                                 WHERE user_id = users.id and DATE(data) = "' . Carbon::now()->format('Y-m-d') . '" and time > ' . Carbon::now()->hour . ' limit 1) , ' . Carbon::now()->hour . ',-1)'
                    ))
                    ->where('capacity', '>', DB::raw('reservation'))
                    ->orderBy('data', 'ASC')
                    ->orderBy('time', 'ASC');
            })
            ->join('user_specializations as us', 'users.id', 'us.user_id')
            ->join('specializations as sp', function ($join) {
                $join->on('sp.id', 'us.specialization_id')
                    ->Where('sp.name', 'LIKE', request()->has('proficiency') && request()->input('proficiency')
                        ? request()->input('proficiency') : '%');
            })
            ->leftJoin('tags', function ($join) {
                $join->on('sp.id', 'tags.searchable_id')
                    ->Where('tags.searchable_type', 'App\Model\Doctor\Specialization');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));

            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                    ->where('badge.priority', '>' ,500);
            })
//            ->leftJoin('states', function ($join) {
//                $join->on('state_id', 'states.id')
//                    ->where('states.state', 'LIKE', request()->has('state') && request()->input('state')
//                        ? request()->input('state') : '%');
//            })
            ->groupBy('users.id')
            ->orderBy('badge.priority' , 'DESC')
            ->orderByRaw('time IS NULL')
            ->orderBy('nearest_time', 'ASC')
            ->orderBy('time', 'asc');
        if ($this->request->has('search') && \request()->input('search')) {
            $data = $data->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('fullname', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.name', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.family', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.bio', "LIKE", "%" . $keyword . "%")
//                        ->orWhere('users.sp_gp', "LIKE", "%" . $keyword . "%")
                        ->orWhere('sp.name', "LIKE", "%" . $keyword . "%")
                        ->orWhere('tags.items', 'LIKE', '%,' . $keyword . ',%')
                        ->orWhere('tags.items', 'LIKE', '%,' . $keyword)
                        ->orWhere('t_user.items', 'LIKE', '%,' . $keyword . '%')
                        
                        ->orWhere('t_user.items', 'LIKE', '%,' . $keyword . ',%');
//                        ->orWhere('skill_json', 'LIKE', '%' . $keyword . '%')
//                        ->orWhere('special_json', 'LIKE', '%' . $keyword . '%');
                }
            });
//            if ($this->request->has('state') && $this->request->input('state'))
//                $data = $data->orderByRaw('state IS NULL')->orderBy('state', 'ASC');
            $doctor = $data->limit(4)->get();
            $random = $data->inRandomOrder()->limit(1)->get();
        }
//        for ($i = 0; $i < $doctor->count(); $i++) {
//            $doctor[$i]['diff'] = Carbon::parse($doctor[$i]->nearest_time)
//                ->diffInDays(Carbon::now()->format('Y-m-d'));
//        }
        $specializations = [];
        if ($this->request->has('search') && \request()->input('search')) {
            $specializations = Specialization::where('language_id',
                request()->has('lang') && request()->has('lang') ?
                    LanguageEnum::getIdBySlug(request()->input('lang'))
                    : LanguageEnum::Farsi)->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('name', "LIKE", "%" . $keyword . "%")

                        ->orWhere('description', "LIKE", "%" . $keyword . "%");
                }
            })
                ->orWhereHas('SearchArea', function ($query) use ($keywords) {
                    $query->where(function ($query2) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            $query2->orWhere('items', "LIKE", '%,' . $keyword . ',%');
                            $query2->orWhere('items', "LIKE", '%,' . $keyword);
                        }
                    });
                });
            $specializations = $specializations->orderBy('name')->limit(4)->get();
        }

        return success_template(['doctor' => $doctor,
            'specializations' => $specializations,
            'random' => $random]);
    }

    public function quickSearch()
    {
        $keywords[] = $this->request->input('search');
        $remove_lists = [
            'آقای' => '',
            'خانم' => '',
            'خانوم' => '',
            'پزشک ' => '',
            'دکتر' => '',
            'شدید' => '',
            'حالت' => '',
            'چرا' => '',
            'دکتر های' => '',
            'دکتر ای' => '',
            'دکتر ها' => '',
            'دکتر ای' => '',
            'دکتر' => '',
            'مشاوره آنلاین' => '',
            'متخصص' => '',
            'مشاوره پزشکی آنلاین' => '',
            'مشاوره پزشکی' => '',
            'پزشکی ' => '',
            'پزشکی های' => '',
            'پزشکی ها' => '',
            'پزشکی ای' => '',
            'متخصص های' => '',
            'متخصص ها' => '',
            'متخصص ای' => '',
            'دکتران' => '',
            'دکترها' => '',
            'جون' => '',
            ' جون' => ''
        ];

        $doctor = [];

        $replacing = [
            'یی' => 'ئی',
            // 'ی' => 'ئ',
            'ک' => 'ك',
            'ي' => 'ی',
            'آ' => 'ا',
        ];

        foreach ($remove_lists as $k => $item) {
            $keywords = str_replace($k, $item, $keywords);
        }
        $keywords[0] = trim($keywords[0]);

        foreach ($replacing as $key => $replace) {
            if (strpos($keywords[0], $key))
                array_push($keywords, str_replace($key, $replace, $keywords[0]));
            elseif (strpos($keywords[0], $replace))
                array_push($keywords, str_replace($replace, $key, $keywords[0]));
        }
        array_push($keywords, ' ' . $keywords[0] . ' ');

//        if (mb_substr($keywords[0], 0, 1) == "ا") {
//            $key = "آ" . mb_substr($keywords[0], 1);
//            array_unshift($keywords,$key);
//        }

        $data = User::select('users.id',
            'users.name', 'users.family', 'users.fullname', 'users.job_title', 'users.username', 'users.doctor_nickname',
             'sp_gp', 'users.bio')
            ->where('approve', 1)
            // ->whereHas('calenders')
            ->where('doctor_status', 'active')
            ->whereIn('status', ['imported', 'active'])
            ->whereNotIn('users.id', TestAccount())
            ->join('user_specializations as us', 'users.id', 'us.user_id')
           /* ->join('specializations as sp', function ($join) {
                $join->on('sp.id', 'us.specialization_id')
                    ->Where('sp.name', 'LIKE', request()->has('proficiency') && request()->input('proficiency')
                        ? request()->input('proficiency') : '%');
            })*/
            ->groupBy('users.id');
        if ($this->request->has('search') && \request()->input('search')) {
            $data = $data->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('fullname', "LIKE", "%" . $keyword . "%")
                        //->orWhere('users.name', "LIKE", "%" . $keyword . "%")
                        //->orWhere('users.family', "LIKE", "%" . $keyword . "%")
                        //->orWhere('users.bio', "LIKE", "%" . $keyword . "%")
                        ->orWhere('users.sp_gp', "LIKE", "%" . $keyword . "%");
                }
            });
            $doctor = $data->limit(4)->get();
            $random = $data->inRandomOrder()->limit(1)->get();
        }

        $specializations = [];
        if ($this->request->has('search') && \request()->input('search')) {
            $specializations = Specialization::where('language_id',
                request()->has('lang') && request()->has('lang') ?
                    LanguageEnum::getIdBySlug(request()->input('lang'))
                    : LanguageEnum::Farsi)->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('name', "LIKE", "%" . $keyword . "%")
                        ->orWhere('description', "LIKE", "%" . $keyword . "%");
                }
            })
                ->orWhereHas('SearchArea', function ($query) use ($keywords) {
                    $query->where(function ($query2) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            $query2->orWhere('items', "LIKE", '%,' . $keyword . ',%');
                            $query2->orWhere('items', "LIKE", '%,' . $keyword);
                        }
                    });
                });
            $specializations = $specializations->orderBy('name')->limit(4)->get();
        }

        return success_template(['doctor' => $doctor,
            'specializations' => $specializations,
            'random' => $random]);
    }
}
