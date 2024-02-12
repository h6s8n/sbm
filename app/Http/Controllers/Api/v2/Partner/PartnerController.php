<?php

namespace App\Http\Controllers\Api\v2\Partner;

use App\Model\Partners\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartnerController extends Controller
{
    public function index($id = null)
    {
        if (!$id)
            $partners = Partner::orderBy('id','ASC')->where('is_active',1)->get();
        else {
            $partners = Partner::where('slug', $id)->where('is_active',1)
                ->with(['services', 'insurances', 'doctors' => function ($query) {
                $query->with(['specializations','NearestTime'])
                    ->select('users.id','name', 'family', 'doctor_nickname', 'fullname', 'picture', 'sp_gp', 'username','job_title');
            }])->orderBy('id','ASC')->get();
        }
        return success_template($partners);
    }

}
