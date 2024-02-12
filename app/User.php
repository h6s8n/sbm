<?php

namespace App;

use App\Model\Arzpaya\ArzpayaUser;
use App\Model\Doctor\CalendarPattern;
use App\Model\Doctor\DoctorDetail;
use App\Model\Doctor\DoctorInformation;
use App\Model\Doctor\Specialization;
use App\Model\Doctor\UserSpecialization;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Partners\Partner;
use App\Model\Partners\PartnerDoctor;
use App\Model\Platform\City;
use App\Model\Platform\FrequentlyAskedQuestion;
use App\Model\Platform\State;
use App\Model\Tag;
use App\Model\Wallet\DoctorWallet;
use App\Model\User\Refund;
use App\Model\User\UserCodes;
use App\Model\User\UserSetting;
use App\Model\Badge\UserBadge;
use App\Model\Badge\Badge;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\Secretary\SpecialSecretary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Spatie\Permission\Traits\HasRoles;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use test\Mockery\MockingStaticMethodsCalledObjectStyleTest;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable , HasRoles;

    public function receivesBroadcastNotificationsOn()
    {
        return 'user.'.$this->id;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'email',
        'mobile',
        'password',
        'status',
        'nationalcode',
        'name',
        'family',
        'fullname',
        'specialcode',
        'gender',
        'doctor_nickname',
        'job_title',
        'birthday',
        'picture',
        'sp_gp',
        'code_title',
        'country_id',
        'state_id',
        'city_id',
        'district_id',
        'street',
        'address',
        'bio',
        'active',
        'approve',
        'doctor_status',
        'mdical_history_status',
        'doctor_info_status',
        'doctor_visit_price',
        'last_calender_time',
        'account_number',
        'account_sheba',
        'passport_image',
        'national_cart_image',
        'education_image',
        'special_cart_image',
        'credit',
        'token',
        'special',
        'skill',
        'show_phone',
        'username',
        'visit_condition',
        'en_url',
        'from_'
    ];
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function scopeSearch($query, $keywords, $mode = true)
    {

        $remove_lists = [
            'ي' => 'ی',
            'ك' => 'ک',
            'دکتر های' => '',
            'دکتر ای' => '',
            'دکتر ها' => '',
            'دکتر ای' => '',
            'دکتر' => '',
            'مشاوره آنلاین' => '',
            'متخصص' => '',
            'مشاوره پزشکی آنلاین' => '',
            'مشاوره پزشکی' => '',
            'پزشکی' => '',
            'پزشکی های' => '',
            'پزشکی ها' => '',
            'پزشکی ای' => '',
            'متخصص های' => '',
            'متخصص ها' => '',
            'متخصص ای' => '',
            'اپلیکیشن' => '',
            'دکتران' => '',
            'دکترها' => '',
        ];
        foreach ($remove_lists as $k => $item) {
            $keywords = str_replace($k, $item, $keywords);
        }

        $keywords = trim($keywords);
        if ($mode) {
            $keywords = explode(' ', $keywords);


            $remove_word = [
                '',
                'و',
                'اگر',
                'چون',
                'اما',
                'زیرا',
                'نیز',
                'یا',
                'نه',
                'چه',
                'باری',
                'که',
                'ولی',
                'پس',
                'را',
                'تا',
                'خواه',
                'لیکن',
                'هم',
                'از',
                'با',
                'من',
                'تو',
                'او',
                'ایشان',
                'آنها',
                'برای',
                'این',
                'آن',
                'ان',
                'ای',
                'برنامه',
                'دانلود',
                'دکتر',
                'پزشکان',
                'پزشک',
                'ها',
                'ی',
            ];


            $keywords[0] = trim($keywords[0]);

            if ($keywords) {
                foreach ($keywords as $k => $item) {
                    if (in_array($item, $remove_word)) {
                        unset($keywords[$k]);
                    } else if (mb_substr($item, 0, 1) == "ا") {
                        $keywords[] = "آ" . mb_substr($item, 1);
                    }
                }
            }

            foreach ($keywords as $keyword) {
                $query->orWhere('fullname', "LIKE", "%" . $keyword . "%")
                    ->orWhere('sp_gp', "LIKE", "%" . $keyword . "%")
                    ->orWhere('special_json', "LIKE", "%" . $keyword . "%")
                    ->orWhere('skill_json', "LIKE", "%" . $keyword . "%")
                    ->orWhere('bio', "LIKE", "%" . $keyword . "%");
            }
        } else {

            $replacing = [
                'یی' => 'ئی',
                'ک' => 'ك',
                'ي' => 'ی',
                'آ' => 'ا',
                'ا' => 'آ',
                'آباد' => 'اباد',
            ];

            $keywords_arr = [$keywords];
            if (strpos($keywords, 'ی') !== false) {
                $keywords_arr[] = str_replace('ی', 'ي', $keywords);
            }
            if (mb_substr($keywords, 0, 1) == "ا") {
                $keywords_arr[] = "آ" . mb_substr($keywords, 1);
            }
            foreach ($replacing as $key => $replace) {
                if (strpos($keywords_arr[0], $key))
                    array_push($keywords_arr, str_replace($key, $replace, $keywords_arr[0]));
                elseif (strpos($keywords_arr[0], $replace))
                    array_push($keywords_arr, str_replace($replace, $key, $keywords_arr[0]));
            }
            array_push($keywords_arr, ' ' . $keywords_arr[0] . ' ');

            foreach ($keywords_arr as $keyword) {
                $query->orWhere('fullname', "LIKE", "%" . $keyword . "%")
                    ->orWhere('sp_gp', "LIKE", "%" . $keyword . "%")
                    ->orWhere('special_json', "LIKE", "%" . $keyword . "%")
                    ->orWhere('skill_json', "LIKE", "%" . $keyword . "%")
                    ->orWhere('bio', "LIKE", "%" . $keyword . "%");
            }


        }

        return $query;
    }

    public function scopeOrdered($query, $jdate)
    {

        $onlineSearch = [];
        $onlineCalenderSearch = [];
        $online_user = DoctorCalender::where('fa_data', '>=', change_number($jdate))->select('user_id', 'time')->orderBy('time', 'desc')->get();

        if ($online_user) {
            foreach ($online_user as $item) {

                if (!in_array($item['user_id'], $onlineCalenderSearch)) {
                    $onlineCalenderSearch[] = $item['user_id'];
                }

            }
        }


        $online_user = DoctorCalender::where('fa_data', '=', change_number($jdate))->where('time', '<=', '24')->where('time', '>=', date('H'))->select('user_id', 'time')->orderBy('time', 'desc')->get();


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

        $ids_ordered = implode(',', $SortSearch);


        if ($ids_ordered) {
            return $query->orderByRaw(DB::raw("FIELD(id, $ids_ordered) DESC"))->orderBy('fullname', 'ASC');
        }

        return $query->orderBy('fullname', 'ASC');
    }

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, UserSpecialization::class);
    }

    public function hasLoginHistory()
    {
        return DB::table('oauth_access_tokens')->where('user_id',$this->id)->exists();
    }

    public function allSpecializationsString()
    {
        $sp = '';
        $specializations = $this->specializations()->get();
        foreach ($specializations as $specialization){
            if ($sp==='')
                $sp = $specialization->name;
            else
                $sp .= ', '.$specialization->name;
        }
        return $sp;
    }
    public function hasSpecialization($id)
    {
        $respond = $this->belongsToMany(Specialization::class, UserSpecialization::class)
            ->whereIn('specialization_id', $id)->get();
        if (!$respond->isEmpty())
            return true;
        return false;
    }

    public function hasSpecialties($id)
    {
        $response = false;
        if (is_array(json_decode($this->special_json))) {
            foreach (json_decode($this->special_json) as $special) {
                if ($special->value == $id) {
                    $response = true;
                }
            }
        }
        return $response;
    }

    public function hasSkill($id)
    {
        $response = false;
        if (is_array(json_decode($this->skill_json))) {
            foreach (json_decode($this->skill_json) as $skill) {
                if ($skill->value == $id) {
                    $response = true;
                }
            }
        }
        return $response;
    }

    public function calenders()
    {
        return $this->hasMany(DoctorCalender::class);
    }

    public function starRates()
    {
        return $this->morphMany(StarRate::class, 'votable');
    }
    public function FAQs()
    {
        return $this->morphMany(FrequentlyAskedQuestion::class, 'questionable');
    }

    public function CreditTransactions($status = null)
    {
        $transactions = $this->hasMany(TransactionCredit::class);
        if ($status)
            return $transactions->where('status', $status);
        return $transactions;

    }

    public function ReserveTransactions($status = null)
    {
        $transactions = $this->hasMany(TransactionReserve::class);
        if ($status)
            return $transactions->where('status', $status);
        return $transactions;

    }

    public function DoctorTransactions($status = null)
    {
        $transactions = $this->hasMany(TransactionDoctor::class,'doctor_id');
        if ($status)
            return $transactions->where('status', $status);
        return $transactions;
    }

    public function DoctorWallet()
    {
        return $this->hasMany(DoctorWallet::class,'doctor_id')->where(['payment_type'=>'Wallet']);
    }

    public function DoctorCOD()
    {
        return $this->hasMany(DoctorWallet::class,'doctor_id')->where(['payment_type'=>'COD']);
    }

    public function SetTimeNotification()
    {
        return $this->hasMany(UserDoctorNotification::class, 'user_id', 'id');
    }

    public function details()
    {
        return $this->hasOne(DoctorDetail::class);
    }

    public function NearestTime()
    {
        return $this->calenders()
//            ->where('partner_id',0)
            ->whereDate('data', '>=',
                DB::raw('(CASE WHEN (date(data) = "' .
                    Carbon::now()->format('Y-m-d') . '"
                and time>= ' . Carbon::now()->hour . ' ) THEN "' .
                    Carbon::now()->format('Y-m-d') . '" ELSE "' . Carbon::now()
                        ->addDay(1)->format('Y-m-d') . '" END)'))
            ->where('capacity', '>', DB::raw('reservation'))
