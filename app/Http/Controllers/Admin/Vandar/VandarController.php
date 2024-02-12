<?php

namespace App\Http\Controllers\Admin\Vandar;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Vandar\Laravel\Facade\Vandar;

class VandarController extends Controller
{
    public function index()
    {
        return  view('admin.vandar.pay');
    }

    public function pay()
    {
        $amount =\request()->input('amount');
        $callback = route('admin.vandar.verify');

//        Vandar::request($amount,$callback);
        $result = Vandar::request($amount,$callback,
            $mobile=null, $factorNumber=null,
            $description=null);
        if ($result['status'])
        {
            Vandar::redirect();
        }
    }

    public function verify()
    {
        $token=$_GET['token'];
        $status = $_GET['payment_status'];

        if ($status == 'OK') {
            $result = Vandar::verify($token);
            $factorNumber = $result['factorNumber'];
            $transId = $result['transId'];
        }
        return redirect()->route('admin.vandar.index');
    }
}
