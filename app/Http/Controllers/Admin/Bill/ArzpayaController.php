<?php

namespace App\Http\Controllers\Admin\Bill;

use App\Model\Arzpaya\ArzpayaTransaction;
use App\Model\Partners\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArzpayaController extends Controller
{
    public function index()
    {
        $invoices = ArzpayaTransaction::paginate(10);
        $partners =Partner::all();
        return view('admin.bill.arzpaya',compact('invoices','partners'));
    }
}
