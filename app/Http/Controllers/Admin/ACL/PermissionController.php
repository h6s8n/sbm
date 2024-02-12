<?php

namespace App\Http\Controllers\Admin\ACL;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        require (base_path('app/jdf.php'));
    }
    public function index()
    {
        $permissions = Permission::paginate(10);
        return view('admin.ACL.permissions',compact('permissions'));
    }

    public function store()
    {
        Permission::create([
            'name'=>\request()->input('name'),
            'display_name'=>\request()->input('display_name'),
            'guard_name'=>'web',
        ]);
        return redirect()->back();
    }
}
