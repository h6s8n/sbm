<?php

namespace App\Http\Controllers;

use App\Model\user\MedicalHistory;
use App\Model\User\Skills;
use App\Model\User\UserConfirm;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\Prescription;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\ImageManagerStatic as Image;


class DeveloperController extends Controller
{

    public function __construct()
    {

        date_default_timezone_set("Asia/Tehran");

        require(base_path('app/jdf.php'));

    }

    public function userimport(){


        $file_user = file_get_contents(base_path("jsons/users.json"));
        $file_user = json_decode($file_user);
        $file_user = $file_user[2]->data;

        $file_doctors = file_get_contents(base_path("jsons/doctors.json"));
        $file_doctors = json_decode($file_doctors);
        $file_doctors = $file_doctors[2]->data;

        $file_mdical_history = file_get_contents(base_path("jsons/medicalhistories.json"));
        $file_mdical_history = json_decode($file_mdical_history);
        $file_mdical_history = $file_mdical_history[2]->data;

        $file_skill = file_get_contents(base_path("jsons/usersskills.json"));
        $file_skill = json_decode($file_skill);
        $file_skill = $file_skill[2]->data;

        $imageServer = 'https://sandbox.sbm24.net/statics-public';

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        $imagePath = "images/v1";

        foreach ($file_user as $user_item){

            $cellphone = '';
            if($user_item->cellphone){
                $cellphone = change_phone($user_item->cellphone);
            }

            $new = new User;
            $new->id = $user_item->id;
            $new->email = $user_item->email;
            $new->mobile = $cellphone;
            $new->password = $user_item->password;
            $new->nationalcode = $user_item->nationalcode;
            $new->name = $user_item->name;
            $new->family = $user_item->family;
            $new->fullname = $user_item->fullname;
            $new->gender = $user_item->gender;
            $new->job_title = $user_item->job_title;
            $new->birthday = $user_item->birthday;
            $new->picture = ($user_item->picture) ? "{$imageServer}/{$imagePath}/" . $user_item->picture : null;
            $new->zone = $user_item->zone;
            $new->state_id = ($user_item->state_id) ? $user_item->state_id : 0;
            $new->city_id = ($user_item->city_id) ? $user_item->city_id : 0;
            $new->address = $user_item->address;
            $new->bio = $user_item->bio;
            $new->status = ($user_item->active) ? 'active' : 'inactive';
            $new->credit = $user_item->credit;
            $new->token = $user_item->token;
            $new->show_phone = $user_item->show_phone;
            $new->created_at = $user_item->created_at;
            $new->updated_at = $user_item->updated_at;
            $new->username = ($user_item->username) ? $user_item->username : str_random(10);

            if($user_item->approve == '-1'){
                $new->approve = 10;
            }elseif($user_item->approve == '2'){
                $new->approve = 2;

                $status = 0;
                foreach ($file_mdical_history as $item){
                    if($item->id && $user_item->id){
                        if($item->ghad){
                            $status = 1;
                        }
                    }
                }

                $new->mdical_history_status = $status;

            }elseif($user_item->approve == '0'){

                $new->approve = 1;
                $new->doctor_status = 'active';

            }elseif($user_item->approve == '1'){

                $new->approve = 1;
                $new->doctor_status = 'inactive';

            }

            if($user_item->approve == '1' || $user_item->approve == '0'){

                $account_number = '';
                foreach ($file_doctors as $item){
                    if($item->id && $user_item->id){
                        $account_number = $item->account_number;
                    }
                }

                $new->account_number = $account_number;
                $new->last_calender_time = $user_item->last_activity;
                $new->doctor_visit_price = 0;
                $new->passport_image = ($user_item->passport) ? "{$imageServer}/{$imagePath}/" . $user_item->passport : null;
                $new->national_cart_image = ($user_item->nationalcart) ? "{$imageServer}/{$imagePath}/" . $user_item->nationalcart : null;
                $new->special_cart_image = ($user_item->specialcodeimage) ? "{$imageServer}/{$imagePath}/" . $user_item->specialcodeimage : null;
                $new->education_image = ($user_item->educationimage) ? "{$imageServer}/{$imagePath}/" . $user_item->educationimage : null;
                $new->specialcode = $user_item->specialcode;


                $skill_json = [];
                foreach ($file_skill as $item){
                    if($item->doctor_id && $user_item->id){

                        $skill = Skills::where('id' , $item->skill_id)->first();
                        if($skill) {
                            $skill_json[] = [
                                'value' => $skill->id,
                                'label' => $skill->name
                            ];
                        }

                    }
                }


                $new->skill_json = json_encode($skill_json);


            }


            var_dump($user_item->id);

            if($new->save()){
                var_dump($user_item->id . ' save');
            }else{
                var_dump($user_item->id . ' not save');
            }

        }


        return '';

    }

