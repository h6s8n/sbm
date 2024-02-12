<?php


namespace App\Repositories\v1\Doctor\Specialization;


use App\Enums\LanguageEnum;
use App\Model\Doctor\Specialization;
use App\Model\Doctor\UserSpecialization;
use App\Model\Visit\DoctorCalender;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;

class SpecializationRepository implements SpecializationInterface
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Tehran');
    }

    public function all($option=null)
    {
        if ($option) {
            if ($option['filter']){
                $sp=Specialization::whereRaw($option['filter'])->where('language_id',
                    request()->has('lang') && request()->input('lang') ?
                        LanguageEnum::getIdBySlug(request()->input('lang'))
                        : LanguageEnum::Farsi);
            }
            return $sp;
        }
        return Specialization::all();
    }

    public function OrderBy($collection,$value,$type){
        return $collection->orderBy($value,$type);
    }

    public function paginate($collection,$paginate)
    {
        return $collection->paginate($paginate);
    }

    public function store($data)
    {
        try {
            return Specialization::create($data);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function update(Specialization $model, $data)
    {
        /* @var Specialization $sp*/
        try {
            $sp = $model->update($data);
            $model->SearchArea()->delete();
            if ($data['items'])
            $model->SearchArea()->create(['items'=>$data['items']]);
            return ['status' => true,'sp'=>$model];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                $exception->getMessage()
            ];
        }
    }

    public function assignDoctor($data)
    {
        try {
            $user = User::find($data['user_id']);
            $user->specializations()->sync($data['specializations_id']);
            return [
                'status' => true
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function withCalender()
    {
//        return User::select(DB::raw('min(calenders.data) as nearest_time'),DB::raw('min(time) as time'),
//        'users.name','users.family','sp.name')
//        ->where('approve',1)
//            ->leftJoin('doctor_calenders as calenders','users.id','user_id')
//            ->whereDate('calenders.data','>=',Carbon::now()->format('Y-m-d'))
//            ->where('calenders.time','>',Carbon::now()->hour)
//            ->join('user_specializations as us','users.id','us.user_id')
//            ->join('specializations as sp','sp.id','us.specialization_id')
//            ->where('sp.name','LIKE',request()->has('sp_name') && request()->input('sp_name') ? request()->input('sp_name') : '%')
//            ->groupBy('users.id')
//            ->orderBy('nearest_time','ASC')
//            ->orderBy('time','ASC')
//            ->get();

        return User::select(DB::raw('min(calenders.data) as nearest_time'), DB::raw('min(time) as time'),
            'users.name', 'users.family', 'users.fullname', 'users.picture', 'sp.name as special_name')
            ->where('approve', 1)
            ->leftJoin('doctor_calenders as calenders', function ($leftJoin) {
                $leftJoin->on('user_id', 'users.id')
                    ->whereDate('calenders.data', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('calenders.time', '>', Carbon::now()->hour);
            })
            ->join('user_specializations as us', 'users.id', 'us.user_id')
            ->join('specializations as sp',function ($join){
                $join->on('sp.id', 'us.specialization_id')
                    ->where('language_id',
                    request()->has('lang') && request()->has('lang') ?
                        LanguageEnum::getIdBySlug(request()->input('lang'))
                        : LanguageEnum::Farsi);
            })
            ->where('sp.name', 'LIKE', request()->has('sp_name') &&
            request()->input('sp_name') ? '%' . request()->input('sp_name') . '%' : '%')
            ->groupBy('users.id')
            ->orderByRaw('time IS NULL')
            ->orderBy('nearest_time', 'ASC')
            ->orderBy('time', 'asc')
            ->paginate(16);
    }

    public function withUsers()
    {
         $sp = Specialization::where('slug', 'LIKE', ' %.' . request()->has('slug') ?
             request()->input('slug') : '' . '%')
             ->where('language_id',
             request()->has('lang') && request()->input('lang') ?
                 LanguageEnum::getIdBySlug(request()->input('lang')) : LanguageEnum::Farsi);
         if (request()->has('user-limit') && request()->input('user-limit') > 0){
             $sp = $sp->whereHas('users')
                 ->with(['users(badges)' => function ($query) {
                     $query->select('fullname', 'doctor_nickname', 'picture', 'id');
                 }])->limit(request()->has('special-limit') ?
                     request()->input('special-limit') : null);
         }
         $sp = $sp->orderBy('priority','ASC')->get();

         if (request()->has('user-limit') && request()->input('user-limit') > 0)
             $sp = $sp->map(function ($specializations){
                 $specializations->setRelation('users',
                     $specializations->users->take(request()->has('user-limit') ?
                         (int)request()->input('user-limit') : null));
                 return $specializations;
             });

         for ($i=0, $iMax = count($sp); $i< $iMax; $i++){
             $sp[$i]['num_of_doctors'] = $sp[$i]->Users()->count();
             $sp[$i]['link'] = 'https://sbm24.com/specialties/'.$sp[$i]['slug'];
         }
         return $sp;
    }

    public function delete(Specialization $sp)
    {
        try {
            $sp->delete();
            return [
                'status' => true
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false
            ];
        }
    }

    public function search($filter)
    {
        $sp = Specialization::all();
            $sp = $sp->where(key($filter),'LIKE','%'.$filter[key($filter)].'%');
        return $sp;
    }
}
