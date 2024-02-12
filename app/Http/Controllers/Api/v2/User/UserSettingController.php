<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Model\User\SettingType;
use App\Model\User\UserSetting;
use Couchbase\UserSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mpdf\Tag\P;

class UserSettingController extends Controller
{
    public function update(Request $request)
    {
        $user_id = auth()->id();

        foreach (\request()->all() as $key=>$item)
        {
            $setting_type_id = SettingType::select('id')->where('key',$key)->first();
            $setting_type_id = $setting_type_id->id;

            $visit_condition = [
                'onlyMyPatient',
                'videoCallVisit',
                'phoneCallVisit',
                'textChatVisit'
            ];
            
            $instance = UserSetting::where('user_id',$user_id)
                ->where('setting_type_id',$setting_type_id)
                ->first();


            $user = auth()->user();
            $conditions  = json_decode($user->visit_condition,true);

            $my_patient_only = filter_var($conditions['my_patient_only'], FILTER_VALIDATE_BOOLEAN);
            $videoConsultation = filter_var($conditions['consultation_type']['videoConsultation'], FILTER_VALIDATE_BOOLEAN);
            $voiceConsultation = filter_var($conditions['consultation_type']['voiceConsultation'], FILTER_VALIDATE_BOOLEAN);
            $textConsultation = filter_var($conditions['consultation_type']['textConsultation'], FILTER_VALIDATE_BOOLEAN);


            if (in_array($key,$visit_condition)){

                switch ($key){
                    case 'onlyMyPatient':{
                        $my_patient_only = $item;
                        break;
                    }
                    case 'videoCallVisit':{
                        $videoConsultation = $item ;
                        break;
                    }
                    case "phoneCallVisit":{
                        $voiceConsultation = $item;
                        break;
                    }
                    case 'textChatVisit':{
                        $textConsultation = $item;
                        break;
                    }
                }

                $user->visit_condition = json_encode(
                    [
                        "my_patient_only" => $my_patient_only,
                        "consultation_type" => [
                            "videoConsultation" => $videoConsultation,
                            "voiceConsultation" => $voiceConsultation,
                            "textConsultation" => $textConsultation
                        ],
                    ]
                );

                $user->save();
            }

            if ($instance)
            {
                $instance->subscribed= (int)filter_var($item, FILTER_VALIDATE_BOOLEAN);
                $instance->last_changed_user_id = $user_id;
                $instance->save();
            }
            else{
                UserSetting::create([
                    'user_id'=>$user_id,
                    'setting_type_id'=>$setting_type_id,
                    'subscribed'=>$item,
                    'last_changed_user_id'=>$user_id
                ]);
            }

        }
        return success_template(1);
    }

    public function getSettings() : JsonResponse
    {
        $dr_settings=array();
        $user_id = auth()->id();
        $settings = UserSetting::select('key','subscribed')
            ->where('user_id',$user_id)
            ->join('setting_types','setting_type_id','=','setting_types.id')->get();
        foreach ($settings as $dr){
            $dr_settings[$dr->key] = (boolean)$dr->subscribed;
        }
        return success_template($dr_settings);
    }
}
