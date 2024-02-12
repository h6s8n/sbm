<?php


namespace App\Repositories\v2\Calendar;


use App\Enums\CurrencyEnum;
use App\Enums\LanguageEnum;
use App\Model\Visit\DoctorCalender;
use App\Traites\CurrencyChangeTrait;
use App\Traites\RepositoryResponseTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\inspect;

class CalendarRepository implements CalendarInterface
{
    use RepositoryResponseTrait,CurrencyChangeTrait;

    public function update($data)
    {
        /* @var DoctorCalender $calendar */

        try {
            if (count($data['ids']) == 1){
                $trim['price'] = $data['price'];
                $trim['original_price'] = $data['price'];
                $trim['type'] = $data['visit_type'];
                $trim['partner_id'] = $data['visit_type'] != 5 ? $data['partner_id'] : 0;
                $trim['office_id'] = $data['visit_type'] == 5 ? $data['office_id'] : null;
                $trim['consultation_type'] = $data['visit_type'] != 5 ? json_encode($data['consultation_type']) : null;
            }
            $trim['capacity'] = $data['capacity'];


            DoctorCalender::whereIn('id',$data['ids'] )
                ->where('reservation' , 0)
                ->update($trim);
            return $this->SuccessResponse(['message' => 'با موفقیت ویرایش شد']);
        } catch (\Exception $exception) {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function getOnlineDoctors()
    {
        $instances = DoctorCalender::where('type',2)
            ->where('capacity','>',DB::raw('reservation'))
//            ->where('partner_id',0)
            ->whereNotIn('doctor_calenders.user_id',TestAccount())
            ->whereDate('data',Carbon::now()->format('Y-m-d'));
        if (Carbon::now()->minute >= 45)
            $instances=$instances->where('time','=',Carbon::now()->hour+1);
        else
            $instances=$instances->where('time','=',Carbon::now()->hour);

        $instances=$instances->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                ->where('badge.priority',2000);
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });
        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'time',
                'data',
                'username',
                'badge.plan as badges',
                'specializations.name as sp_name')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'time',
                'fa_data',
                'username',
                'badge.plan as badges',
                'specializations.name as sp_name')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
            ->groupBy('doctor_calenders.user_id')->get();
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);

        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }

        return $instances;
    }

    public function getInterpretationDoctors()
    {
        $instances = DoctorCalender::where('type',4)
            ->where('capacity','>',DB::raw('reservation'))
//            ->where('partner_id',0)
            ->whereNotIn('doctor_calenders.user_id',TestAccount())
            ->where('doctor_calenders.user_id','!=',321)
            ->whereDate('data','=',Carbon::now()->format('Y-m-d'));
//        if (Carbon::now()->minute >= 45)
//            $instances=$instances->where('time','=',Carbon::now()->hour+1);
//        else
//            $instances=$instances->where('time','=',Carbon::now()->hour);

        $instances=$instances->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                ->where('badge.priority',2000);
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });
        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'users.picture',
                    'doctor_calenders.id as key',
                    'doctor_calenders.price',
                    'time',
                    'data',
                    'badge.plan as badges',
                    'username',
                    'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'time',
                'fa_data',
                'badge.plan as badges',
                'username',
                'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);

        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }

        return $instances;
    }

    public function getInPersonDoctors()
    {
        $instances = DoctorCalender::where('type',5)
            ->where('capacity','>',DB::raw('reservation'))
//            ->where('partner_id',0)
            ->whereNotIn('doctor_calenders.user_id',TestAccount())
            ->whereDate('data','>=',Carbon::now()->format('Y-m-d'))
            ->whereDate('data','<=',Carbon::now()->addMonths(3)->format('Y-m-d'));
//        if (Carbon::now()->minute >= 45)
//            $instances=$instances->where('time','=',Carbon::now()->hour+1);
//        else
//            $instances=$instances->where('time','=',Carbon::now()->hour);

        $instances=$instances
//            ->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                    ->where('badge.priority',2000);
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });
        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'users.picture',
                    'doctor_calenders.id as key',
                    'doctor_calenders.price',
                    'time',
                    'data',
                    'badge.plan as badges',
                    'username',
                    'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'time',
                'fa_data',
                'badge.plan as badges',
                'username',
                'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);

        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }

        return $instances;
    }

    public function getSurgeryDoctors()
    {
        DB::connection()->enableQueryLog();
        $instances = DoctorCalender::where('type',6)
            ->where('capacity','>',DB::raw('reservation'))
//            ->whereNotIn('doctor_calenders.user_id',TestAccount())
            ->whereDate('data','>=',Carbon::now()->format('Y-m-d'))
            ->whereDate('data','<=',Carbon::now()->addMonths(3)->format('Y-m-d'));


        $instances=$instances
//            ->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                    ->where('badge.priority',2000);
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });

        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'users.picture',
                    'doctor_calenders.id as key',
                    'doctor_calenders.price',
                    'time',
                    'data',
                    'badge.plan as badges',
                    'username',
                    'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'time',
                'fa_data',
                'badge.plan as badges',
                'username',
                'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();

        $queries = DB::getQueryLog();

