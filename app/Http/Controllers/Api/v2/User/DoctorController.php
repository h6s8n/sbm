<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Model\Badge\Badge;
use App\Model\Badge\BadgeRequest;
use App\Repositories\v2\Doctor\DoctorInterface;
use App\Repositories\v2\File\FileInterface;
use App\Http\Controllers\Api\v2\vandar\AdvertisingController;
use App\User;
use App\Services\Gateways\src\Zibal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    private $doctor;
    private $file;

    public function __construct(DoctorInterface $doctor,
                                FileInterface $file)
    {
        $this->doctor = $doctor;
        $this->file = $file;
    }

    /**
     * Update a Doctor
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        /*  Validate Request */
        $ValidData = $this->validate($this->request, [
            'mobile' => 'required|digits:11',
            'fullname' => 'required',
            'specialization_id' => 'required',
            'passport_image' => 'required|mimes:jpg,jpeg,bmp,png',
            'special_cart_image' => 'required|mimes:jpg,jpeg,bmp,png',
            'education_image' => 'required|mimes:jpg,jpeg,bmp,png',
        ],
            [
                'mobile.required' => 'ورود شماره همراه الزامی است',
                'fullname.required' => 'ورود نام و نام خانوادگی الزامی است',
                'specialization_id.required' => 'انتخاب تخصص الزامی است',
                'national_cart_image.required' => 'بارگذاری تصویر کارت ملی الزامی است',
                'special_cart_image.required' => 'بارگذاری تصویر کارت نظام پزشکی الزامی است',
                'education_cart_image.required' => 'بارگذاری تصویر پروانه مطب یا مدرک تحصیلی الزامی است',
                'national_cart_image.mimes'=>'لطفا کارت ملی را با یکی از فرمت های jpg، bmpُ، یا png آپلود کنید',
                'special_cart_image.mimes'=>'لطفا کارت نظام پزشکی را با یکی از فرمت های jpg، bmpُ، یا png آپلود کنید',
                'education_cart_image.mimes'=>'لطفا پروانه مطب یا مدرک تحصیلی را با یکی از فرمت های jpg، bmpُ، یا png آپلود کنید'
            ]
        );
        $request = $request->all();
        $user = auth()->user();
        $request['user_id'] = $user->id;
        $request['approve'] = 1;

//        /* Upload First Files */
//        $response = $this->file->upload(\request()->file('national_cart_image'));
//        if ($response->status && $response->object)
//            $request['national_cart_image'] = $response->object;
//        else
//            return error_template('مشکلی در آپلود فایل بوجود آمده است');

        /* Upload Second Files */
        $response = $this->file->upload(\request()->file('special_cart_image'));
        if ($response->status && $response->object)
            $request['special_cart_image'] = $response->object;
        else
            return error_template('مشکلی در آپلود فایل بوجود آمده است');

        /* Upload Third Files */
        $response = $this->file->upload(\request()->file('education_image'));
        if ($response->status && $response->object)
            $request['education_image'] = $response->object;
        else
            return error_template('مشکلی در آپلود فایل بوجود آمده است');

        /* Upload Fourth Files */
        $response = $this->file->upload(\request()->file('passport_image'));
        if ($response->status && $response->object)
            $request['passport_image'] = $response->object;
        else
            return error_template('مشکلی در آپلود فایل بوجود آمده است');
        $response = $this->doctor->update($request);
        if ($response->status)
            return success_template($response->object);
        return error_template($response->message);
    }

    public function getPartners()
    {
        $doctor_id = \request()->get('doctor_id' , null);
        $doctor =  User::with('partners')->find($doctor_id);

        if ($doctor && count($doctor->partners) > 0)
            return success_template($doctor->partners);
        else
            return success_template([]);


    }

    public function badgeRequest(Request $request)
    {
        $ValidData = $this->validate($request,[
            'full_name' => 'required|string',
            'phone' => 'required|digits_between:10,11',
            'plan' => 'required|in:Bronze,Silver,Gold',
        ], [
            'full_name.required' => 'نام درخواست کننده الزامی است.',
            'phone.required' => 'شماره تماس الزامی است.',
            'plan.required' => 'طرح حامی الزامی است.',
            'phone.digits_between' => 'شماره تماس نامعتبر.',
            'plan.in' => 'طرح نامعتبر.',
        ]);


        try {

            $user = User::whereMobile($request->phone)->first();
            $badge = Badge::wherePlan($request->plan)->first();
            $request->merge([
                'user_id' => optional($user)->id,
                'badge_id' => $badge->id,
                'token' => Str::random(15).time(),
            ]);

            if ($badgeRequest = BadgeRequest::create($request->all())) {

                $amount = $badge->priority;

                $data['amount'] = $request->phone == '09125501910' && $badge->id === 4 ? 10000000 : $amount * 10000;
                $data['callback'] = route('vandar.advertising.verify');
                $data['mobile'] = $request->phone;
                $data['factorNumber'] = $badgeRequest->token;
                $data['$description'] = 'درخواست نشان با طرح: ' .$badge->plan .' توسط: ' . $request->phone ;

                $payment = new AdvertisingController();
                return $payment->pay($data);
            }

        } catch (Exception $ex) {
            $response = [
                'status' => false,
                'message' => $ex->getMessage()
            ];
        }

    }
}
