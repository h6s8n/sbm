<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class IsPartner
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (Auth::user() &&  Auth::user()->approve == "8" &&  Auth::user()->status == "active") {
            return $next($request);
        }

        if(!Auth::check()){
            return redirect("/cp-partner/login")->with('error' , 'جهت مشاهده این بخش وارد حساب کاربری خود شوید.');
        }

        return App::abort(404);
    }
}
