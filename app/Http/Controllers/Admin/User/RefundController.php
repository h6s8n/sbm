<?php

namespace App\Http\Controllers\Admin\User;

use App\Model\User\Refund;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RefundController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function index()
    {
        $refunds = Refund::where('flag',0)->paginate(10);
        return view('admin.users.refund',compact('refunds'));
    }
}
