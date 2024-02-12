<?php


namespace App\Repositories\v2\Profile\Doctor;


use App\Enums\LanguageEnum;
use App\Traites\RepositoryResponseTrait;
use App\User;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Support\Facades\DB;

class ProfileRepository implements ProfileInterface
{
    use RepositoryResponseTrait;

    public function get($value, $type, $with = null)
    {
        /* @var User $user */
        try {
            $user = User::where('approve', 1)
                ->where('doctor_status', 'active')
                ->whereIn('status', ['active', 'imported'])
//                ->where(function ($query) use ($type,$value){
//                    $query->where($type, $value)
//                        ->orWhere('token', $value);
//                })
                ->where(function ($query) use ($type, $value) {
                    $query->where($type, $value)
                        ->orWhere('en_url', $value);
                })
                ->with($with)
                ->leftJoin('user_badges',function ($leftJoin){
                    $leftJoin->on('user_badges.user_id','users.id')
                        ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
                })
                ->leftJoin('cities', function ($leftJoin){
                    $leftJoin->on('cities.id','users.city_id');
                })
                ->leftJoin('badges as badge' , function ($join){
                    $join->on('user_badges.badge_id','badge.id');
                })
                ->leftJoin('star_rates as stars', function ($leftJoin) {
                    $leftJoin->on('stars.votable_id', 'users.id')
                        ->where('votable_type', 'App\User')
                        ->whereIn('flag', [1, 5, 6]);
                });
            if (\request()->has('lang') && \request()->input('lang'))
                $user = $user->leftJoin('user_dictionaries', function ($leftJoin) {
                    $leftJoin->on('users.id', 'user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',
                            LanguageEnum::getIdBySlug(request()->input('lang')));
                })->select(
                    'users.id',
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'username',
                    'badge.plan as badges',
                    'gender',
                    DB::raw('coalesce(user_dictionaries.bio,users.bio) as bio'),
                    'special_point',
                    'in_person_special_point',
                    'address',
                    'cities.city',
                    'latitude',
                    'longitude',
                    'picture',
                    DB::raw('coalesce(user_dictionaries.job_title,users.job_title) as job_title'),
                    DB::raw('coalesce(user_dictionaries.prefix,users.doctor_nickname) as doctor_nickname'),
                    DB::raw("coalesce(concat('Code: ',specialcode),concat(code_title,':',specialcode)) as specialcode"),
                    'skill_json',
                    'special_json',
                    'visit_condition',
                    DB::raw('count(stars.id) as number_of_votes'),
                    DB::raw('AVG(overall) as avg_overall'),
                    DB::raw('AVG(quality) as avg_quality'),
                    DB::raw('AVG(cost) as avg_cost'),
                    DB::raw('AVG(behaviour) as avg_behaviour')
                )->first();
            else {
                $user = $user->select(
                    'users.id',
                    'fullname',
                    'username',
                    'badge.plan as badges',
                    'gender',
                    'bio',
                    'special_point',
                    'in_person_special_point',
                    DB::raw("concat(ifnull(cities.city,''),' ',address) as address"),
//                    'address',
                    'cities.city',
                    'latitude',
                    'longitude',
                    'picture',
                    'job_title',
                    'doctor_nickname',
                    DB::raw("concat(code_title,':',specialcode) as specialcode"),
//                    'specialcode',
                    'skill_json',
                    'special_json',
                    'visit_condition',
                    DB::raw('count(stars.id) as number_of_votes'),
                    DB::raw('AVG(overall) as avg_overall'),
                    DB::raw('AVG(quality) as avg_quality'),
                    DB::raw('AVG(cost) as avg_cost'),
                    DB::raw('AVG(behaviour) as avg_behaviour')
                )->first();
            }
            return $this->SuccessResponse($user);
        } catch (\Exception $exception) {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function update($data)
    {
        /* @var User $user */
        try {
            $user = User::find($data['user_id']);
            $user->fill($data)->save();
            return $this->SuccessResponse($user);
        } catch (\Exception $exception) {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }
}