    public function users_skills(){


        $file_user = file_get_contents(base_path("jsons/users.json"));
        $file_user = json_decode($file_user);
        $file_user = $file_user[2]->data;

        $file_skill = file_get_contents(base_path("jsons/usersskills.json"));
        $file_skill = json_decode($file_skill);
        $file_skill = $file_skill[2]->data;

        foreach ($file_user as $user_item){

            if($user_item->approve == '1' || $user_item->approve == '0'){


                $skill_json = [];
                foreach ($file_skill as $item){
                    if($item->doctor_id == $user_item->id){

                        $skill = Skills::where('id' , $item->skill_id)->first();
                        if($skill) {
                            $skill_json[] = [
                                'value' => $skill->id,
                                'label' => $skill->name
                            ];
                        }

                    }
                }

                if($skill_json){

                    $user = User::where('id', $user_item->id)->first();
                    if($user){
                        $user->skill_json = json_encode($skill_json);
                        $user->save();
                    }

                }else{

                    $user = User::where('id', $user_item->id)->first();
                    if($user){
                        $user->skill_json = json_encode([]);
                        $user->save();
                    }

                }


                //$new->skill_json = json_encode($skill_json);


            }


            //$new->save();

        }


        return '';

    }

    public function user_mdical_history(){


        $file_mdical_history = file_get_contents(base_path("jsons/medicalhistories.json"));
        $file_mdical_history = json_decode($file_mdical_history);
        $file_mdical_history = $file_mdical_history[2]->data;


        foreach ($file_mdical_history as $item){

            if($item->ghad){

                $request_update = New MedicalHistory;
                $request_update->user_id = $item->id;

                $request_update->height = $item->ghad;
                $request_update->weight = $item->vazn;
                $request_update->job = $item->job;
                $request_update->bloodtype = $item->bloodtype;

                $request_update->severeـillnessـchek = ($item->severeـillnessـchek == "on") ? 1 : 0;
                $request_update->severeـillness = ($item->severeـillnessـchek == "on" && $item->severeـillness) ? $item->severeـillness : '';

                $request_update->smoke_chek = ($item->smoke_chek == "on") ? 1 : 0;
                $request_update->smoke = ($item->smoke_chek == "on" && $item->smoke) ? $item->smoke : '';

                $request_update->drink_alcohol_chek = ($item->drink_alcohol_chek == "on") ? 1 : 0;
                $request_update->drink_alcohol = ($item->drink_alcohol_chek == "on" && $item->drink_alcohol) ? $item->drink_alcohol : '';

                $request_update->addiction = ($item->addiction == "on") ? 1 : 0;
                $request_update->addiction_text = ($item->addiction == "on" && $item->addiction_text) ? $item->addiction_text : '';

                $request_update->medications_chek = ($item->medications_chek == "on") ? 1 : 0;
                $request_update->medications = ($item->medications_chek == "on" && $item->medications) ? $item->medications : '';

                $request_update->regular_exercise = ($item->regular_exercise == "on") ? 1 : 0;
                $request_update->regular_exercise_text = ($item->regular_exercise == "on" && $item->regular_exercise_text) ? $item->regular_exercise_text : '';

                $request_update->alergies = ($item->alergies == "on") ? 1 : 0;
                $request_update->alergies_text = ($item->alergies == "on" && $item->alergies_text) ? $item->alergies_text : '';

                $request_update->hereditary_illness = ($item->hereditary_illness == "on") ? 1 : 0;
                $request_update->hereditary_illness_text = ($item->hereditary_illness == "on" && $item->hereditary_illness_text) ? $item->hereditary_illness_text : '';

                /*$request_update->history_disease_1 = ($item->history_disease_1 == "true") ? 1 : 0;
                $request_update->history_disease_2 = ($item->history_disease_2 == "true") ? 1 : 0;
                $request_update->history_disease_3 = ($item->history_disease_3 == "true") ? 1 : 0;
                $request_update->history_disease_4 = ($item->history_disease_4 == "true") ? 1 : 0;
                $request_update->history_disease_5 = ($item->history_disease_5 == "true") ? 1 : 0;
                $request_update->history_disease_6 = ($item->history_disease_6 == "true") ? 1 : 0;
                $request_update->history_disease_7 = ($item->history_disease_7 == "true") ? 1 : 0;
                $request_update->history_disease_8 = ($item->history_disease_8 == "true") ? 1 : 0;
                $request_update->history_disease_9 = ($item->history_disease_9 == "true") ? 1 : 0;
                $request_update->history_disease_10 = ($item->history_disease_10 == "true") ? 1 : 0;
                $request_update->history_disease_11 = ($item->history_disease_11 == "true") ? 1 : 0;
                $request_update->history_disease_12 = ($item->history_disease_12 == "true") ? 1 : 0;
                $request_update->history_disease_13 = ($item->history_disease_13 == "true") ? 1 : 0;*/

                $request_update->medicalnote = $item->medicalnote;
                $request_update->save();

            }

        }

        return '';

    }

