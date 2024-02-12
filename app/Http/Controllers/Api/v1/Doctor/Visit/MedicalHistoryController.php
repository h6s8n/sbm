<?php

namespace App\Http\Controllers\Api\v1\Doctor\Visit;

use App\Model\user\MedicalHistory;
use App\Model\Visit\EventReserves;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MedicalHistoryController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
    }


    public function getUserInfo(){

        $ValidData = $this->validate($this->request,[
            'username' => 'required',
        ]);

        $user = User::where('username', $ValidData['username'])->where('status', 'active')->first();
        if(!$user) return error_template('خطا ، کاربری با این مشخصات یافت نشد.');

        $userOnline = auth()->user();

        $event = EventReserves::where('user_id', $user->id)->where('doctor_id', $userOnline->id)->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، نمی توانید به این اطلاعات دسترسی داشته باشید.');


        $request = MedicalHistory::where('user_id' , $user->id)->orderBy('created_at', 'desc')->first();

        return success_template($request);

    }


}
