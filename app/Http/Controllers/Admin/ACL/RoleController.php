<?php

namespace App\Http\Controllers\Admin\ACL;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function index()
    {
        $roles = Role::paginate(10);
        return view('admin.ACL.roles',compact('roles'));
    }

    public function store()
    {
        Role::create([
            'name'=>\request()->input('name'),
            'display_name'=>\request()->input('display_name'),
            'guard_name'=>'web',
        ]);
        return redirect()->back();
    }

    public function permissions(Role $role)
    {
        Artisan::all('cache:clear');
        $permissions = Permission::all();
        return view('admin.ACL.RolePermissions',compact('role','permissions'));
    }

    public function AssignPermissions(Role $role,Request $request)
    {
        $request->validate([
            'permission_name' => 'required'
        ]);
        $role->syncPermissions();
        foreach ($request->input('permission_name') as $permission_name) {
            $role->givePermissionTo($permission_name);
        }
        return redirect()->back()
            ->with(['success' => 'دسترسی های انتخابی با موفقیت ثبت شدند']);
    }
}
