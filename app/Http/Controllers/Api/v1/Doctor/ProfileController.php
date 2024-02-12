<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Model\Doctor\DoctorInformation;
use App\Model\Platform\State;
use App\Model\User\Skills;
use App\Model\User\Specialties;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

    }

    public function GetDoctorInfo(){

        $user = auth()->user();

        $skills = Skills::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();
        $specialties = Specialties::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();



        $request = [
            'passport_image' => $user->passport_image,
            'national_cart_image' => $user->national_cart_image,
            'special_cart_image' => $user->special_cart_image,
            'education_image' => $user->education_image,
            'longitude' => $user->longitude,
            'latitude' => $user->latitude,
            'address' => $user->address,
            'special' => $user->special_json,
            'skill' => $user->skill_json,
            'doctor_info_status' => ($user->doctor_info_status) ? true : false,
            'doctor_status' => ($user->doctor_status == 'active') ? true : false,
            'skills_option' => $skills,
            'specialties_option' => $specialties,
        ];

        return success_template($request);

    }

    public function DoctorInfoSave(){

        $ValidData = $this->validate($this->request,[
            'passport_image' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'national_cart_image' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'special_cart_image' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'education_image' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'special' => 'nullable',
            'skill' => 'nullable',
        ], [
            'passport_image.mimes' => 'نوع فایل ها باید pdf,jpg,jpeg یا png باشد.',
            'passport_image.max' => 'حجم هر فایل نباید بیشتر از ۲ مگابایت باشد.',
            'national_cart_image.max' => 'حجم هر فایل نباید بیشتر از ۲ مگابایت باشد.',
            'national_cart_image.mimes' => 'نوع فایل ها باید pdf,jpg,jpeg یا png باشد.',
            'special_cart_image.max' => 'حجم هر فایل نباید بیشتر از ۲ مگابایت باشد.',
            'special_cart_image.mimes' => 'نوع فایل ها باید pdf,jpg,jpeg یا png باشد.',
            'education_image.max' => 'حجم هر فایل نباید بیشتر از ۲ مگابایت باشد.',
            'education_image.mimes' => 'نوع فایل ها باید pdf,jpg,jpeg یا png باشد.',
        ]);



        $user = auth()->user();



        if(isset($ValidData['passport_image']) && $ValidData['passport_image'] !== 'undefined'){

            $passport_image = $this->uploadImageCt('passport_image' , 'images');

            $user->passport_image = $passport_image;
        }
        if(isset($ValidData['national_cart_image']) && $ValidData['national_cart_image'] !== 'undefined'){
            $national_cart_image = $this->uploadImageCt('national_cart_image','images');
            $user->national_cart_image = $national_cart_image;
        }

        if(isset($ValidData['special_cart_image']) && $ValidData['special_cart_image'] !== 'undefined'){
            $special_cart_image = $this->uploadImageCt('special_cart_image' , 'images');
            $user->special_cart_image = $special_cart_image;
        }

        if(isset($ValidData['education_image']) && $ValidData['education_image'] !== 'undefined'){

            $education_image = $this->uploadImageCt('education_image' , 'images');
            $user->education_image = $education_image;
        }


        $user->special_json = $ValidData['special'];
        $user->skill_json = $ValidData['skill'];
        $user->doctor_info_status = 1;

        if($user->save()){

            return success_template(['request_id' => $user->id]);

        }


        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');

    }

    public function getAddress()
    {
        $user = auth()->user();

        $data = [
            'address' => $user->address,
            'longitude' => $user->longitude,
            'latitude' => $user->latitude,
            'phone' => $user->phone,
            'city' => $user->city,
            'state' => $user->state
        ];

        return success_template($data);
    }

    public function setAddress()
    {
        $ValidData = $this->validate($this->request,[
            'address' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'phone' => 'required',
            'city_id' => 'required',
            'state_id' => 'required',
        ], [
            'latitude.required' => 'عرض جغرافیایی الزامی است',
            'longitude.required' => 'طول جغرافیایی الزامی است',
            'address.required' => 'آدرس الزامی است',
            'city_id.required' => 'شهر الزامی است',
            'phone.required' => 'تلفن مطب الزامی است',
            'state_id.required' => 'استان الزامی است'
        ]);

        $user = auth()->user();

        $user->address = $ValidData['address'];
        $user->longitude = $ValidData['longitude'];
        $user->latitude = $ValidData['latitude'];
        $user->state_id = $ValidData['state_id'];
        $user->phone = $ValidData['phone'];
        $user->city_id = $ValidData['city_id'];

        $user->save();

        return success_template(['message' => 'آدرس با موفقیت ثبت شد']);
    }

    public function getInfo(){

        $user = auth()->user();

        $get_rand = rand(1111111, 9999999);

        $embed = '<div id="sbm'.$get_rand.'"><script type="text/JavaScript" src="'.url('get-embed/'. $user->username . '/sbm'.$get_rand.'?responsive=yes').'"></script></div>';
        $conditions  = json_decode($user->visit_condition,true);
        $conditions['my_patient_only']=filter_var($conditions['my_patient_only'], FILTER_VALIDATE_BOOLEAN);


//        return success_template($conditions);
        $conditions['consultation_type']['videoConsultation']=filter_var($conditions['consultation_type']['videoConsultation'], FILTER_VALIDATE_BOOLEAN);
        $conditions['consultation_type']['voiceConsultation']=filter_var($conditions['consultation_type']['voiceConsultation'], FILTER_VALIDATE_BOOLEAN);
        $conditions['consultation_type']['textConsultation']=filter_var($conditions['consultation_type']['textConsultation'], FILTER_VALIDATE_BOOLEAN);

        $secretaries = $user->secretaries;


        return success_template([
            'profile' => [
                'name' => $user->name,
                'family' => $user->family,
                'job' => $user->job_title,
                'bio' => strip_tags($user->bio),
                'nid'=>$user->nationalcode,
                'username'=>$user->username,
                'en_url'=>$user->en_url ?? $user->username,
                'address' => $user->address,
                'longitude' => $user->longitude,
                'latitude' => $user->latitude,
                'secretaries' => $user->secretaries,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'city' => $user->city_id,
                'state' => $user->state_id,
                'picture' => $user->picture,
                'specialcode' => $user->specialcode,
                'account_number' => $user->account_number,
                'account_sheba' => change_number($user->account_sheba),
                'visit_condition'=>$conditions
            ],
            'embed' => $embed,
            'states' => State::orderBy('state', 'ASC')->get()
        ]);


    }

    public function save(){
        $user = auth()->user();
        $ValidData = $this->validate($this->request,[
            'name' => 'sometimes|required|string|max:191',
            'family' => 'sometimes|required|string|max:191',
//            'job' => 'sometimes|required|string|max:191',
            'bio' => 'nullable|string',
            'specialcode' => 'sometimes|required',
            'account_sheba' => 'nullable|numeric|digits:24',
            'account_number' => 'nullable|numeric',
            'old_password' => 'nullable',
            'new_password' => 'nullable',
            'address' => 'string|max:191|nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'photo' => 'nullable',
            'nid'=>'sometimes|required|numeric|digits:10',
        ]);

        if($this->request->file('photo')){
            $ValidDataImage = $this->validate($this->request,[
                'photo' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $image = $this->uploadAvatar('photo');

            $user->picture = $image;

        }


        if($this->request->get('new_password')){
//
            $ValidDataPass = $this->validate($this->request,[
//                'old_password' => 'required',
                'new_password' => 'required|min:6',
            ]);
//
//
//            $check = Hash::check($ValidData['old_password'], $user->password);
//
            $password = Hash::make($this->request->get('new_password'));
//
//            if( !$check ){
//                return error_template('رمز عبور صحیح نمی باشد.');
//            }
//
            $user->password = $password;
//
        }


        if ($this->request->has('name')) {
            $user->fullname = $this->request->get('name') . ' ' . $this->request->get('family');
            $user->name = $this->request->get('name');
//            $user->latitude = $this->request->get('latitude');
//            $user->longitude =$this->request->get('longitude');
            $user->family = $this->request->get('family');
//            $user->job_title = $ValidData['job'];

//            $user->bio = ($this->request->get('bio') && $this->request->get('bio') != 'null' && $this->request->get('bio') != '') ? $this->request->get('bio') : $user->bio;
           // $user->city_id = ($this->request->get('state') && $this->request->get('state') != 'undefined' && $this->request->get('city') && $this->request->get('city') != 'undefined') ? $this->request->get('city') : 0;
          //  $user->state_id = ($this->request->get('state') && $this->request->get('state') != 'undefined') ? $this->request->get('state') : 0;
            $user->specialcode = $this->request->get('specialcode');
            $user->account_sheba = $this->request->get('account_sheba');
//            $user->address = ($this->request->get('address') && $this->request->get('address') != 'null') ? $this->request->get('address') : '';
            $user->nationalcode = $this->request->get('nid');
//            $user->visit_condition = json_encode(
//                [
//                    'my_patient_only'=>\request()->has('my_patient_only') && request()->input('my_patient_only') ?
//                        request()->input('my_patient_only') : false,
//                    'consultation_type'=>[
//                        'videoConsultation'=> \request()->has('videoConsultation') && request()->input('videoConsultation') ?
//                            request()->input('videoConsultation') : false,
//                        'voiceConsultation'=> \request()->has('voiceConsultation') && request()->input('voiceConsultation') ?
//                            request()->input('voiceConsultation') : false,
//                        'textConsultation'=> \request()->has('textConsultation') && request()->input('textConsultation') ?
//                            request()->input('textConsultation') : false,
//                    ],
//                ]
//            );
            if ($user->save()) {
                return success_template(['request' => 'update']);
            }
        }
        else
        {
            $user->account_sheba = $this->request->get('account_sheba');
            if ($user->save())
                return success_template([
                    'message'=>'شماره شبا با موفقیت ذخیره شد',
                    'account_sheba'=>$this->request->get('account_sheba')
                ]);
        }
        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');
    }


    public function storeSecretary($data)
    {
        $user = auth()->user();

        DoctorInformation::updateOrCreate(
            ['doctor_id' => $user->id ],
            [
                'office_secretary_mobile' => $data['office_secretary_mobile'],
                'office_secretary_name' => $data['office_secretary_name']
            ]
        );
        return success_template(['message'=>'با موفقیت ثبت شد']);
    }

    public function create_skill() {

        $ValidData = $this->validate($this->request,[
            'name' => 'required|string|max:191',
        ]);

        $skills = Skills::where('name', trim($ValidData['name']))->orderBy('name', 'asc')->first();
        if(!$skills){
            $new = new Skills;
            $new->name = trim($ValidData['name']);
            $new->save();
        }

        $skills = Skills::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();

        $request = [
            'skills_option' => $skills,
            'new_option' => [ 'value' => $new->id , 'label' => $new->name],
        ];

        return success_template($request);
    }

    public function saveAvatar()
    {
        $user = auth()->user();


        $ValidData = $this->validate($this->request, [
            'photo' => 'required|image|max:1024',

        ], [
            'photo.required' => ' تصویر الزامی است',
            'photo.max' => 'حجم فایل نباید بیشتر از 1 مگابایت باشد.',
        ]);

        if (isset($ValidData['photo'])) {
            $ValidDataImage = $this->validate($this->request, [
                'photo' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $image = $this->uploadAvatar('photo');

            $user->picture = $image;

        }

        if ($user->save()) {

            return success_template($user->picture);

        }

        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');
    }

}