    public function user_confirms(){


        $file_user_confirms = file_get_contents(base_path("jsons/user_confirms.json"));
        $file_user_confirms = json_decode($file_user_confirms);
        $file_user_confirms = $file_user_confirms[2]->data;


        foreach ($file_user_confirms as $item){

            if($item->status == 'verified'){

                $request_update = New UserConfirm();
                $request_update->mobile = $item->mobile;
                $request_update->email = $item->email;
                $request_update->confirm = $item->confirm;
                $request_update->status = $item->status;
                $request_update->created_at = $item->created_at;
                $request_update->save();

            }

        }

        return '';

    }

    public function doctor_calenders(){


        $file_doctor_calenders = file_get_contents(base_path("jsons/userslocationevents.json"));
        $file_doctor_calenders = json_decode($file_doctor_calenders);
        $file_doctor_calenders = $file_doctor_calenders[2]->data;


        foreach ($file_doctor_calenders as $item){

            $en_date = date('Y-m-d H:i:s', strtotime($item->date));
            $fa_date = jdate('Y-m-d', strtotime($item->date));
            $start_time = jdate('H', strtotime($item->start_time));

            $request_update = New DoctorCalender();
            $request_update->id = $item->id;
            $request_update->user_id = $item->doctor_id;
            $request_update->fa_data = $fa_date;
            $request_update->data = $en_date;
            $request_update->time = $start_time;
            $request_update->capacity = $item->visitpertime;
            $request_update->original_price = $item->price;
            $request_update->price = $item->price;
            $request_update->created_at = $item->created_at;
            $request_update->updated_at = $item->updated_at;

            $request_update->save();

        }

        return '';

    }

