<?php

namespace App\Http\Controllers\Admin;

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
            return redirect("/cp-manager/login");
        }

        return redirect("/cp-manager/dashboard");

    }

    public function login(){

        if(auth()->user()){
            return redirect("/cp-manager/dashboard");
        }

        return view('admin/login');
    }

    public function ActionLogin(){

        if(auth()->user()){
            return redirect("/cp-manager/dashboard");
        }

        $ValidData = $this->validate($this->request,[
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);


        if(!auth()->attempt(['email' => $ValidData['email'], 'password' => $ValidData['password'] , 'status' => 'active' , 'approve' => '10'])) {
            return redirect("/cp-manager/login")->with('error' , 'نام کاربری و یا رمز عبور اشتباه است.');
        }

        return redirect("/cp-manager/dashboard");

    }

    public function logout(){

        auth()->logout();

        return redirect("/cp-manager/login");

    }

    public function dashboard(){

        return view('admin/index');

    }

    public function getCity(){

        $ValidData = $this->validate($this->request,[
            'state' => 'required',
        ]);

        $data = City::where('state_id', $this->request->get('state'))->orderBy('city', 'ASC')->get();

        return success_template($data);

    }

    public function setting(){

        $system_profits_type = $this->get_optien('system_profits_type');
        $system_profits = $this->get_optien('system_profits');

        return view('admin/setting/edit', ['system_profits_type' => $system_profits_type , 'system_profits' => $system_profits]);

    }

    public function ActionSetting(){

        // Validation Data
        $ValidData = $this->validate($this->request,[
            'email' => 'required|string|email|max:255',
            'phone' => 'required',
            'facebook' => 'required',
            'instagram' => 'required',
            'linkedin' => 'required',
            'title_about' => 'required',
            'text_about' => 'required',
            'title_baner_order' => 'required',
            'text_baner_order' => 'required',
        ]);


        SettingController::update_package_optien('title_about', $ValidData['title_about']);
        SettingController::update_package_optien('text_about', $ValidData['text_about']);
        SettingController::update_package_optien('title_baner_order', $ValidData['title_baner_order']);
        SettingController::update_package_optien('text_baner_order', $ValidData['text_baner_order']);
        SettingController::update_package_optien('email', $ValidData['email']);
        SettingController::update_package_optien('phone', $ValidData['phone']);
        SettingController::update_package_optien('facebook', $ValidData['facebook']);
        SettingController::update_package_optien('instagram', $ValidData['instagram']);
        SettingController::update_package_optien('linkedin', $ValidData['linkedin']);



        return redirect('cp-manager/setting')->with('success' ,  'اطلاعات تنظیمات بروز رسانی شد.')->withInput();

    }

}
