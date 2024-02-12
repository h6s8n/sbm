<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Model\Partners\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }

    public function create()
    {
        $services = Service::all();
        return view('admin.service.create',compact('services'));
    }

    public function store()
    {
        Service::create(\request()->all());
        return redirect()->back()->with(['success'=>'با موفقیت ثبت شد']);

    }
}
