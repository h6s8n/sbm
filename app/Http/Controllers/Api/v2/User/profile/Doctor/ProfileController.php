<?php

namespace App\Http\Controllers\Api\v2\User\profile\Doctor;

use App\Repositories\v2\Profile\Doctor\ProfileInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    private $profile;

    public function __construct(ProfileInterface $profile)
    {
        $this->profile = $profile;
    }

    public function SinglePage($value)
    {
        $response = $this->profile->get($value,
            'username',['specializations','details','FAQs','NearestTime']);
        if ($response->status) {
            $user = $response->object;
            return success_template($user);
        }
        return error_template($response->message);

    }

    public function update(Request $request)
    {
        /* Validate Request*/
        $request->validate([
            'birthday'=>'nullable',
            'name'=>'required',
            'family'=>'required',
            'account_sheba'=>'nullable',
            'job_title'=>'required',
            'specialcode'=>'required',
        ],[
//            'birthday.required'=>'ورود تاریخ تولد الزامی است',
            'name.required'=>'ورود نام الزامی است',
            'family.required'=>'ورود نام خانوادگی الزامی است',
//            'account_sheba.required'=>'ورود شماره شبا الزامی است',
            'job_title.required'=>'ورود عنوان مطب الزامی است',
            'specialcode.required'=>'ورود کد نظام پزشکی الزامی است',
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