//        dd($queries);
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);


        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }


        return $instances;
    }


    public function getPrescriptionsDoctors()
    {
        $instances = DoctorCalender::where('has_prescription',1)
            ->where('capacity','>',DB::raw('reservation'))
//            ->where('partner_id',0)
            ->whereNotIn('doctor_calenders.user_id',TestAccount())
            ->where('doctor_calenders.user_id','!=',321)
            ->whereDate('data','=',Carbon::now()->format('Y-m-d'));
//        if (Carbon::now()->minute >= 45)
//            $instances=$instances->where('time','=',Carbon::now()->hour+1);
//        else
//            $instances=$instances->where('time','=',Carbon::now()->hour);

        $instances=$instances->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->leftJoin('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->leftJoin('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id')
                ->where('badge.priority',2000);
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });
        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'users.picture',
                    'doctor_calenders.id as key',
                    'doctor_calenders.price',
                    'doctor_calenders.type',
                    'time',
                    'data',
                    'username',
                    'badge.plan as badges',
                    'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'doctor_calenders.type',
                'time',
                'fa_data',
                'username',
                'badge.plan as badges',
                'specializations.name as sp_name')
                ->where('users.status' , 'active')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);

        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }

        return $instances;

    }

    public function getSponsorDoctors()
    {
        $instances = DoctorCalender::
//            where('has_prescription',1)
//            ->where('capacity','>',DB::raw('reservation'))
//            ->where('partner_id',0)
            whereNotIn('doctor_calenders.user_id',TestAccount())
//            ->where('doctor_calenders.user_id','!=',321)
//            ->whereDate('data','=',Carbon::now()->format('Y-m-d'))
        ;
//        if (Carbon::now()->minute >= 45)
//            $instances=$instances->where('time','=',Carbon::now()->hour+1);
//        else
//            $instances=$instances->where('time','=',Carbon::now()->hour);

        $instances=$instances->where('time','>=',Carbon::now()->hour)
            ->join('users','doctor_calenders.user_id','users.id')
            ->join('user_specializations',function ($query){
                $query->on('users.id','user_specializations.user_id');
            })
            ->join('user_badges',function ($leftJoin){
                $leftJoin->on('user_badges.user_id','users.id')
                    ->whereDate('user_badges.expiration_time','>=' , Carbon::now()->format('Y-m-d'));
            })
            ->join('badges as badge' , function ($join){
                $join->on('user_badges.badge_id','badge.id');
            })
            ->join('specializations',function ($query){
                $query->on('specializations.id','user_specializations.specialization_id');
                if (request()->has('sp') && request()->input('sp'))
                    $query->where('specializations.slug','LIKE','%'.request()->input('sp').'%');
                if (request()->has('lang') && request()->input('lang'))
                    $query->where('specializations.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
            });
        if (request()->has('lang') && request()->input('lang')){
            $instances = $instances
                ->leftJoin('user_dictionaries',function ($join){
                    $join->on('users.id','user_dictionaries.user_id')
                        ->where('user_dictionaries.language_id',LanguageEnum::getIdBySlug(request()->input('lang')));
                })
                ->select(
                    DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                    'users.picture',
                    'users.id',
                    'doctor_calenders.id as key',
                    'doctor_calenders.price',
                    'doctor_calenders.type',
                    'time',
                    'data',
                    'username',
                    'badge.plan as badges',
                    'specializations.name as sp_name')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
            foreach ($instances as $instance){
                $instance['price'] =  $this->ConvertRialTo(CurrencyEnum::EUR,$instance['price']);
            }
        }else
            $instances=$instances->select(
                'users.fullname',
                'users.picture',
                'doctor_calenders.id as key',
                'doctor_calenders.price',
                'doctor_calenders.type',
                'time',
                'fa_data',
                'username',
                'badge.plan as badges',
                'specializations.name as sp_name')
                ->orderBy('badge.priority' , 'DESC')
                ->orderBy(DB::raw('DATE(data)','DESC'))
                ->groupBy('doctor_calenders.user_id')
                ->get();
        for ($i=0, $iMax = count($instances); $i< $iMax; $i++)
            $instances[$i]['price'] = number_format($instances[$i]['price']);

        if (request()->has('paginate') && request()->input('paginate')) {
            $page = request()->has('page') && request()->input('page') ? request()->input('page') : 1;
            $instances = $instances->paginate(12,null,$page);
        }

        return $instances;

    }

}