    public function event_reserves(){


        $file_event_reserves = file_get_contents(base_path("jsons/eventreserves.json"));
        $file_event_reserves = json_decode($file_event_reserves);
        $file_event_reserves = $file_event_reserves[2]->data;


        foreach ($file_event_reserves as $item){

            $calender = DoctorCalender::where('id', $item->users_location_event_id)->first();

            if($calender){

                $request_update = New EventReserves();
                $request_update->id = $item->id;
                $request_update->token_room = $item->remember_token;
                $request_update->user_id = $item->user_id;
                $request_update->doctor_id = $item->doctor_id;
                $request_update->calender_id = $item->users_location_event_id;
                $request_update->fa_data = $calender->fa_data;
                $request_update->data = $calender->data;
                $request_update->time = $calender->time;
                $request_update->last_activity_doctor = $item->last_activity_doctor;
                $request_update->last_activity_user = $item->last_activity_user;
                $request_update->visit_status = ($item->visitdone == 0) ? 'not_end' : 'end';
                $request_update->created_at = $item->created_at;
                $request_update->updated_at = $item->updated_at;

                $request_update->save();

                if($item->explain){

                    $new = new Dossiers();
                    $new->user_id = $item->user_id;
                    $new->audience_id = $item->doctor_id;
                    $new->event_id =  $item->users_location_event_id;
                    $new->message = $item->explain;
                    $new->seen_audience = 1;
                    $new->created_at = $item->created_at;
                    $new->updated_at = $item->updated_at;
                    $new->save();

                }

            }


        }

        return '';

    }

    public function messages(){


        $file_messages = file_get_contents(base_path("jsons/user_chats.json"));
        $file_messages = json_decode($file_messages);
        $file_messages = $file_messages[2]->data;


        foreach ($file_messages as $item){

            $event = EventReserves::where('id', $item->event_reserve_id)->first();
            if($event){

                $audience_id = $event->doctor_id;
                if($event->doctor_id == $item->user_id){
                    $audience_id = $event->user_id;
                }

                $request_update = New Message();
                $request_update->user_id = $item->user_id;
                $request_update->audience_id = $audience_id;
                $request_update->seen_audience = $item->seen;
                $request_update->message = $item->massages;
                $request_update->created_at = $item->created_at;
                $request_update->updated_at = $item->updated_at;

                $request_update->save();

            }


        }

        return '';

    }

    public function prescriptions(){


        $file_prescriptions = file_get_contents(base_path("jsons/prescriptions.json"));
        $file_prescriptions = json_decode($file_prescriptions);
        $file_prescriptions = $file_prescriptions[2]->data;


        $imageServer = 'https://sandbox.sbm24.net/statics-public';

        $imagePath = "files/old_file";

        foreach ($file_prescriptions as $item){

            $event = EventReserves::where('id', $item->event_reserve_id)->first();
            if($event){

                $request_update = New Prescription();
                $request_update->id = $item->id;
                $request_update->user_id = $item->doctor_id;
                $request_update->audience_id = $item->user_id;
                $request_update->event_id = $item->event_reserve_id;
                $request_update->file = ($item->image) ? "{$imageServer}/{$imagePath}/" . $item->image : null;
                $request_update->message = $item->prescription;
                $request_update->created_at = $item->created_at;
                $request_update->updated_at = $item->updated_at;

                $request_update->save();

            }


        }

        return '';

    }