//            ->groupBy(DB::raw('DATE(data)'))
//            ->having(DB::raw('SUM(capacity)'),'>',DB::raw('SUM(reservation)'))
            ->orderBy('data','ASC')
            ->orderBy('time','ASC')
            ->limit(1);
    }

    public function TimesSorted()
    {
        return $this->calenders()
            ->whereDate('data', '>=',
                DB::raw('CASE WHEN (date(data) = "' .
                    Carbon::now()->format('Y-m-d') . '"
                and time> ' . Carbon::now()->hour . ' ) THEN "' .
                    Carbon::now()->format('Y-m-d') . '" ELSE "' . Carbon::now()
                        ->addDay(1)->format('Y-m-d') . '" END'))
            ->where('capacity', '>', DB::raw('reservation'))->orderBy('data','ASC')
            ->orderBy('time','ASC');
    }

    public function UserEvents()
    {
        return $this->hasMany(EventReserves::class, 'user_id');
    }

    public function DoctorEvents($status = null)
    {
        $events =  $this->hasMany(EventReserves::class, 'doctor_id');
        if ($status)
            $events = $events->where('visit_status',$status);
        return $events;
    }

    public function hasTimeWith($doctor_id = null)
    {
        $items = $this->UserEvents()
         //   ->whereDate('data', '>=', date('Y-m-d'))
            ->where('visit_status','not_end');
        if ($doctor_id)
            $items = $items->where('doctor_id', $doctor_id)->get();
        else
            $items = $items->get();
        if (!$items->isEmpty())
            return true;
        return false;
    }

    public function secretary()
    {
        return $this->hasOne(SpecialSecretary::class);
    }

    public function SearchArea()
    {
        return $this->morphOne(Tag::class,'searchable');
    }

    public function Waiting()
    {
        return $this->hasMany(UserDoctorNotification::class,'doctor_id');
    }

    public function partners()
    {
        return $this->hasManyThrough(Partner::class,PartnerDoctor::class,
        'user_id','id','id','partner_id');
    }

    public function information()
    {
        return $this->hasOne(DoctorInformation::class,'doctor_id');
    }

    public function secretaries()
    {
        return $this->hasMany(DoctorInformation::class,'doctor_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class,'user_id');
    }

    public function hasSetting($id,$for=null) : bool
    {
        $response = $this->settings()
            ->where('setting_type_id',$id)
            ->where('subscribed',1)
            ->get();
        if ($response->isEmpty())
            return false;
        return true;
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class , 'user_badges','user_id','badge_id')->withPivot('activation_time','expiration_time');
    }

    public function refundRequest(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function arzpayaUser(): HasOne
    {
        return $this->hasOne(ArzpayaUser::class,'internal_user_id');
    }

    public function pattern(): HasMany
    {
        return $this->hasMany(CalendarPattern::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

}
