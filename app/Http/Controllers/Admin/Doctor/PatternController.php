<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Model\Doctor\CalendarPattern;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PatternController extends Controller
{
    public function index()
    {
        $doctor = null;
        if (\request()->has('doctor') && \request()->input('doctor')) {
            $doctor = \request()->input('doctor');
        }
        if ($doctor)
            $patterns = CalendarPattern::where('user_id', $doctor)->get();
        else
            $patterns = null;
        $users = User::where('approve',1)->whereHas('pattern')->get();
        return view('admin.calenders.pattern',compact('patterns','users'));
    }

    public function extend()
    {

    }
}
