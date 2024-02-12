<?php

namespace App\Http\Controllers\Api\v2;

use App\Model\Triage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TriageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(
            ['mobile'=>'required|digits:11'],
            ['mobile.required'=>'ورود شماره همراه الزامی است',
                'mobile.digits'=>'شماره همراه را صحیح و با حروف لاتین وارد کنید']
        );
        if (Triage::where('mobile',$request->input('mobile'))->where('called',false)->first())
            return error_template('شما قبلا درخواست خود را ثبت کرده اید');
        $triage = Triage::create($request->all());
        if ($triage)
            return success_template($triage);
        return error_template();
    }
}
