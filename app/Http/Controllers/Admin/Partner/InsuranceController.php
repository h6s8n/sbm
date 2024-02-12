<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Model\Partners\Insurance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InsuranceController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function create()
    {
        $insurances = Insurance::all();
        return view('admin.insurance.create',compact('insurances'));
    }

    public function store()
    {
        Insurance::create(\request()->all());
        return redirect()->back()->with(['success'=>'با موفقیت ثبت شد']);
    }
}
