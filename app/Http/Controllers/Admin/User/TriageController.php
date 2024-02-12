<?php

namespace App\Http\Controllers\Admin\User;

use App\Model\Triage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TriageController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function index()
    {
        $called = \request()->input('called');
        $triages = Triage::all();
        if (\request()->has('called') && \request()->input('called') >=0)
            $triages = $triages->where('called',$called);
        return view('admin.Triage.index',compact('triages'));
    }

    public function edit($id)
    {
        $triage = Triage::find($id);
        return view('admin.Triage.edit',compact('triage'));
    }

    public function update(Triage $triage)
    {
        $data = \request()->all();
        $data['called']= true;
        $triage->fill($data)->save();
        return redirect()->route('triage.index')->with(['success'=>'توضیحات با موفقیت ثبت شد']);

    }
}
