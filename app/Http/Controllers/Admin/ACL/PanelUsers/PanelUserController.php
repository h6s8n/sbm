<?php

namespace App\Http\Controllers\Admin\ACL\PanelUsers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PanelUserController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function index()
    {
        $request = User::where('approve',10)->get();
        return view('admin.PanelUsers.index',compact('request'));
    }
    public function create()
    {
        return view('admin.PanelUsers.create');
    }

    public function store()
    {
        $data = \request()->all();
        $data['approve']=10;
        $data['fullname']=$data['name'].' '.$data['family'];
        $data['password']=Hash::make($data['password']);
        $data['token']=str_random(8);
        User::create($data);
        return redirect()->back()->with(['success'=>'کاربر با موفقیت ثبت شد']);
    }

    public function roles(User $user)
    {
        Artisan::all('cache:clear');
        $roles = Role::all();
        return view('admin.PanelUsers.roles',
            compact('roles','user'));
    }

    public function AssignRoles(User $user,Request $request)
    {
        if ($user->syncRoles($request->role))
            return redirect()->back()
                ->with(['success' => 'نقشهای کاربر با موفقیت بروز شد']);
        return redirect()->back()
            ->withErrors(['error' => 'درخواست انجام نشد. مجددا تلاش کنید']);

    }
}
