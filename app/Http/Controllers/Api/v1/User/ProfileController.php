<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Model\Platform\City;
use App\Model\Platform\State;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;

class ProfileController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

    }

    public function getInfo()
    {

        $user = auth()->user();
        $day = null;
        $month = null;
        $year = null;
        if ($user->birthday) {
            $date = explode('/', $user->birthday);
            if (count($date)==3) {
                $day = $date[2];
                $month = $date[1];
                $year = $date[0];
            }
        }
        return success_template([
            'profile' => [
                'name' => $user->name,
                'family' => $user->family,
                'job' => $user->job_title,
                'bio' => $user->bio,
                'address' => $user->address,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'city' => $user->city_id,
                'state' => $user->state_id,
                'picture' => $user->picture,
                'birthday' => $user->birthday,
                'day' => $day,
                'month' => $month,
                'year' => [
                    'value' => (int)$year,
                    'label' => (int)$year
                ],
                'nid' => $user->nationalcode,
                'account_sheba' => $user->account_sheba != 'null' ? $user->account_sheba : NULL
            ],
            'states' => State::orderBy('state', 'ASC')->get()
        ]);


    }

    public function getStates()
    {
        $states = State::orderBy('state', 'ASC')->get();

        return success_template($states);

    }

    public function getCity()
    {

        $ValidData = $this->validate($this->request, [
            'state' => 'required',
        ]);

        $data = City::where('state_id', $this->request->get('state'))->orderBy('city', 'ASC')->get();

        return success_template($data);

    }

    public function search()
    {

        $ValidData = $this->validate($this->request, [
            'mobile' => 'required|starts_with:09|digits_between:10,11',
        ], [
            'mobile.required' => 'ورود موبایل الزامی است',
            'mobile.starts_with' => 'شماره همراه نامعتبر',
            'mobile.digits_between' => 'فرمت موبایل نادرست است',
        ]);
        $mobile = $ValidData['mobile'];

        $user = User::whereMobile($mobile)
        ->where(function ($query) {
            $query->where('approve', 2)
                ->orWhere('doctor_status', 'active');
        })
            ->select(
                'id',
                'mobile',
                'name',
                'gender',
                'birthday',
                'family',
                'nationalcode',
            )
            ->first();

        if ($user)
            return success_template($user);
        return success_template(['message'=>'کاربر یافت نشد. برای ثبت کاربر، اطلاعات را تکمیل نمایید.']);
    }

    public function save()
    {
        $user = auth()->user();

        $ValidData = $this->validate($this->request, [
            'name' => 'required|string|max:191',
            'family' => 'required|string|max:191',
            'job' => 'nullable|string|max:191',
//            'bio' => 'nullable|string',
            'old_password' => 'nullable',
            'new_password' => 'nullable',
            'address' => 'string|max:191|nullable',
            'city' => 'required|numeric',
            'state' => 'required|numeric',
            'photo' => 'nullable|image',
            'birthday' => 'nullable',
            'account_sheba' => 'nullable|numeric',
            'nid' => 'required|numeric|digits:10',
        ], [
            'name.required' => 'ورود نام الزامی است',
            'family.required' => 'ورود نام خانوادگی الزامی است',
            'job.required' => 'ورود شغل الزامی است',
            'nid.required' => 'ورود شماره ملی الزامی است',
            'account_sheba.required' => 'ورود شماره شبا الزامی است',
            'city.required'=>'ورود شهر و استان الزامی است'
        ]);

        if (isset($ValidData['photo'])) {
            $ValidDataImage = $this->validate($this->request, [
                'photo' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $image = $this->uploadAvatar('photo');

            $user->picture = $image;

        }


        if (isset($ValidData['new_password'])) {

            $ValidDataPass = $this->validate($this->request, [
//                'old_password' => 'required',
                'new_password' => 'required|min:6',
            ]);


//            $check = Hash::check($ValidData['old_password'], $user->password);

            $password = Hash::make($ValidData['new_password']);

//            if (!$check) {
//                return error_template('رمز عبور صحیح نمی باشد.');
//            }

            $user->password = $password;

        }


        $user->fullname = $ValidData['name'] . ' ' . $ValidData['family'];
        $user->name = $ValidData['name'];
        $user->family = $ValidData['family'];
        $user->job_title = $ValidData['job'];
        $user->birthday = $ValidData['birthday'];
        $user->bio = $this->request->get('bio',null);
        $user->city_id = ($ValidData['state'] && $ValidData['state'] != 'undefined' && $ValidData['city'] && $ValidData['city'] != 'undefined') ? $ValidData['city'] : 342;
        $user->state_id = ($ValidData['state'] && $ValidData['state'] != 'undefined') ? $ValidData['state'] : 33;
        $user->address = ($ValidData['address'] && $ValidData['address'] != 'null') ? $ValidData['address'] : '';
        $user->nationalcode = $ValidData['nid'];
        $user->account_sheba = $this->request->has('account_sheba') && $ValidData['account_sheba'] ? $ValidData['account_sheba'] : null;

        if ($user->save()) {

            return success_template(['request' => 'update']);

        }

        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');

    }

    public function saveAvatar()
    {
        $user = auth()->user();


        $ValidData = $this->validate($this->request, [
            'photo' => 'required|image|max:1024',

        ], [
            'photo.required' => ' تصویر الزامی است',
            'photo.max' => 'حجم فایل نباید بیشتر از 1 مگابایت باشد.',
        ]);

        if (isset($ValidData['photo'])) {
            $ValidDataImage = $this->validate($this->request, [
                'photo' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $image = $this->uploadAvatar('photo');

            $user->picture = $image;

        }

        if ($user->save()) {

            return success_template($user->picture);

        }

        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');
    }

}
