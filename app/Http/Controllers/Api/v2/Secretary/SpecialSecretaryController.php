<?php

namespace App\Http\Controllers\Api\v2\Secretary;

use App\Secretary\SpecialSecretary;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SpecialSecretaryController extends Controller
{

    public function authentication()
    {
        $sp = SpecialSecretary::where('username',\request()->input('username'))->first();
        if ($sp){
            $password = change_number(\request()->input('password'));
            if ($password == $sp->password)
            {
                $user = \App\User::where('mobile',$sp->username)->first();
                Auth::login($user,false);
                $token = auth()->user()->createToken('Api Token On Login To App')->accessToken;
                return success_template(['access_token' =>$token,'status'=>true]);
            }
            return error_template('پسورد اشتباه است');
        }
        return error_template('نام کاربری صحیح نیست');
    }
}
