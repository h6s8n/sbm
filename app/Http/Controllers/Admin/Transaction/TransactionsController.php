<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionsController extends Controller
{
    public function __construct()
    {
        require(base_path('app/jdf.php'));

    }
    public function index(User $user)
    {
        $transactions = null;
        if (\request()->has('filter_name'))
        {
            if (\request()->input('filter_name') == 'reserves')
            {
                $transactions = $user->ReserveTransactions()->get();
                return view('admin.Transactions.users',compact('transactions','user'));
            }elseif (\request()->input('filter_name') == 'credits'){
                $transactions = $user->CreditTransactions()->get();
                return view('admin.Transactions.users',compact('transactions','user'));
            }
        }
        return view('admin.Transactions.users',compact('transactions','user'));
    }
}
