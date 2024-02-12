<?php

namespace App\Http\Controllers\Api\v2\User\profile\User;

use App\Repositories\v2\Profile\Doctor\ProfileInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    private $profile;

    public function __construct(ProfileInterface $profile)
    {
        $this->profile = $profile;
    }

    public function store(Request $request)
    {
        $request->validate([
            'birthday'=>'required',
            'mobile'=>'required|starts_with:09|digits_between:10,11|unique:users,mobile',
            'name'=>'required',
            'family'=>'required',
            'nationalcode' => 'required|digits:10',
            'gender' => 'required'
        ],[
            'birthday.required'=>'ورود تاریخ تولد الزامی است',
            'mobile.required'=>'ورود موبایل الزامی است',
            'nationalcode.required'=>'ورود کد ملی الزامی است',
            'nationalcode.digits'=>' کد ملی نامعتبر است',
            'mobile.starts_with'=>'موبایل نامعتبر است',
            'gender.required'=>'ورود جنسیت الزامی است',
            'name.required'=>'ورود نام الزامی است',
            'mobile.unique'=>'موبایل تکراری است',
            'family.required'=>'ورود نام خانوادگی الزامی است',
        ]);



        $data['fullname'] = $request->input('name').' '. $request->input('family');
        $data['name'] = $request->input('name');
        $data['family'] = $request->input('family');
        $data['nationalcode'] = $request->input('nationalcode');
        $data['gender'] = $request->input('gender');
        $data['birthday'] = $request->input('birthday');
        $data['mobile'] = $request->input('mobile');
        $data['token'] = str_random(6);
        $data['username'] = str_random(6);
        $data['password'] = str_random(6);
        $data['approve'] =  2;

        if ($request->input('user_id')){
            $user = User::find($request->input('user_id'));
            if ($user){

                $data['user_id'] = $request->input('user_id');

                $response = $this->profile->update($data);

                if ($response->status){
                    return success_template($response->object);
                }
                return error_template($response->message);
            }
            return error_template('کاربر یافت نشد.');
        }

        $user = User::create($data);

        return success_template($user);
    }

    public function update(Request $request)
    {
        /* Validate Request*/
        $request->validate([
            'birthday'=>'required',
            'name'=>'required',
            'family'=>'required',
            'nationalcode'=>'required',
        ],[
            'birthday.required'=>'ورود تاریخ تولد الزامی است',
            'name.required'=>'ورود نام الزامی است',
            'nationalcode.required'=>'ورود کد ملی الزامی است',
            'family.required'=>'ورود نام خانوادگی الزامی است',
        ]);
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['fullname'] = $request->input('name').' '. $request->input('family');
        $response = $this->profile->update($data);
        if ($response->status){
            return success_template($response->object);
        }
        return error_template($response->message);
    }
}
