<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Enums\LanguageEnum;
use App\Model\Doctor\DoctorDetail;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\StarRate;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function top(Request $request)
    {


        $request->validate([
            'take' => 'integer'
        ]);
		
        $data = EventReserves::whereDate('reserve_time', '>=', Carbon::now()->startOfWeek()->subDays(2)->format('Y-m-d'))
            ->whereDate('reserve_time', '<=', Carbon::now()->format('Y-m-d'))
            ->whereNotIn('users.id', TestAccount())
            ->whereNotIn('users.id', [321, 11572, 3334])
            ->join('users', 'event_reserves.doctor_id', 'users.id')
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                ->where('badge.priority',2000);
            })
            ->join('user_specializations as us', 'us.user_id', 'users.id')
            ->join('specializations as sp', function ($join) {
                $join->on('sp.id', 'us.specialization_id');
                if (\request()->has('lang') && \request()->input('lang'))
                    $join->where('sp.language_id', LanguageEnum::getIdBySlug(\request()->input('lang')));
            })
            ->orderBy('badge.priority' , 'DESC')
            ->orderBy('counts', 'DESC')
            ->groupBy('users.id');
        if ($request->has('lang') && \request()->input('lang'))
            $data = $data->leftJoin('user_dictionaries', function ($leftJoin) {
                $leftJoin->on('users.id', 'user_dictionaries.user_id')
                    ->where('user_dictionaries.language_id', LanguageEnum::getIdBySlug(request()->input('lang')));
            })->select(DB::raw('COUNT(event_reserves.id) as counts'),
                DB::raw('coalesce(user_dictionaries.name,users.name) as name'),
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                DB::raw('coalesce(user_dictionaries.job_title,users.job_title) as job_title'),
                DB::raw('coalesce(user_dictionaries.prefix,users.doctor_nickname) as doctor_nickname'),
                'users.username',
                'badge.plan as badges',
                'users.family', 'users.picture', 'users.online_status', 'sp.name as sp_name',
                'user_dictionaries.fullname as dic_fullname');
        else
            $data = $data->select(DB::raw('count(event_reserves.id) as counts'),
                'badge.plan as badges',
                'users.name', 'users.fullname', 'users.job_title', 'users.username', 'users.doctor_nickname',
                'users.family', 'users.picture', 'users.online_status', 'sp.name as sp_name');

        if (\request()->has('take'))
			
            $data = $data->limit(\request()->has('take') ? \request()->input('take') : '')->get();
        else
            $data = $data->get();
        if ($data)
            if (!$data->isEmpty())
                return success_template($data);
            else
                return success_template(['message' => 'اطلاعاتی با این مشخصات موجود نیست']);
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }

    public function setStar(Request $request)
    {
        /** @var \App\User $user */
        $doctor = \App\User::find($request->doctor_id);
        $request->validate([
            'user_id' => 'required',
            'doctor_id' => 'required',
        ]);
        if ($doctor) {
            $stars = $doctor->starRates()->where('user_id', $request->user_id)->first();
            if ($stars) {
                return error_template('شما یکبار مجاز به رای دهی هستید');
            } else {
                $quality = $request->quality ? $request->quality : 5;
                $cost = $request->cost ? $request->cost : 5;
                $behaviour = $request->behaviour ? $request->behaviour : 5;
                $rate = StarRate::create([
                    'votable_id' => $request->doctor_id,
                    'votable_type' => 'App\User',
                    'user_id' => $request->user_id,
                    'quality' => $quality,
                    'cost' => $cost,
                    'behaviour' => $behaviour,
                    'overall' => (($quality + $cost + $behaviour) / 3),
                    'comment' => $request->comment ? $request->comment : 'نظری ثبت نشده است',
                    'flag' => $request->comment ? 0 : 5
                ]);
                return success_template($rate);
            }
        }
        return error_template('Wrong User ID !');
    }

    public function getStar($id)
    {
        $overall = \App\User::find($id)->starRates()
            ->whereIn('flag', [1, 5, 6])
            ->select(DB::raw('AVG(overall) as avg_overall'),
                DB::raw('AVG(quality) as avg_quality'),
                DB::raw('AVG(cost) as avg_cost'),
                DB::raw('AVG(behaviour) as avg_behaviour'),
                DB::raw('COUNT(id) as number_of_votes'))->first();
        $stars = \App\User::find($id)->starRates()
            ->whereIn('flag', [1])
            ->join('users', 'user_id', '=', 'users.id')
            ->select('users.fullname', 'users.picture', 'overall', 'quality', 'behaviour', 'cost', 'comment', 'reply')->get();
        if ($stars && $overall) {
            $data = [
                'overall' => $overall,
                'stars' => $stars
            ];
            return success_template($data);
        }
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }

    public function detail($id)
    {
        $details = DoctorDetail::where('user_id', $id)->first();
        if ($details) {
            $faqs = $details->user->FAQs()->get();
            $faqs = $faqs->transform(function ($faq) {
                unset($faq->questionable_type);
                return $faq;
            });
        } else
            $faqs = null;
        return success_template([
            'details' => $details,
            'faq' => $faqs
        ]);
    }

    public function specializationsTop(Request $request)
    {
        $request->validate([
            'take' => 'integer'
        ]);
        $data = EventReserves::select(DB::raw('count(event_reserves.id) as counts'), 'users.name', 'users.fullname', 'users.job_title', 'users.username', 'users.doctor_nickname',
            'users.family', 'users.picture', 'users.online_status', 'sp.name as sp_name')
            ->whereDate('reserve_time', '>=', Carbon::now()->subDays(30)->format('Y-m-d'))
//            ->whereDate('reserve_time', '<=', Carbon::now()->format('Y-m-d'))
            ->whereNotIn('users.id', TestAccount())
            ->whereNotIn('users.id', [321, 11572, 3334])
            ->whereIn('users.status', ['active', 'imported'])
            ->where('doctor_status', 'active')
            ->join('users', 'event_reserves.doctor_id', 'users.id')
            ->join('user_specializations as us', 'us.user_id', 'users.id')
            ->join('specializations as sp', 'sp.id', 'us.specialization_id')
            ->where('sp.slug', $request->input('slug'))
            ->orderBy('counts', 'DESC')
            ->groupBy('users.id');
        if (\request()->has('take'))
            $data = $data->limit(\request()->has('take') ? \request()->input('take') : '')->get();
        else
            $data = $data->get();
        if ($data)
            if (!$data->isEmpty())
                return success_template($data);
            else
                return success_template(['message' => 'اطلاعاتی با این مشخصات موجود نیست']);
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }

    public function suggestDoctor(Request $request)
    {

        $calendar = \App\User::select('users.username', 'badge.plan as badges', 'users.id', 'doctor_calenders.time')
            ->whereIn('users.status', ['active', 'imported'])
            ->where('doctor_status', 'active')
            ->whereHas('specializations', function ($query) {
                $query->where('slug', \request()->input('slug'));
            })->join('doctor_calenders',function ($join){
                $join->on('users.id', 'doctor_calenders.user_id')
                    ->where('doctor_calenders.type','!=',3);
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id');
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                ->where('badge.priority', 2000);
            })
            ->whereDate('data', Carbon::now()->format('Y-m-d'))
            ->where('capacity', '>', DB::raw('reservation'))
            ->where('time', '>=', Carbon::now()->hour)
            ->orderBy('badge.priority' , 'DESC')
            ->inRandomOrder()
            ->first();
        return success_template($calendar);
    }
}