    public function dossiers(){


        $file_event_reserves = file_get_contents(base_path("jsons/reservedossiers.json"));
        $file_event_reserves = json_decode($file_event_reserves);
        $file_event_reserves = $file_event_reserves[2]->data;

        $imageServer = 'https://sandbox.sbm24.net/statics-public';

        $imagePath = "files/old_file";

        foreach ($file_event_reserves as $item){

            $event = EventReserves::where('id', $item->event_reserve_id)->first();

            if($event){

                if($item->sendBy == 1){

                    $new = new Dossiers();
                    $new->user_id = $event->user_id;
                    $new->audience_id = $event->doctor_id;
                    $new->event_id =  $item->event_reserve_id;
                    $new->message = $item->text;
                    $new->file = ($item->file) ? "{$imageServer}/{$imagePath}/" . $item->file : null;
                    $new->seen_audience = 1;
                    $new->created_at = $item->created_at;
                    $new->updated_at = $item->updated_at;
                    $new->save();

                    if($item->textDescript){

                        $new = new Dossiers();
                        $new->user_id = $event->doctor_id;
                        $new->audience_id = $event->user_id;
                        $new->event_id =  $item->event_reserve_id;
                        $new->message = $item->textDescript;
                        $new->file = null;
                        $new->seen_audience = 1;
                        $new->created_at = $item->created_at;
                        $new->updated_at = $item->updated_at;
                        $new->save();

                    }


                }else{

                    $new = new Dossiers();
                    $new->user_id = $event->doctor_id;
                    $new->audience_id = $event->user_id;
                    $new->event_id =  $item->event_reserve_id;
                    $new->message = $item->text;
                    $new->file = ($item->file) ? "{$imageServer}/{$imagePath}/" . $item->file : null;
                    $new->seen_audience = 1;
                    $new->created_at = $item->created_at;
                    $new->updated_at = $item->updated_at;
                    $new->save();


                    if($item->textDescript){

                        $new = new Dossiers();
                        $new->user_id = $event->user_id;
                        $new->audience_id = $event->doctor_id;
                        $new->event_id =  $item->event_reserve_id;
                        $new->message = $item->textDescript;
                        $new->file = null;
                        $new->seen_audience = 1;
                        $new->created_at = $item->created_at;
                        $new->updated_at = $item->updated_at;
                        $new->save();

                    }

                }

            }


        }

        return '';

    }

    public function transaction_reserves()
    {


        $file_transaction = file_get_contents(base_path("jsons/purchases.json"));
        $file_transaction = json_decode($file_transaction);
        $file_transaction = $file_transaction[2]->data;


        foreach ($file_transaction as $item) {

            $calender = DoctorCalender::where('id', $item->users_location_event_id)->first();
            if ($calender) {

                $request_update = New TransactionReserve();
                $request_update->user_id = $item->user_id;
                $request_update->doctor_id = $calender->user_id;
                $request_update->calender_id = $item->users_location_event_id;
                $request_update->amount = $item->amount;
                $request_update->token = str_random(7);
                $request_update->used_credit = 0;
                $request_update->amount_paid = $item->amount;
                $request_update->status = ($item->confirm == 1) ? 'paid' : 'pending';
                $request_update->created_at = $item->created_at;
                $request_update->updated_at = $item->updated_at;

                $request_update->save();

            }


        }

        return '';

    }

    public function transaction_doctors(){


        $file_transaction = file_get_contents(base_path("jsons/doctor_payments.json"));
        $file_transaction = json_decode($file_transaction);
        $file_transaction = $file_transaction[2]->data;


        foreach ($file_transaction as $item){

            $calender = DoctorCalender::where('id', $item->users_location_event_id)->first();
            if($calender){

                $request_update = New TransactionReserve();
                $request_update->user_id = $item->user_id;
                $request_update->doctor_id = $calender->user_id;
                $request_update->calender_id = $item->users_location_event_id;
                $request_update->amount = $item->amount;
                $request_update->token = str_random(7);
                $request_update->used_credit = 0;
                $request_update->amount_paid = $item->amount;
                $request_update->status = ($item->confirm == 1) ? 'paid' : 'pending';
                $request_update->created_at = $item->created_at;

                $request_update->save();

            }


        }

        return '';

    }

    public function update_image(){


        $user = User::get();


        foreach ($user as $item){


            if($item->picture){

                $item->picture = str_replace('old_image', 'v1', $item->picture);
                $item->save();

            }


        }

        return '';

    }


    public function edit_doctor_calenders(){

        $request = DoctorCalender::orderBy('created_at' , 'desc')->get();
        if($request){

            foreach ($request as $item){

                $fa_date_part = explode( '-' , $item['fa_data'] );
                $en_date = jalali_to_gregorian($fa_date_part[0],$fa_date_part[1],$fa_date_part[2],'-');

                $item->data = $en_date;
                $item->save();


            }

        }

    }

