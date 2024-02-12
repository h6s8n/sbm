<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Model\Doctor\DoctorInformation;
use App\Model\Visit\DoctorCalender;
use Illuminate\Http\Request;
use App\Model\Doctor\DoctorOffice;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DoctorOfficeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $offices = DoctorOffice::with(['city:id,city,state_id','state:id,state'])->where('doctor_id' , $user->id)->get();

        return success_template($offices);
    }

    public function list()
    {
        $user = auth()->user();

        $offices = DoctorOffice::select('id','title')->where('doctor_id' , $user->id)->get();

        return success_template($offices);
    }

    public function store(Request $request)
    {

        $request->validate([
            'address'=>'required',
            'title'=>'required',
            'phones'=>'required',
            'description'=>'nullable',
            'latitude'=>'required',
            'longitude'=>'required',
            'state_id'=>'required',
            'city_id'=>'required',
            'secretaries'=>'nullable',
        ],[
            'address.required'=>'ورود آدرس مطب الزامی است',
            'title.required'=>'ورود عنوان الزامی است',
            'phones.required'=>'ورود شماره مطب الزامی است',
            'latitude.required'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'longitude.required'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'state_id.required'=>'ورود استان مطب الزامی است',
            'city_id.required'=>'ورود شهر مطب الزامی است',
        ]);

        $office = new DoctorOffice();
        $office->address = $request->address;
        $office->title = $request->title;
        $office->phones = json_encode($request->phones);
        $office->description = $request->description;
        $office->latitude = $request->latitude;
        $office->longitude =  $request->longitude;
        $office->state_id = $request->state_id;
        $office->city_id = $request->city_id;
        $office->doctor_id = auth()->id();

        if (($request->secretaries) > 0) {
            foreach (json_decode(json_encode($request->secretaries)) as $item) {
                if(change_number($item->mobile) == auth()->user()->mobile){
                    return error_template('شماره منشی نمی تواند با شماره پزشک برابر باشد');
                }
                DoctorInformation::create(
                    [
                        'doctor_id' => $office->doctor_id,
                        'office_id' => $office->id,
                        'office_secretary_mobile' => change_number($item->mobile),
                        'office_secretary_name' => $item->name . ' ' . $item->family
                    ]
                );
            }
            $office->secretaries = $request->secretaries == null ? '[]' : json_encode($request->secretaries);
        }

        $office->save();


        return success_template($office);
    }

    public function show($office)
    {
        $office = DoctorOffice::with(['city:id,city,state_id','state:id,state'])->find($office);
        return success_template($office);
    }

    public function update($id,Request $request)
    {
        $request->validate([
            'address'=>'required',
            'title'=>'required',
            'phones'=>'required',
            'description'=>'nullable',
            'latitude'=>'required|min:1',
            'longitude'=>'required|min:1',
            'state_id'=>'required',
            'city_id'=>'required',
            'secretaries'=>'nullable',
        ],[
            'address.required'=>'ورود آدرس مطب الزامی است',
            'title.required'=>'ورود عنوان الزامی است',
            'phones.required'=>'ورود شماره مطب الزامی است',
            'latitude.required'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'latitude.min'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'longitude.required'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'longitude.min'=>'ورود مختصات جغرافیایی مطب الزامی است',
            'state_id.required'=>'ورود استان مطب الزامی است',
            'city_id.required'=>'ورود شهر مطب الزامی است',
        ]);

        $doctorOffice  = DoctorOffice::find($id);
        $doctorOffice->address = $request->address;
        $doctorOffice->title = $request->title;
        $doctorOffice->phones = json_encode($request->phones);
        $doctorOffice->description = $request->description;
        $doctorOffice->latitude = $request->latitude;
        $doctorOffice->longitude =  $request->longitude;
        $doctorOffice->state_id = $request->state_id;
        $doctorOffice->city_id = $request->city_id;

        try {
            DB::beginTransaction();
            DoctorInformation::where('office_id',$doctorOffice->id)->delete();

            if (($request->secretaries) > 0) {

                foreach (json_decode(json_encode($request->secretaries)) as $item) {

                    if(change_number($item->mobile) == auth()->user()->mobile){
                        return error_template('شماره منشی نمی تواند همانند شماره پزشک باشد');
                    }
                    DoctorInformation::create(
                        [
                            'office_secretary_mobile' => $item->mobile,
                            'office_secretary_name' => $item->name . ' ' . $item->family,
                            'doctor_id' => $doctorOffice->doctor_id,
                            'office_id' => $doctorOffice->id
                        ]
                    );
                }
            }
            DB::commit();
        }
        catch(\Exception $exception){
            DB::rollBack();
        }

        $doctorOffice->secretaries = $request->secretaries == null ? '[]' : json_encode($request->secretaries);
        $doctorOffice->save();

        return success_template($doctorOffice);
    }

    public function destroy($id)
    {
        $doctorOffice = DoctorOffice::findOrFail($id);

        $calendar = DoctorCalender::where('office_id',$id)->first();

        if ($calendar){
            return error_template('برای این مطب برنامه کاری تنظیم شده است');
        }

        DoctorInformation::where('office_id',$doctorOffice->id)->delete();

        $doctorOffice->delete();

        return success_template(['message' => 'مطب با موفقیت حذف شد']);
    }


}
