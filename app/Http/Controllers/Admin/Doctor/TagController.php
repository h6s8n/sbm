<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Repositories\v2\Doctor\DoctorInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    private $doctor;

    public function __construct(DoctorInterface $doctor)
    {
        $this->doctor = $doctor;
    }
    public function index(User  $doctor)
    {
        return view('admin.Tag.edit',compact('doctor'));
    }

    public function update(User $doctor)
    {
        $this->doctor->SyncSearchArea($doctor,\request()->all());
        return redirect()->back()->with(['success'=>'موارد مورد جستجو با موفقیت به روز رسانی شد']);
    }
}
