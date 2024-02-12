<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Model\user\MedicalHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MedicalHistoryController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
    }


    public function getInfo(){

        $user = auth()->user();
        $request = MedicalHistory::where('user_id' , $user->id)->orderBy('created_at', 'desc')->first();

        return success_template($request);

    }


    public function save(){

        $ValidData = $this->validate($this->request,[
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'job' => 'required|string|max:191',
            'bloodtype' => 'required|string|max:10',

            'severeـillnessـchek' => 'nullable',
            'severeـillness' => 'string|max:191|nullable',

            'smoke_chek' => 'nullable',
            'smoke' => 'string|max:191|nullable',

            'drink_alcohol_chek' => 'nullable',
            'drink_alcohol' => 'string|max:191|nullable',

            'addiction' => 'nullable',
            'addiction_text' => 'string|max:191|nullable',

            'medications_chek' => 'nullable',
            'medications' => 'string|max:191|nullable',

            'regular_exercise' => 'nullable',
            'regular_exercise_text' => 'string|max:191|nullable',

            'alergies' => 'nullable',
            'alergies_text' => 'string|max:191|nullable',

            'hereditary_illness' => 'nullable',
            'hereditary_illness_text' => 'string|max:191|nullable',

            'history_disease_1' => 'nullable',
            'history_disease_2' => 'nullable',
            'history_disease_3' => 'nullable',
            'history_disease_4' => 'nullable',
            'history_disease_5' => 'nullable',
            'history_disease_6' => 'nullable',
            'history_disease_7' => 'nullable',
            'history_disease_8' => 'nullable',
            'history_disease_9' => 'nullable',
            'history_disease_10' => 'nullable',
            'history_disease_11' => 'nullable',
            'history_disease_12' => 'nullable',
            'history_disease_13' => 'nullable',

            'medicalnote' => 'string|nullable',
        ]);

        $user = auth()->user();

        $request_update = [];
        $request = MedicalHistory::where('user_id' , $user->id)->orderBy('created_at', 'desc')->first();
        if($request){
            $request_update = $request;
        }else{
            $request_update = New MedicalHistory;
            $request_update->user_id = $user->id;
        }

        $request_update->height = $ValidData['height'];
        $request_update->weight = $ValidData['weight'];
        $request_update->job = $ValidData['job'];
        $request_update->bloodtype = $ValidData['bloodtype'];

        $request_update->severeـillnessـchek = ($ValidData['severeـillnessـchek'] == "true") ? 1 : 0;
        $request_update->severeـillness = ($ValidData['severeـillnessـchek'] == "true" && $ValidData['severeـillness']) ? $ValidData['severeـillness'] : '';

        $request_update->smoke_chek = ($ValidData['smoke_chek'] == "true") ? 1 : 0;
        $request_update->smoke = ($ValidData['smoke_chek'] == "true" && $ValidData['smoke']) ? $ValidData['smoke'] : '';

        $request_update->drink_alcohol_chek = ($ValidData['drink_alcohol_chek'] == "true") ? 1 : 0;
        $request_update->drink_alcohol = ($ValidData['drink_alcohol_chek'] == "true" && $ValidData['drink_alcohol']) ? $ValidData['drink_alcohol'] : '';

        $request_update->addiction = ($ValidData['addiction'] == "true") ? 1 : 0;
        $request_update->addiction_text = ($ValidData['addiction'] == "true" && $ValidData['addiction_text']) ? $ValidData['addiction_text'] : '';

        $request_update->medications_chek = ($ValidData['medications_chek'] == "true") ? 1 : 0;
        $request_update->medications = ($ValidData['medications_chek'] == "true" && $ValidData['medications']) ? $ValidData['medications'] : '';

        $request_update->regular_exercise = ($ValidData['regular_exercise'] == "true") ? 1 : 0;
        $request_update->regular_exercise_text = ($ValidData['regular_exercise'] == "true" && $ValidData['regular_exercise_text']) ? $ValidData['regular_exercise_text'] : '';

        $request_update->alergies = ($ValidData['alergies'] == "true") ? 1 : 0;
        $request_update->alergies_text = ($ValidData['alergies'] == "true" && $ValidData['alergies_text']) ? $ValidData['alergies_text'] : '';

        $request_update->hereditary_illness = ($ValidData['hereditary_illness'] == "true") ? 1 : 0;
        $request_update->hereditary_illness_text = ($ValidData['hereditary_illness'] == "true" && $ValidData['hereditary_illness_text']) ? $ValidData['hereditary_illness_text'] : '';

        $request_update->history_disease_1 = ($ValidData['history_disease_1'] == "true") ? 1 : 0;
        $request_update->history_disease_2 = ($ValidData['history_disease_2'] == "true") ? 1 : 0;
        $request_update->history_disease_3 = ($ValidData['history_disease_3'] == "true") ? 1 : 0;
        $request_update->history_disease_4 = ($ValidData['history_disease_4'] == "true") ? 1 : 0;
        $request_update->history_disease_5 = ($ValidData['history_disease_5'] == "true") ? 1 : 0;
        $request_update->history_disease_6 = ($ValidData['history_disease_6'] == "true") ? 1 : 0;
        $request_update->history_disease_7 = ($ValidData['history_disease_7'] == "true") ? 1 : 0;
        $request_update->history_disease_8 = ($ValidData['history_disease_8'] == "true") ? 1 : 0;
        $request_update->history_disease_9 = ($ValidData['history_disease_9'] == "true") ? 1 : 0;
        $request_update->history_disease_10 = ($ValidData['history_disease_10'] == "true") ? 1 : 0;
        $request_update->history_disease_11 = ($ValidData['history_disease_11'] == "true") ? 1 : 0;
        $request_update->history_disease_12 = ($ValidData['history_disease_12'] == "true") ? 1 : 0;
        $request_update->history_disease_13 = ($ValidData['history_disease_13'] == "true") ? 1 : 0;

        $request_update->medicalnote = $ValidData['medicalnote'];

        if($request_update->save()){

            $user->mdical_history_status = 1;
            $user->save();

            return success_template($request_update);

        }

        return error_template('خطا در ذخیره اطلاعات ، لطفا چند لحظه دیگر امتحان کنید.');

    }


}
