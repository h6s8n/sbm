<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Model\Doctor\DoctorInformation;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DoctorInformationController extends Controller
{
    public function index()
    {
        $filter_name = trim(\request()->get('filter_name'));
        $filter_mobile = trim(\request()->get('filter_mobile'));
        $filter_email = trim(\request()->get('filter_email'));
        $where_array=[];
        if ($filter_mobile) {
            $where_array[] = array('mobile', "LIKE", "%" . $filter_mobile . "%");
        }
        if ($filter_email) {
            $where_array[] = array('email', "LIKE", "%" . $filter_email . "%");
        }

        $information = DoctorInformation::whereHas('doctor',function ($query) use ($where_array,$filter_name){
            $query->where($where_array)->where('approve', '1')
                ->where(function ($query2) use ($filter_name) {
                    $query2->search($filter_name, false);
                });
        })->paginate(10);
        return view('admin.DoctorInformation.index',compact('information'));
    }

    public function create(User $user)
    {
        $request = $user->information()->first();
        return view('admin.DoctorInformation.create',compact('request','user'));
    }

    public function store(User $user)
    {
//        \request()->validate([
//            'office_secretary_name'=>'required',
//            'office_secretary_mobile'=>'required',
//        ]);
        $information = $user->information()->first();
        $data = \request()->all();
        $data['doctor_id']=$user->id;
        if ($information){
            $information->fill($data)->save();
        }
        DoctorInformation::create($data);
        return redirect()->back()->with(['success'=>'با موفقیت ثبت شد'])->withInput();
    }
}
