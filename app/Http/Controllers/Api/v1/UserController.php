<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\BlackList;
use App\Http\Controllers\Api\v1\Doctor\CalendarController;
use App\Http\SystemInfo;
use App\Model\User\UserConfirm;
use App\RequestCodesLog;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use SoapFault;

class UserController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
    }


    public function LoginAccount()
    {
        return true;
        $username = change_number($this->request->get('username'));
        $login = [];

        $username_old = str_replace('+', '', $username);
        $this->request['username'] = $username_old;

        if (is_numeric($username_old)) {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|digits_between:10,15',
                'password' => 'required',
            ]);

            $username = change_phone($username);

            $login = ['mobile' => $username, 'password' => $ValidData['password'], 'status' => 'active'];
            $user_status = User::where('mobile', $username)->first();

        } else {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
                'password' => 'required',
            ]);

            $login = ['email' => $username, 'password' => $ValidData['password'], 'status' => 'active'];
            $user_status = User::where('email', $username)->first();

        }

        $login = auth()->attempt($login);
        // Check Login User
        if (!$login) {

            //if($user_status) add_log_visit($user_status->id, 'login', 'error');

            return error_template('نام کاربری و رمز عبور اشتباه است.');

        }


        //auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('Api Token On Login To App')->accessToken;


        //return error_template( $token );

        //if($user_status) add_log_visit($user_status->id, 'login');

        // Return response
        /*return new UserResource(auth()->user(), $token);*/


        //$twilio = $this->twilio();


        return success_template(['access_token' => $token, 'turn' => [], 'two_factor' => false, 'status' => true]);


    }


    /**
     * Register
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerConfirm()
    {
        
        $username = change_number($this->request->get('username'));
        $type_field = 'email';
        $login = [];

        $username_old = str_replace('+', '', $username);
        $this->request['username'] = $username_old;

        if (is_numeric($username_old)) {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|digits_between:10,15',
            ]);

            $username = change_phone($username);

            $type_field = 'mobile';

        } else {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
            ]);

        }

        $user = User::where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->first();

        if ($user) {
            return error_template('این شماره موبایل و یا ایمیل قبلا در سامانه ثبت شده است. برای ورود به حساب کاربری از بخش ورود اقدام نمایید.');
        }

        $request = UserConfirm::where('status', 'not_verified')->where('created_at', '>=', Carbon::now()->subHour(1))->where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->count();

        if ($request > 10) {
            return error_template('تعداد درخواست شما بیش از حد مجاز می باشد ، لطفا یک ساعت دیگر امتحان کنید.');
        }


        $app_name = config('app.name');
        //$confirmCod = 1111;
        $confirmCod = rand(111111, 999999);
        /*$confirmCodSend = implode("-", str_split($confirmCod, 3));*/

        $requestNew = new UserConfirm();
        $requestNew->mobile = ($type_field == 'mobile') ? $username : null;
        $requestNew->email = ($type_field == 'email') ? $username : null;
        $requestNew->confirm = $confirmCod;
        if ($requestNew->save()) {
            if ($type_field == 'mobile') {
                $origin = (string)\request()->headers->get('origin');
                if ($origin == "https://sbm24.com/"
                    || $origin == "https://sbm24.com"
                    || $origin == "https://cp.sbm24.com/"
                    || $origin == "https://cp.sbm24.com") {
                    if (BlackList::black(\request()->ip())) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' => $confirmCod,
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
                        ->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get()->count();
                    if ($count >= 7) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' => $confirmCod,
                                'ip' => \request()->ip(),
                                'os' => SystemInfo::info(),
                                'host' => $origin,
                                'message' => 'More than 5 attempts'
                            ]);
                        } catch (\Exception $exception) {
                        }
                        return success_template('false');
                    }
                    SendSMS::sendConfirmTemplate($username, $confirmCod);
                    try {
                        RequestCodesLog::create([
                            'mobile' => $username,
                            'code' => $confirmCod,
                            'ip' => \request()->ip(),
                            'os' => SystemInfo::info(),
                            'host' => $origin,
                            'message' => 'Kavenegar'
                        ]);
                    } catch (\Exception $exception) {
                        ;
                    }
                }

            } else {

                $email = $username;
                $subject = 'کد تأیید حساب ' . $app_name . ' : ' . $confirmCod;

                Mail::send('emails.confirmAccount', ['token' => $confirmCod], function ($message) use ($subject, $email) {
                    $message->from('noreply@sbm24.net', "sbm24");
                    $message->to($email)->subject($subject);
                });

            }


            $success = success_template(['confirmation' => 'Send Code', 'access' => $username, 'type' => $type_field, 'confirmCod' => $confirmCod]);


            return $success;

        }

        return error_template('خطا در ثبت اطلاعات، لطفا مجددا تلاش کنید.');

    }

    public function confirmResend()
    {

        return true;
        $username = change_number($this->request->get('username'));

        $type_field = 'email';

        $username_old = str_replace('+', '', $username);
        $this->request['username'] = $username_old;

        if (is_numeric($username_old)) {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|digits_between:10,15',
            ]);

            $username = change_phone($username);

            $type_field = 'mobile';

        } else {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
            ]);

        }

        $request = UserConfirm::where('status', 'not_verified')->where('created_at', '>=', Carbon::now()->subHour(1))->where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->first();

        if ($request) {

            $expiredTime = Carbon::parse($request->created_at)->addMinutes(1);
            $newtime = date('Y-m-d H:i:s');

            if (strtotime($expiredTime) > strtotime($newtime)) {

                return error_template('در حال حاضر امکان ارسال مجدد نمی باشد لطفا یک دقیقه دیگر امتحان کنید.');

            }

            $request->created_at = date('Y-m-d H:i:s');
            $request->save();


            $app_name = config('app.name');
            $confirmCod = $request->confirm;

            if ($type_field == 'mobile') {
                $origin = (string)\request()->headers->get('origin');
                if ($origin == "https://sbm24.com/"
                    || $origin == "https://sbm24.com"
                    || $origin == "https://cp.sbm24.com/"
                    || $origin == "https://cp.sbm24.com") {
                    if (BlackList::black(\request()->ip())) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' => $confirmCod,
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
                        ->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get()->count();
                    if ($count >= 7) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' =>$confirmCod,
                                'ip' => \request()->ip(),
                                'os' => SystemInfo::info(),
                                'host' => $origin,
                                'message' => 'More than 5 attempts'
                            ]);
                        } catch (\Exception $exception) {
                        }
                        return success_template('false');
                    }
                    SendSMS::sendConfirmTemplate($username, $confirmCod);
                    try {
                        RequestCodesLog::create([
                            'mobile' => $username,
                            'code' => $confirmCod,
                            'ip' => \request()->ip(),
                            'os' => SystemInfo::info(),
                            'host' => $origin,
                            'message' => 'Kavenegar'
                        ]);
                    } catch (\Exception $exception) {
                        ;
                    }
                }

            } else {

                $email = $username;
                $subject = 'کد تأیید حساب ' . $app_name . ' : ' . $confirmCod;

                Mail::send('emails.confirmAccount', ['token' => $confirmCod], function ($message) use ($subject, $email) {
                    $message->from('noreply@sbm24.net', "sbm24");
                    $message->to($email)->subject($subject);
                });

            }

            return success_template(['message' => ' کد تایید مجددا برای شما ارسال شد.']);

        }

        return error_template('کدی جهت ارسال برای شما ثبت نشده است لطفا به مرحله قبل بازگردید.');

    }

    public function actionConfirm()
    {
        return true;
        $username = change_number($this->request->get('username'));
        $this->request['username'] = $username;

        $type_field = 'email';

        $username_old = str_replace('+', '', $username);
        $this->request['username'] = $username_old;

        if (is_numeric($username)) {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|digits_between:10,15',
                'code' => 'required|numeric',
            ]);

            $username = change_phone($username);


            $type_field = 'mobile';

        } else {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
                'code' => 'required|numeric',
            ]);

        }


        $request = UserConfirm::where('confirm', $ValidData['code'])->where('status', 'not_verified')->where('created_at', '>=', Carbon::now()->subHour(1))->where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->first();

        if ($request) {

            $request->status = 'verified';
            $request->save();


            $user = User::where('status', 'active')->where(function ($query) use ($username) {
                $query->where('email', $username)->orWhere('mobile', $username);
            })->orderBy('created_at', 'desc')->count();

            $created_status = false;
            if ($user > 0) {

                $created_status = true;

            }


            return success_template(['confirmation' => 'verified', 'created_status' => $created_status, 'info' => ['username' => $username, 'type' => $type_field]]);

        }


        return error_template('کد وارد شده صحیح نیست.');

    }

    public function actionNationalcode()
    {
        // Validation Data
        $ValidData = $this->validate($this->request, [
            'nid' => 'numeric|digits:10|unique:users,nationalcode',
        ], [
            'numeric' => 'لطفا کد ملی را صحیح وارد کنید.',
            'digits' => 'لطفا کدملی را کامل وارد کنید.',
            'unique' => 'این کد ملی قبلا در سیستم ثبت شده است.'
        ]);

//        $request = User::where('nationalcode' , $ValidData['nid'])->first();
//        if($request){
//            return error_template('این کد ملی در سیستم ثبت شده است.');
//        }
        //$dataInsurance = $this->InsuranceInquiry($ValidData['nid']);
        $dataInsurance = [];

        return success_template($dataInsurance);

    }

    public function createAccount()
    {
        // Validation Data
        $approve = $this->validate($this->request, [
            'approve' => 'required|in:1,2',
            'username' => 'required',
            'type_field' => 'required',
        ]);

        if ($approve['type_field'] == 'mobile') {
            $ValidUsername = $this->validate($this->request, [
                'username' => 'required|numeric|digits_between:10,15',
                'email' => 'string|email|max:255',
            ]);
        } else {
            $ValidUsername = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
            ]);
        }
        if ($approve['approve'] == '2') {

            $ValidData = $this->validate($this->request, [
                'gender' => 'required|in:0,1',
                'name' => 'required|max:255',
                'family' => 'required|max:255',
                'birthday' => 'nullable',
                'password' => 'required|min:6',
                'privacy_policy' => 'required',
                'nid' => 'nullable|unique:users,nationalcode',
            ]);

        } else {

            $ValidData = $this->validate($this->request, [
                'gender' => 'required|in:0,1',
                'name' => 'required|max:255',
                'family' => 'required|max:255',
                'specialcode' => 'string|unique:users',
                'job_title' => 'string',
                'birthday' => 'nullable',
                'password' => 'required|min:6',
                'privacy_policy' => 'required',
                'nid' => 'nullable|unique:users,nationalcode',
            ]);

        }
        $user = User::where(function ($query) use ($ValidUsername) {
            $query->where('email', $ValidUsername['username'])->orWhere('mobile', $ValidUsername['username']);
        })->orderBy('created_at', 'desc')->first();

        if ($user) {
            if ($approve['type_field'] == 'mobile') {
                $login = ['mobile' => $ValidUsername['username'], 'password' => $ValidData['password'], 'status' => 'active'];
            } else {
                $login = ['email' => $ValidUsername['username'], 'password' => $ValidData['password'], 'status' => 'active'];
            }

            $login = auth()->attempt($login);

            // Check Login User
            if ($login) {

                $access_token = $user->createToken('Api Token On Application Login')->accessToken;

                return success_template(['access_token' => $access_token, 'turn' => [], 'status' => true]);

            } else {

                return error_template('این شماره موبایل و یا ایمیل قبلا در سامانه ثبت شده است. برای ورود به حساب کاربری از بخش ورود اقدام نمایید.');

            }


        }


        $token = str_random(6);
        $user_status = User::where('token', $token)->count();
        if ($user_status) $token = str_random(5);


        $full_data = [
            'approve' => $approve['approve'],
            'gender' => $ValidData['gender'],
            'name' => $ValidData['name'],
            'family' => $ValidData['family'],
            'fullname' => $ValidData['name'] . ' ' . $ValidData['family'],
            'birthday' => $ValidData['birthday'],

            'nationalcode' => $ValidData['nid'],
            'national_insurance' => ($this->request->get('national_insurance')) ? $this->request->get('national_insurance') : null,

            'username' => str_random(10),
            'specialcode' => ($this->request->get('specialcode')) ? $this->request->get('specialcode') : null,
            'job_title' => ($this->request->get('job_title')) ? $this->request->get('job_title') : null,
            'account_number' => ($this->request->get('account_number')) ? $this->request->get('account_number') : null,

            'zone' => '+98',

            'show_phone' => ($this->request->get('phone_copy')) ? '1' : '0',

            'token' => $token,
            'password' => bcrypt($ValidData['password']),
        ];

        if ($approve['type_field'] == 'email') {
            $full_data['email'] = $ValidUsername['username'];
        } else {

            $full_data['email'] = ($this->request->get('email')) ? $this->request->get('email') : null;
            $full_data['mobile'] = $ValidUsername['username'];
        }

        $user = User::create($full_data);

        $access_token = $user->createToken('Api Token On Application Registration')->accessToken;

        //$twilio = $this->twilio();

        return success_template(['access_token' => $access_token, 'turn' => [], 'status' => true]);

    }

    /*   forget password api   */
    public function ForgetPasswordConfirm()
    {
        return true;
        $username = change_number($this->request->get('username'));
        $username_old = str_replace('+', '', $username);
        $this->request['username'] = $username_old;
        $type_field = 'email';

        if (is_numeric($username_old)) {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|digits_between:10,15',
            ]);
            $username = change_phone($username);

            $type_field = 'mobile';

        } else {

            $ValidData = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
            ]);

        }


        $request = UserConfirm::where('status', 'not_verified')->where('created_at', '>=', Carbon::now()->subHour(1))->where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->count();

        if ($request > 10) {
            return error_template('تعداد درخواست شما بیش از حد مجاز می باشد ، لطفا یک ساعت دیگر امتحان کنید.');
        }


        $app_name = config('app.name');
        //$confirmCod = 1111;
        $confirmCod = rand(111111, 999999);
        /*$confirmCodSend = implode("-", str_split($confirmCod, 3));*/

        $requestNew = new UserConfirm();
        $requestNew->mobile = ($type_field == 'mobile') ? $username : null;
        $requestNew->email = ($type_field == 'email') ? $username : null;
        $requestNew->confirm = $confirmCod;
        if ($requestNew->save()) {


            if ($type_field == 'mobile') {
                $origin = (string)\request()->headers->get('origin');
                if ($origin == "https://sbm24.com/"
                    || $origin == "https://sbm24.com"
                    || $origin == "https://cp.sbm24.com/"
                    || $origin == "https://cp.sbm24.com") {
                    if (BlackList::black(\request()->ip())) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' => $confirmCod,
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
                        ->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get()->count();
                    if ($count >= 7) {
                        try {
                            RequestCodesLog::create([
                                'mobile' => $username,
                                'code' => $confirmCod,
                                'ip' => \request()->ip(),
                                'os' => SystemInfo::info(),
                                'host' => $origin,
                                'message' => 'More than 5 attempts'
                            ]);
                        } catch (\Exception $exception) {
                        }
                        return success_template('false');
                    }
                    SendSMS::sendConfirmTemplate($username, $confirmCod);
                    try {
                        RequestCodesLog::create([
                            'mobile' => $username,
                            'code' => $confirmCod,
                            'ip' => \request()->ip(),
                            'os' => SystemInfo::info(),
                            'host' => $origin,
                            'message' => 'Kavenegar'
                        ]);
                    } catch (\Exception $exception) {
                        ;
                    }
                }
            } else {

                $email = $username;
                $subject = 'کد تأیید حساب ' . $app_name . ' : ' . $confirmCod;

                Mail::send('emails.confirmAccount', ['confirm' => $confirmCod], function ($message) use ($subject, $email) {
                    $message->from('noreply@appka.ir', "Appka");
                    $message->to($email)->subject($subject);
                });

            }


            $success = success_template(['confirmation' => 'Send Code', 'access' => $username, 'type' => $type_field, 'confirmCod' => $confirmCod]);


            return $success;

        }

        return error_template('خطا در ثبت اطلاعات، لطفا مجددا تلاش کنید.');

    }

    public function ForgetPassword()
    {

        return true;
        // Validation Data
        $ValidData = $this->validate($this->request, [
            'username' => 'required',
            'type_field' => 'required',
            'password' => 'required|min:6',
        ]);

        $username = change_number($this->request->get('username'));
        if ($ValidData['type_field'] == 'mobile') {
            $ValidUsername = $this->validate($this->request, [
                'username' => 'required|numeric|digits_between:10,15',
                'email' => 'string|email|max:255',
            ]);
            $username = change_phone($username);
        } else {
            $ValidUsername = $this->validate($this->request, [
                'username' => 'required|string|email|max:255',
            ]);
        }

        $user = User::where(function ($query) use ($username) {
            $query->where('email', $username)->orWhere('mobile', $username);
        })->orderBy('created_at', 'desc')->first();

        if (!$user) {
            return error_template('این کاربر قبلا در سامانه ثبت نام نکرده است. لطفا شماره موبایل و یا ایمیل را با دقت وارد کنید.');
        }
        if ($user->status != 'active') {
            return error_template('حساب کاربری شما مسدود شده است.');
        }

        $user->password = bcrypt($ValidData['password']);
        $user->save();

        $token = $user->createToken('Api Token On Forget Password To App')->accessToken;

        add_log_visit($user->id, 'login');

        return success_template(['reset_password' => true, 'access_token' => $token,]);

    }

    /*   tamin api   */
    public function InquiryOnRegistration($nid = '', $birthDate = '')
    {

        if (!$nid || !$birthDate) return false;

        try {
            $client = new \SoapClient('http://188.214.5.231/GetIdentityWS/GetIdentityWS?wsdl');

            $parameters['nid'] = $nid;
            $parameters['birthDate'] = $birthDate;
            $parameters['endUser'] = '0';
            $parameters['pin'] = 'DPIC_pCe5416w42M3';
            $request = $client->findByNid_BirthDate($parameters);
            if ($request) {
                if ($request->return) {
                    if ($request->return->serial) {
                        return true;
                    }
                }
            }
        } catch (SoapFault $fault) {
            return false;
        }

        return false;
    }

    public function InsuranceInquiry($nid = '')
    {

        if (!$nid) return [];

        try {
            $client = new \SoapClient('http://188.214.5.231/services/InquiryWS?wsdl');

            $parameters['nid'] = $nid;
            $request = $client->getHealthStatus($parameters);
            if ($request) {
                if ($request->return) {
                    if (isset($request->return->revokeCode) && $request->return->revokeCode == 'C') {
                        return [];
                    }
                    if (isset($request->return->risuid)) {
                        return $request->return;
                    }
                }
            }
        } catch (SoapFault $fault) {
            return [];
        }

        return [];
    }


    /*public function twilio(){

        $sid    = "AC0390cee992f977d3c7dfe94bb11a288f";
        $token  = "7df059ecc6f616d5ad00e12594a33a2c";
        try {
            $twilio = new Client($sid, $token);


            $token = $twilio->tokens->create();
\

            return $token->iceServers;

        } catch (ConfigurationException $e) {

            return [];

        }

    }*/
    public function start()
    {

        $user = auth()->user();

        $request = [
            'client_id' => $user->id,
            'fullname' => $user->fullname,
            'gender' => ($user->gender == 0) ? 'man' : 'woman',
            'username' => $user->username,
            'picture' => $user->picture,
            'status' => $user->status,
            'birthday' => $user->birthday,
            'created_at' => $user->created_at,
        ];

        if ($user->approve == 2) {

            $request['approve'] = 'user';
            $request['credit'] = $user->credit;
            $request['mdical_history_status'] = ($user->mdical_history_status == 0) ? false : true;

        } elseif ($user->approve == 1) {

            $request['specialcode'] = $user->specialcode;
            $request['job_title'] = $user->job_title;
            $request['nickname'] = $user->doctor_nickname;
            $request['approve'] = 'doctor';
            $request['doctor_status'] = $user->doctor_status;
            $request['doctor_info_status'] = ($user->doctor_info_status == 0) ? false : true;
            $request['info_alert'] = $user->doctor_info_alert;
            $request['dr_visit_status'] = CalendarController::get_status();

        }

        return success_template(['user_login' => $request]);

    }

}
