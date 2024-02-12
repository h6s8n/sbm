<?php

namespace App\Http\Controllers\Api\v2\Authenticate;

use App\Enums\UserActivityLogEnum;
use App\Http\BlackList;
use App\Http\SystemInfo;
use App\Model\Doctor\DoctorInformation;
use App\Model\User\UserCodes;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;
use App\Repositories\v2\ShortMessageService\ShortMessageInterface;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\Repositories\v2\User\UserInterface;
use App\RequestCodesLog;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthenticateController extends Controller
{
    private $sms;
    private $user;
    private $ActivityLog;
    protected $request;


    public function __construct(ShortMessageInterface $sms,
                                UserInterface $user,
                                UserActivityLogInterface $activityLog,
                                Request $request)
    {
        $this->sms = $sms;
        $this->user = $user;
        $this->ActivityLog = $activityLog;
        $this->request = $request;
    }

    public function SendConfirmationCode()
    {
        
        /* Validate Mobile Number */
        \request()->validate([
            'mobile' => 'nullable|digits:11|starts_with:09',
            'email' => 'nullable|email'
        ], [
            'mobile.digits' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'mobile.starts_with' => 'شماره همراه نامعتبر',
            'email.email' => 'ایمیل را لاتین و با فرمت صحیح وارد کنید'
        ]);
		
		
		 

        if (\request()->has('mobile')) {
			  if(request()->has('reqtoken')) {
        } else {
           			                    return error_template('به دلایل مسائل امنیتی این امکان برای شما فراهم نیست');

        }
            $user = User::where('mobile',change_number(\request()->input('mobile')))->first();
            $code = UserCodes::where('mobile', change_number(\request()->input('mobile')))->first();
            if ($code) {
                $t1 = Carbon::parse($code->created_at);
                $t2 = Carbon::now();
                if ($t1->format('Y-m-d H:i') == $t2->format('Y-m-d H:i')) {
                    $diff = $t1->diffInRealSeconds($t2);
                    if ($diff < 30)
                    return error_template('کاربر گرامی، شما در هر ۳۰ ثانیه مجاز به ارسال یک پیام هستید');
                }
            }
        }else{
            $user = User::where('email',change_number(\request()->input('email')))->first();
        }


            if (strlen(optional($user)->password) < 7) {
                if (\request()->has('mobile')) {
                    return $this->SendCode();
                }
                return $this->SendEmail();
            }
            return success_template(['has_password' => true]);

    }

    // Wallet decrease
    public function SendValidationCode()
    {
        $user = auth()->user();
        $code = UserCodes::where('mobile', change_number($user->mobile))->first();
        if ($code) {
            $t1 = Carbon::parse($code->created_at);
            $t2 = Carbon::now();
            if ($t1->format('Y-m-d H:i') == $t2->format('Y-m-d H:i')) {
                $diff = $t1->diffInRealSeconds($t2);
                if ($diff < 30)
                return error_template('کاربر گرامی، شما در هر ۳۰ ثانیه مجاز به ارسال یک پیام هستید');
            }
        }
        \request()->merge(['mobile' => $user->mobile]);
        return $this->SendCode();
    }


    /** Validate confirmation code
     * @return JsonResponse
     */
    public function ValidateCode()
    {
        \request()->validate([
            'code' => 'required'
        ], [
            'code.required' => 'ورود کد تایید الزامی است',
            'code.required' => 'کد تایید را صحیح وارد کنید'
        ]);
        if (\request()->has('mobile'))
            return $this->ValidateWithMobile();
        return $this->ValidateWithEmail();
    }

    public function ValidateWithMobile()
    {
        $mobile = change_number(\request()->input('mobile'));
        $secretary = null;
        $user = $this->user->findByMobile($mobile);

        /* Check Existence Of User*/

        $secretary = DoctorInformation::whereOfficeSecretaryMobile($mobile)->first();
        if ($secretary)
            $user = $this->user->findByMobile($secretary->doctor->mobile);


        $SentCode = change_number(\request()->input('code'));
        $code = UserCodes::where('mobile', $mobile)->first();
        if (!$code)
            return error_template('شماره موبایل یافت نشد');

        if ($user->status && $user->object) {
            $user = $user->object;
            if ($user->approve == 0)
                return error_template('Error 403');
            if (Hash::check($SentCode, $code->code)) {
                if ($secretary)
                    return $this->LogInUser($secretary->doctor,false,true);
                return $this->LogInUser($user);
            }
            return error_template('کد نا معتبر است');
        } else {
            $token = str_random(6);
            $username = str_random(6);
            $password = str_random(6);
            $approve = \request()->has('approve') ? \request()->input('approve') : 2;
            if ($approve==1)
                return error_template('پزشک گرامی لطفا از قسمت "همکاری به عنوان پزشک" ثبت نام نمایید.');
            else
                $condition=null;
            $user = $this->user->store([
                'token' => $token,
                'username' => $username,
                'password' => $password,
                'mobile' => $mobile,
                'approve' => 2,
                'visit_condition'=>$condition
            ]);
            if ($user->status) {
                if (Hash::check($SentCode, $code->code)){
                    if ($secretary)
                        return $this->LogInUser($secretary->doctor,true,true);
                    return $this->LogInUser($user->object, true);
                }
                return error_template('کد وارد شده است');
            } else {
                return error_template('مشکلی در ثبت کاربر بوجود آمده است');
            }
        }
    }

    public function ValidateWithEmail()
    {
        /* Check Existence Of User*/
        $user = $this->user->findByEmail(\request()->input('email'));
        $code = UserCodes::where('mobile', \request()->input('email'))->first();
        if (!$code)
            return error_template('کد نا معتبر است');
        if ($user->status && $user->object) {
            $user = $user->object;
            if (Hash::check(\request()->input('code'), $code->code)) {
                return $this->LogInUser($user);
            }
            return error_template('کد نا معتبر است');
        } else {
            $token = str_random(6);
            $username = str_random(6);
            $password = str_random(6);
            $approve = \request()->has('approve') ? \request()->input('approve') : 2;
            if ($approve==1)
                return error_template('پزشک گرامی لطفا از قسمت "همکاری به عنوان پزشک" ثبت نام نمایید.');

            else
                $condition=null;
            $user = $this->user->store([
                'token' => $token,
                'username' => $username,
                'password' => $password,
                'email' => change_number(\request()->input('email')),
                'approve' => $approve,
                'visit_condition'=>$condition
            ]);
            if ($user->status) {
                if (Hash::check(\request()->input('code'), $code->code))
                    return $this->LogInUser($user->object, true);
                return error_template('کد نا معتبر است');
            } else {
                return error_template('مشکلی در ثبت کاربر بوجود آمده است');
            }
        }
    }

    /** Send Confirmation Code
     * @return JsonResponse
     */
    private function SendCode()
    {
        $code = '';
        for ($i = 1; $i <= 6; $i++)
            $code = rand(1, 9) . $code;

        $mobile = change_number(\request()->input('mobile'));

        $origin = (string)\request()->headers->get('origin');

        if ($origin == "https://sbm24.com/"
            || $origin == "https://sbm24.com"
            || $origin == "https://cp.sbm24.com/"
            || $origin == "https://cptest.sbm24.com/"
            || $origin == "https://cp.sbm24.com"
            || $origin == "https://cptest.sbm24.com"
            || $origin == "https://ashidnetwork.ir"
            || $origin == "https://ashidnetwork.ir/"
            || $origin == "http://ashidnetwork.ir/"
            || $origin == "http://ashidnetwork.ir"
            || $origin == "http://localhost:3000"
            || $origin == "http://localhost"
            || $origin == "https://cpstage.sbm24.com") {

            if (BlackList::black(\request()->ip()) && \request()->ip() != '89.219.230.8') {
                try {
                    RequestCodesLog::create([
                        'mobile' => $mobile,
                        'code' => $code,
                        'ip' => \request()->ip(),
                        'os' => SystemInfo::info(),
                        'host' => $origin,
                        'message' => 'Block List'
                    ]);
                } catch (\Exception $exception) {
                   
                }
                return success_template('false');
            }
            $count = RequestCodesLog::where('ip', \request()->ip())
                ->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get()->count();
			
            if ($mobile !="09183640998" ) {
                if ($count >= 5) {
                    try {
                        RequestCodesLog::create([
                            'mobile' => $mobile,
                            'code' => $code,
                            'ip' => \request()->ip(),
                            'os' => SystemInfo::info(),
                            'host' => $origin,
                            'message' => 'More than 5 attempts'
                        ]);
                    } catch (\Exception $exception) {
						
                    }
                    return error_template('آیپی شما تا 24 ساعت بلاک است');
                }
            }
            if (!$mobile)
                return error_template('موبایل معتبر نیست');
            UserCodes::where('mobile', $mobile)->delete();
            UserCodes::create([
                'mobile' => $mobile,
                'code' => Hash::make($code)
            ]);

            $response=false;
            if ($response) {
                try {
                    RequestCodesLog::create([
                        'mobile' => $mobile,
                        'code' => $code,
                        'ip' => \request()->ip(),
                        'os' => SystemInfo::info(),
                        'host' => $origin,
                        'message' => 'sms.ir'
                    ]);
                } catch (\Exception $exception) {
                    
                }
                return success_template([
                    'mobile' => $mobile,
                    'has_password' => false
                ]);
            } else {
                $response = $this->sms
                    ->sendConfirmationCode($mobile, $code);
                if ($response->status) {
                    try {
                        RequestCodesLog::create([
                            'mobile' => $mobile,
                            'code' => $code,
                            'ip' => \request()->ip(),
                            'os' => SystemInfo::info(),
                            'host' => $origin,
                            'message' => 'kavenegar.com'
                        ]);
                    } catch (\Exception $exception) {
                        ;
                    }
                    return success_template([
                        'mobile' => $mobile,
                        'has_password' => false
                    ]);
                }
            }
            try {
                RequestCodesLog::create([
                    'mobile' => $mobile,
                    'code' => $code,
                    'ip' => \request()->ip(),
                    'os' => SystemInfo::info(),
                    'host' => $origin,
                    'message' => 'All failed'
                ]);
            } catch (\Exception $exception) {
                ;
            }
            return error_template("خطا در ارسال پیام");
        }
        return error_template("خطا در ارسال پیام");
    }

    public function SendEmail()
    {
        $origin = (string)\request()->headers->get('origin');
        $to_email = \request()->input('email');
        $code = '';
        for ($i = 1; $i <= 6; $i++)
            $code = rand(1, 9) . $code;
        if (BlackList::black(\request()->ip()) && \request()->ip() != '94.182.198.50') {
            try {
                RequestCodesLog::create([
                    'mobile' => $to_email,
                    'code' => $code,
                    'ip' => \request()->ip(),
                    'os' => SystemInfo::info(),
                    'host' => $origin,
                    'message' => 'Block List'
                ]);
            } catch (\Exception $exception) {
                ;
            }
            return success_template('false');
        }
        $count = RequestCodesLog::where('ip', \request()->ip())
            ->whereDate('created_at', Carbon::now()
                ->format('Y-m-d'))->get()->count();
        if (\request()->ip() != '94.182.198.50') {
            if ($count >= 5) {
                try {
                    RequestCodesLog::create([
                        'mobile' => $to_email,
                        'code' => $code,
                        'ip' => \request()->ip(),
                        'os' => SystemInfo::info(),
                        'host' => $origin,
                        'message' => 'More than 5 attempts'
                    ]);
                } catch (\Exception $exception) {
                }
                return success_template('false');
            }
        }
        UserCodes::where('mobile', \request()->input('email'))->delete();
        UserCodes::create([
            'mobile' => \request()->input('email'),
            'code' => Hash::make($code)
        ]);

        $subject = 'کد تأیید حساب ' . $code;
            Mail::send('emails.confirmAccount', ['token' => $code], function ($message) use ($subject, $to_email) {
                $message->from('noreply@sbm24.net', "sbm24");
                $message->to($to_email)->subject($subject);
            });
            try {
                RequestCodesLog::create([
                    'mobile' => $to_email,
                    'code' => $code,
                    'ip' => \request()->ip(),
                    'os' => SystemInfo::info(),
                    'host' => $origin,
                    'message' => 'Email'
                ]);
            } catch (\Exception $exception) {
                ;
            }
        return success_template([
            'email' => \request()->input('email') ,
            'has_password' => false
        ]);
        //return success_template(['code' => $code]);
    }

    /** Log in a user
     * @param User $user
     * @param bool $new_user
     * @return JsonResponse
     */
    private function LogInUser(User $user, $new_user = false , $is_secretary = false)
    {
        if ($user->approve == 0)
            return error_template('Error 403');

        Auth::login($user, 1);
        if (Auth::check()) {
            $this->ActivityLog->CreateLog($user, UserActivityLogEnum::UserLogin);
            UserCodes::where('mobile', \request()->input('mobile'))->delete();
            $token = auth()->user()->createToken('Api Token On Login To App')->accessToken;
            $code = change_number(\request()->input('code'));
            $instance = RequestCodesLog::where('code',$code)->first();
            if ($instance) {
                $instance->verfied = 1;
                $instance->save();
                
            }
            
            return success_template([
                'access_token' => $token,
                'full_name' => $user->fullname,
                'mobile' => $user->mobile,
                'name' => $user->name,
                'family' => $user->family,
                'longitude' => $user->longitude,
                'latitude' => $user->latitude,
                'image' => $user->picture,
                'nick_name' => $user->nick_name,
                'user_id' => $user->id,
                'is_secretary' =>  $is_secretary,
                'approve' => $user->approve,
                'new_user' => $new_user,
            ]);
        }
        return error_template('مشکلی در لاگین بوجود آمده است');
    }

    public function logout()
    {
        $user = \auth()->user();
        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::UserLogout);
        $user->token()->revoke();

        return success_template(1);
    }

    public function MellatLogin(): JsonResponse
    {
        $reference_token ="VgizfwZpoDzaDz";

        if (\request()->has('token') && \request()->input('token') === $reference_token)
        {
            /* @var User $user*/
            $mobile = change_number(\request()->input('mobile'));

            if ($mobile) {
                $user = $this->user->findByMobile($mobile);
            }
            else {
                return error_template('شماره همراه وارد نشده است');
            }

            if ($user->status && $user->object){
                $user=$user->object;
                $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                $name = \request()->input('name') ?: null;
                $family = \request()->input('family') ?: null;
                $fullname = $name && $family ? $name.' '.$family :' ';
                if ($name && $family)
                {
                    $user->name = $name;
                    $user->family = $family;
                    $user->fullname = $fullname;
                    $user->save();
                }
                return success_template([
                    'link'=>'https://cp.sbm24.com/authorization?name='.urlencode($user->name).'&family='.urlencode($user->family).'&fullname='.urlencode($user->fullname).'&access_token='.urlencode($access_token).'&image='.urlencode($user->picture).'&user_id'.$user->id.'&approve='.$user->approve
                ]);

            }
            $token = str_random(6);
            $username = str_random(6);
            $password = str_random(6);
            $name = \request()->input('name') ?: null;
            $family = \request()->input('family') ?: null;
            $fullname = $name && $family ? $name.' '.$family :' ';

           $user = $this->user->store([
               'token' => $token,
               'name'=>$name,
               'family'=>$family,
               'fullname'=>$fullname,
               'username' => $username,
               'password' => $password,
               'mobile' => $mobile,
               'approve' => 2
           ]);
            if ($user->status && $user->object){
                $user=$user->object;
                $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                return success_template([
                    'link'=>'https://cp.sbm24.com/authorization?name='.urlencode($user->name).'&family='.urlencode($user->family).'&fullname='.urlencode($user->fullname).'&access_token='.urlencode($access_token).'&image='.urlencode($user->picture).'&user_id'.$user->id.'&approve='.$user->approve
                ]);
            }
            return error_template('مشکلی در ورود کاربر به وجود آمده است');
        }
        return error_template('Error 403');
    }

    public function jiringLogin(): JsonResponse
    {
        $reference_token ="zPyPONbJ";

        if (\request()->ip()=='79.175.164.98' || \request()->ip() == '46.209.208.220') {
            if (\request()->has('token') && \request()->input('token') === $reference_token) {
                /* @var User $user */
                $mobile = change_number(\request()->input('mobile'));

                if ($mobile) {
                    $user = $this->user->findByMobile($mobile);
                } else {
                    return error_template('شماره همراه وارد نشده است');
                }

                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    $name = \request()->input('name') ?: null;
                    $family = \request()->input('family') ?: null;
                    $fullname = $name && $family ? $name . ' ' . $family : ' ';
                    if ($name && $family) {
                        $user->name = $name;
                        $user->family = $family;
                        $user->fullname = $fullname;
                        $user->save();
                    }
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                $token = str_random(6);
                $username = str_random(6);
                $password = str_random(6);
                $name = \request()->input('name') ?: null;
                $family = \request()->input('family') ?: null;
                $fullname = $name && $family ? $name . ' ' . $family : ' ';

                $user = $this->user->store([
                    'token' => $token,
                    'name' => $name,
                    'family' => $family,
                    'fullname' => $fullname,
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobile,
                    'approve' => 2,
                    'from_' => 83401
                ]);
                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                return error_template('مشکلی در ورود کاربر به وجود آمده است');
            }
        }
        return error_template('Error 403');
    }

    public function hikishLogin(): JsonResponse
    {
        $reference_token ="DXkxklPSfKotGwrlHUEO";

        if (\request()->ip()=='45.82.136.35') {
            if (\request()->has('token') && \request()->input('token') === $reference_token) {
                /* @var User $user */
                $mobile = change_number(\request()->input('mobile'));

                if ($mobile) {
                    $user = $this->user->findByMobile($mobile);
                } else {
                    return error_template('شماره همراه وارد نشده است');
                }

                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    $name = \request()->input('name') ?: null;
                    $family = \request()->input('family') ?: null;
                    $fullname = $name && $family ? $name . ' ' . $family : ' ';
                    if ($name && $family) {
                        $user->name = $name;
                        $user->family = $family;
                        $user->fullname = $fullname;
                        $user->save();
                    }
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                $token = str_random(6);
                $username = str_random(6);
                $password = str_random(6);
                $name = \request()->input('name') ?: null;
                $family = \request()->input('family') ?: null;
                $fullname = $name && $family ? $name . ' ' . $family : ' ';

                $user = $this->user->store([
                    'token' => $token,
                    'name' => $name,
                    'family' => $family,
                    'fullname' => $fullname,
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobile,
                    'approve' => 2,
                    'from_' => 103496
                ]);
                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                return error_template('مشکلی در ورود کاربر به وجود آمده است');
            }
        }
        return error_template('Error 403');
    }

    public function pulsynoLogin(): JsonResponse
    {
        $reference_token ="0lJKFtj4DnOh1Q";

        if (\request()->ip()=='193.151.131.57' || \request()->ip() == '94.182.198.50') {
            if (\request()->has('token') && \request()->input('token') === $reference_token) {
                /* @var User $user */
                $mobile = change_number(\request()->input('mobile'));

                if ($mobile) {
                    $user = $this->user->findByMobile($mobile);
                } else {
                    return error_template('شماره همراه وارد نشده است');
                }

                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    $name = \request()->input('name') ?: null;
                    $family = \request()->input('family') ?: null;
                    $fullname = $name . ' ' . $family;
                    if ($name && $family) {
                        $user->name = $name;
                        $user->family = $family;
                        $user->fullname = $fullname;
                        $user->save();
                    }
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                $token = str_random(6);
                $username = str_random(6);
                $password = str_random(6);
                $name = \request()->input('name') ?: null;
                $family = \request()->input('family') ?: null;
                $fullname = $name && $family ? $name . ' ' . $family : ' ';

                $user = $this->user->store([
                    'token' => $token,
                    'name' => $name,
                    'family' => $family,
                    'fullname' => $fullname,
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobile,
                    'approve' => 2,
                    'from_' => 151122
                ]);
                if ($user->status && $user->object) {
                    $user = $user->object;
                    $access_token = $user->createToken('Api Token On Login To App')->accessToken;
                    return success_template([
                        'link' => 'https://cp.sbm24.com/authorization?name=' . urlencode($user->name) . '&family=' . urlencode($user->family) . '&fullname=' . urlencode($user->fullname) . '&access_token=' . urlencode($access_token) . '&image=' . urlencode($user->picture) . '&user_id' . $user->id . '&approve=' . $user->approve
                    ]);
                }
                return error_template('مشکلی در ورود کاربر به وجود آمده است');
            }
        }
        return abort( 403,'Forbidden');

    }

    public function login()
    {
        /* Validate Mobile Number */
        \request()->validate([
            'mobile' => 'required_without:email|digits:11',
            'email' => 'required_without:mobile|email',
            'password' => 'required'
        ], [
            'mobile.digits' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'mobile.required_without' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'email.email' => 'ایمیل را لاتین و با فرمت صحیح وارد کنید',
            'email.required_without' => 'ایمیل را لاتین و با فرمت صحیح وارد کنید',
            'password.required' => 'کلمه عبور الزامیست.'
        ]);

        $login = [];
        $password = \request()->input('password');

        $is_secretary = false;

        if (\request()->has('mobile')) {

            $username = change_number(change_phone(\request()->input('mobile')));

            $login = ['mobile' => $username, 'password' => $password, 'status' => ['active','imported']];
            $secretary = DoctorInformation::whereOfficeSecretaryMobile($username)->first();
            if($secretary) {
                $user = User::whereMobile($secretary->doctor->mobile)->first();
                $is_secretary = true;
            }else {
                $user = User::where('mobile', $username)->first();
            }

        } elseif (\request()->has('email')){
            $username = change_number(\request()->input('email'));

            $login = ['email' => $username, 'password' => $password, 'status' => ['active','imported']];
            $user = User::where('email', $username)->first();
        }


        $login = auth()->attempt($login);
        if($login) {
            if ($user->approve == 0)
                return error_template('Error 403');

            Auth::login($user, 1);
            if (Auth::check()) {
                $this->ActivityLog->CreateLog($user, UserActivityLogEnum::UserLogin);
                $token = auth()->user()->createToken('Api Token On Login To App')->accessToken;

                return success_template([
                    'access_token' => $token,
                    'full_name' => $user->fullname,
                    'mobile' => $user->mobile,
                    'name' => $user->name,
                    'family' => $user->family,
                    'image' => $user->picture,
                    'longitude' => $user->longitude,
                    'latitude' => $user->latitude,
                    'nick_name' => $user->nick_name,
                    'user_id' => $user->id,
                    'is_secretary' => $is_secretary,
                    'approve' => $user->approve,
                    'new_user' => false,
                ]);
            }
        }
        return error_template('اطلاعات ورود اشتباه است');

    }

    public function register()
    {
        \request()->validate([
            'mobile'              => 'required_without:email|digits_between:10,11',
            'email'               => 'required_without:mobile|email',
            'name'                => 'required',
            'family'              => 'required',
            'job_title'           => 'required',
            'specialcode'         => 'unique:users,specialcode',
            'national_cart_image' => 'mimes:pdf,jpg,jpeg,png|max:2048',
            'special_cart_image'  => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
            'education_image'     => 'mimes:pdf,jpg,jpeg,png|max:2048',
            'picture'             => 'mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'mobile.digits_between' => 'تعداد ارقام و فرمت شماره همراه صحیح نیست',
            'mobile.required_without' => 'شماره همراه یا ایمیل الزامی است',
            'email.email' => 'ایمیل را لاتین و با فرمت صحیح وارد کنید',
            'email.required_without' => 'شماره همراه یا ایمیل الزامی است',
            'name.required' => 'نام الزامی است',
            'family.required' => 'نام خانوادگی الزامی است',
            'job_title.required' => 'عنوان تخصص الزامی است',
            'specialcode.required' => 'شماره نظام پزشکی الزامی است',
            'specialcode.unique' => 'این شماره نظام پزشکی قبلا ثبت شده است',
            'mobile.unique' => 'این موبایل  قبلا ثبت شده است',
            'email.unique' => 'این ایمیل  قبلا ثبت شده است',
            'special_cart_image.required' => 'تصویر کارت نظام پزشکی الزامی است',

            'picture.mimes' => 'نوع فایل تصویر پروفایل باید pdf,jpg,jpeg یا png باشد.',
            'picture.max' => 'حجم تصویر پروفایل نباید بیشتر از ۲ مگابایت باشد.',
            'national_cart_image.max' => 'حجم تصویر کارت ملی نباید بیشتر از ۲ مگابایت باشد.',
            'national_cart_image.mimes' => 'نوع فایل تصویر کارت ملی باید pdf,jpg,jpeg یا png باشد.',
            'special_cart_image.max' => 'حجم تصویر کارت نظام پزشکی نباید بیشتر از ۲ مگابایت باشد.',
            'special_cart_image.mimes' => 'نوع فایل تصویر کارت نظام پزشکی باید pdf,jpg,jpeg یا png باشد.',
            'education_image.max' => 'حجم تصویر کارت پروانه مطب/مدرک تحصیلی نباید بیشتر از ۲ مگابایت باشد.',
            'education_image.mimes' => 'نوع فایل تصویر کارت پروانه مطب/مدرک تحصیلی باید pdf,jpg,jpeg یا png باشد.',
        ]);

        $mobile = change_number(\request()->input('mobile',NULL));
        $email = \request()->input('email',NULL);
        $name = \request()->input('name');
        $family = \request()->input('family');
        $specialcode = \request()->input('specialcode');
        $job_title = \request()->input('job_title');
        $picture = null;
        $national_cart_image = null;
        $special_cart_image = null;
        $education_image = null;

        if ($mobile){
            $user = User::whereMobile($mobile)->first();
            if ($user){
                return success_template(['success'=>true]);
            }
        }
        if ($email){
            $user = User::whereEmail($email)->first();
            if ($user){
                return success_template(['success'=>true]);
            }
        }

        $condition='{"my_patient_only":"false","consultation_type" :{"videoConsultation":"true","voiceConsultation":"true","textConsultation":"true"}}';

        $token = str_random(6);
        $username = str_random(6);
        $password = str_random(6);

        if (\request()->file('national_cart_image')) {
            $national_cart_image = $this->uploadImageCt('national_cart_image', 'images');
        }
        if (\request()->file('special_cart_image')) {
            $special_cart_image = $this->uploadImageCt('special_cart_image', 'images');
        }
        if (\request()->file('education_image')) {
            $education_image = $this->uploadImageCt('education_image', 'images');
        }
        if (\request()->file('picture')) {
            $picture = $this->uploadImageCt('picture', 'images');
        }

        $user = new User();

            $user->token = $token;
            $user->username = $username;
            $user->name = trim($name);
            $user->family = trim($family);
            $user->password = $password;
            $user->fullname = trim($name . ' ' . $family);
            $user->specialcode = $specialcode;
            $user->job_title = $job_title;
            if ($mobile){

                $user->mobile = $mobile ?? NULL;
            }
            if ($email){
                $user->email = $email ?? NULL;
            }
            $user->special_cart_image = $special_cart_image;
            $user->education_image = $education_image;
            $user->national_cart_image = $national_cart_image;
            $user->picture = $picture;
            $user->approve = 1;
            $user->visit_condition = $condition;

        if ($user->save()){

            if ($user->mobile) {
                $params = array(
                    "token" => $user->fullname,
                );
                SendSMS::send($user->mobile, 'doctorRegister', $params);
            }
            return success_template(['success'=>true]);
        }

        return error_template(['success'=>false]);

    }

    public function forget()
    {
        \request()->validate([
            'mobile' => 'nullable|digits:11|starts_with:09',
            'email' => 'nullable|email'
        ], [
            'mobile.digits' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'mobile.starts_with' => 'شماره همراه نامعتبر',
            'email.email' => 'ایمیل را لاتین و با فرمت صحیح وارد کنید'
        ]);

        if (\request()->has('mobile')) {
            $user = User::where('mobile',change_number(\request()->input('mobile')))->first();
            $code = UserCodes::where('mobile', change_number(\request()->input('mobile')))->first();
            if ($code) {
                $t1 = Carbon::parse($code->created_at);
                $t2 = Carbon::now();
                if ($t1->format('Y-m-d H:i') == $t2->format('Y-m-d H:i')) {
                    $diff = $t1->diffInRealSeconds($t2);
                    if ($diff < 30)
                        return error_template('کاربر گرامی، شما در هر ۳۰ ثانیه مجاز به ارسال یک پیام هستید');
                }
            }
        }else{
            $user = User::where('email',change_number(\request()->input('email')))->first();
        }

        if (\request()->has('mobile')) {
            return $this->SendCode();
        }
        return $this->SendEmail();


    }

}
