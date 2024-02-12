<?php

namespace App\Http\Controllers\PartnerAdmin;

use App\Model\Partners\Partner;
use App\Model\Platform\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }


    public function home(){

        if(!auth()->user()){
            return redirect("/cp-partner/login");
        }

        return redirect("/cp-partner/dashboard");

    }

    public function login(){

        if(auth()->user()){
            return redirect("/cp-partner/dashboard");
        }

        return view('admin/login');
    }

    public function ActionLogin(){

        if(auth()->user()){
            return redirect("/cp-partner/dashboard");
        }

        $ValidData = $this->validate($this->request,[
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);


        if(!auth()->attempt(['email' => $ValidData['email'], 'password' => $ValidData['password'] , 'status' => 'active' , 'approve' => '8'])) {
            return redirect("/cp-partner/login")->with('error' , 'نام کاربری و یا رمز عبور اشتباه است.');
        }

        return redirect("/cp-partner/dashboard");

    }

    public function logout(){

        auth()->logout();

        return redirect("/cp-partner/login");

    }

    public function dashboard(){

        return view('partnerPanel/index');

    }

    public function doctors($id = null)
    {
        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)
            ->with(['doctors' => function ($query) {
                $query->select('users.id','name','doctor_nickname', 'family', 'fullname', 'email', 'mobile', 'users.created_at','job_title');
            }])->orderBy('id','ASC')->first();


        return view('partnerPanel.doctors.doctors', ['partner' => $partner]);
    }

}
