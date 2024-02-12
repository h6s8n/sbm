<?php

namespace App\Http\Controllers\Admin\User;

use App\Model\User\SettingType;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserSettingController extends Controller
{
    public function edit(User $user)
    {
        $doctor_settings = SettingType::where('key','NOT LIKE','%_Se')->get();
        $secretary_settings = SettingType::where('key','LIKE','%_Se')->get();
        return view('admin.users.setting',compact('user','doctor_settings','secretary_settings'));
    }

    public function update(User $user): RedirectResponse
    {
        $doctor_settings = \request()->input('setting_doctor');
        $user->settings()->update([
            'subscribed'=>0,
            'last_changed_user_id'=>auth()->id()
        ]);
        if ($doctor_settings)
        foreach ($doctor_settings as $dt){
            $st = $user->settings()
                ->where('setting_type_id',$dt)->first();
            if ($st)
            {
                $st->subscribed=1;
                $st->save();
            }
            else{
                $user->settings()->create([
                    'setting_type_id'=>$dt,
                    'last_changed_user_id'=>auth()->id(),
                    'subscribed'=>1
                ]);
            }
        }
        $secretary_settings = \request()->input('setting_secretary');
        if ($secretary_settings)
        foreach ($secretary_settings as $dt){
            $st = $user->settings()
                ->where('setting_type_id',$dt)->first();
            if ($st)
            {
                $st->subscribed=1;
                $st->save();
            }
            else{
                $user->settings()->create([
                    'setting_type_id'=>$dt,
                    'last_changed_user_id'=>auth()->id(),
                    'subscribed'=>1
                ]);
            }
        }
        return redirect()->back()->with(['success'=>'تغییر با موفقیت انجام شد']);
    }
}