    public function edit_event_reserves(){

        $request = EventReserves::orderBy('created_at' , 'desc')->get();
        if($request){

            foreach ($request as $item){

                $fa_date_part = explode( '-' , $item['fa_data'] );
                $en_date = jalali_to_gregorian($fa_date_part[0],$fa_date_part[1],$fa_date_part[2],'-');

                $item->data = $en_date;
                $item->save();


            }

        }

    }


    public function chane_user_image(){

        $filesystem = new Filesystem();
        $users = User::get();

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        if($users){
            foreach ($users as $item){
                if($item['picture']){

                    $user_id = $item['id'];
                    $picture_old = $item['picture'];

                    $picture = str_replace('https://sandbox.sbm24.net/statics-public/' , '', $picture_old);
                    $picture = str_replace(env('path_live'), env('path_root'), base_path($picture));

                    $root_Path_v1 = "images/member{$user_id}";
                    $root_Path = str_replace(env('path_live'), env('path_root'), base_path($root_Path_v1));
                    $imagePath_v1 = "{$root_Path_v1}/{$year}/{$month}/{$day}";
                    $imagePath = "{$root_Path}/{$year}/{$month}/{$day}";

                    if(!$filesystem->exists("{$root_Path}")){
                        $filesystem->makeDirectory("{$root_Path}");
                    }
                    if(!$filesystem->exists("{$root_Path}/{$year}")){
                        $filesystem->makeDirectory("{$root_Path}/{$year}");
                    }
                    if(!$filesystem->exists("{$root_Path}/{$year}/{$month}")){
                        $filesystem->makeDirectory("{$root_Path}/{$year}/{$month}");
                    }
                    if(!$filesystem->exists("{$root_Path}/{$year}/{$month}/{$day}")){
                        $filesystem->makeDirectory("{$root_Path}/{$year}/{$month}/{$day}");
                    }

                    var_dump([
                        'picture_old' => $picture_old,
                        'picture' => $picture,
                        'imagePath' => $imagePath,
                    ]);


                    $fileName = Carbon::now()->timestamp . ".jpg";

                    Image::make("{$picture}")->resize(185, null, function ($constraint) {$constraint->aspectRatio(); })->save("{$imagePath}/{$fileName}");

                    $picture_new = 'https://sandbox.sbm24.net/statics-public/' . $imagePath_v1 . '/' . $fileName;

                    $item['picture'] = $picture_new;
                    $item->save();

                }
            }
        }

    }

    public function image_resize(){

        $filesystem = new Filesystem();
        $users = User::whereNOTNull('picture')->where('picture_org' , '')->limit(1)->orderBy('id', 'ASC')->get();

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        if($users){
            foreach ($users as $item){
                if($item->picture){

                    $user_id = $item->id;
                    var_dump($user_id);
                    $picture_old = $item->picture;

                    $Path = $this->fileFinalPath('images');
                    $Path_path = $Path["path"];

                    $picture = '/var/www/vhosts/sandbox.sbm24.net/httpdocs/statics-public/' . str_replace('https://sandbox.sbm24.net/statics-public/' , '', $picture_old);


                    $fileName = Carbon::now()->timestamp . ".webp";


                    Image::make($picture)->resize(181, null, function ($constraint) {$constraint->aspectRatio(); })->save("{$Path_path}/{$fileName}");

                    $picture_new = '';
                    $picture_new = $Path["url"] . $fileName;

                    $item->picture = $picture_new;
                    $item->picture_org = $picture_old;
                    $item->save();
                }
            }
        }
        return '';

    }

