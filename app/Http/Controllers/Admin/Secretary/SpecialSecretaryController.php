<?php

namespace App\Http\Controllers\Admin\Secretary;

use App\Secretary\SpecialSecretary;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class SpecialSecretaryController extends Controller
{
    public function create(User $user)
    {

        return view('admin.secretary.index', compact('user'));
    }

    public function store(User $user)
    {

        $data = \request()->all();
        $data['user_id'] = $user->id;
        $data['mobile'] = change_number(\request()->input('mobile'));
        $data['password'] = change_number(\request()->input('password'));
        try {
            $sp = SpecialSecretary::create($data);
            return redirect()->route('doctors.index')
                ->with(['success' => 'منشی با موفقیت ثبت شد']);

        }catch(\Exception $exception){
            return redirect()->route('secretary.create', $user->id)
                ->withErrors('ثبت با مشکل مواجه شده است');

        }
    }

    public function edit(User $user)
    {
        $secretary = $user->secretary()->first();
        return view('admin.secretary.edit',compact('secretary','user'));
    }

    public function update($id)
    {
        $data = \request()->all();
        $data['mobile'] = change_number(\request()->input('mobile'));
        $data['password'] = change_number(\request()->input('password'));
        $data['username'] = change_number(\request()->input('username'));
        try {

            /* @var SpecialSecretary $secretary */
            $secretary = SpecialSecretary::find($id);
            $secretary->fill($data)->save();
            return redirect()->back()->with(['success'=>'ویرایش با موفقیت انجام شد']);
        }catch (\Exception $exception) {
            return redirect()->back()->withErrors('ویرایش انجام نشد');
        }
    }
}
