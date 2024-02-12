<?php

namespace App\Http\Controllers\Admin\Visit;

use App\Model\Visit\EventReserves;
use App\Model\Visit\Rate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RateController extends Controller
{
    public function __construct()
    {
        require(base_path('app/jdf.php'));
    }

    public function index()
    {
        $rates = EventReserves::whereHas('DoctorRate')
            ->whereHas('UserRate')
            ->where('doctor_id','!=',321)
            ->orderBy('created_at','DESC');
        if (\request()->has('filter_user') && \request()->input('filter_user'))
            $rates = $rates->whereHas('doctor',function ($query){
                $query->where('fullname','LIKE','%'.\request()->input('filter_user').'%');
            });
        $rates=$rates->paginate(10);
        return view('admin.Rate.index',compact('rates'));
    }
}