    public function change_visit_hours(){


        $events = EventReserves::orderBy('created_at', 'ASC')->get();
        if($events){

            foreach ($events as $item){

                $calender = DoctorCalender::where('id' , $item->calender_id)->first();
                if($calender && !$item->reserve_time){


                    $capacity_mints = 60 / $calender->capacity;
                    $max_time = $calender->time + 1;
                    $reserve_time = null;

                    $fa_date_part = explode( '-' , $calender->fa_data );
                    $en_date = jalali_to_gregorian($fa_date_part[0],$fa_date_part[1],$fa_date_part[2],'-');


                    $start_date = date('Y-m-d', strtotime($en_date));
                    if((jdate('Y-m-d', strtotime($item->created_at)) == jdate('Y-m-d', strtotime($start_date))) && (((int) date('H', strtotime($item->created_at)) ) == $calender->time)){
                        $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i', strtotime($item->created_at)));
                    }else{
                        $start = Carbon::parse($start_date)->addHours($calender->time);
                    }

                    $getevents = EventReserves::where('doctor_id' , $calender->user_id)->where('fa_data' , $calender->fa_data)->where('time' , $calender->time)->where('visit_status' , 'not_end')->orderBy('reserve_time', 'ASC')->first();
                    if($getevents && $getevents->reserve_time){

                        $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

                    }

                    $reserve_time = date('Y-m-d H:i', strtotime($start));

                    if(((int) date('H', strtotime($start))) >= $max_time){
                        $min = date('i', strtotime($start));
                        $min += 10;

                        $start = Carbon::parse($reserve_time)->subMinutes($min);
                        $reserve_time = date('Y-m-d H:i', strtotime($start));
                    }

                    //var_dump($item->calender_id . '---' .$reserve_time);
                    $item->reserve_time = $reserve_time;
                    $item->save();

                }

            }


        }

    }



    public function transaction_change_event()
    {

        $Reserve = TransactionReserve::whereNull('event_id')->get();

        foreach ($Reserve as $item) {

            $event = EventReserves::where('user_id', $item->user_id)->where('doctor_id', $item->doctor_id)->where('calender_id', $item->calender_id)->first();
            if ($event) {

                $item->event_id = $event->id;

                $item->save();

                var_dump($item->id);

            }


        }

        return '';

    }

    public function event_reserves_change_finish()
    {

        $Reserve = EventReserves::where('visit_status', 'end')->get();

        foreach ($Reserve as $item) {

            $item->finish_at = $item->updated_at;

            $item->save();


        }

        return '';

    }

    public function event_reserves_finish()
    {

        $Reserve = EventReserves::where('visit_status', 'not_end')->where('reserve_time', '<=' , '2020-05-20 20:15:00')->get();

        foreach ($Reserve as $item) {

            $item->visit_status = 'end';
            $item->doctor_payment_status = 'doctor';
            $item->finish_at = $item->updated_at;

            $item->save();



            $transaction = TransactionReserve::where('user_id', $item->user_id)->where('doctor_id', $item->id)->where('calender_id', $item->calender_id)->where('status', 'paid')->first();
            if($transaction) {

                $amount_visit = $transaction->amount;
                $amount = 0;

                if($amount_visit > 400000){
                    $amount_pe = ( $amount_visit * 20 ) / 100;
                    $amount = $amount_visit - $amount_pe;
                }else{
                    $amount = $amount_visit - 80000;
                }
                if($amount < 0) $amount = 0;

                $transactionDr = new TransactionDoctor();
                $transactionDr->status = 'paid';
                $transactionDr->message = 'پرداخت توسط پشتیبانی';
                $transactionDr->user_id = $item->user_id;
                $transactionDr->doctor_id = $item->doctor_id;
                $transactionDr->event_id = $item->id;
                $transactionDr->amount = $amount;
                $transactionDr->save();
            }


        }

        return '';

    }

    public function TransactionReserveApp()
    {

        $Reserve = EventReserves::where('reserve_time', '<=' , '2020-05-21')->get();

        foreach ($Reserve as $item) {

            $Reserve2 = TransactionDoctor::where('status', 'pending')->where('event_id', $item->id)->get();


            foreach ($Reserve2 as $item2) {

                var_dump($item2->id);
                $item2->status = 'paid';
                $item2->message = 'پرداخت توسط پشتیبانی';

                $item2->save();


            }
        }

        return '';

    }





}
