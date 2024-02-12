<?php

namespace App\Http\Controllers\Api\v1\Site;

use App\Enums\LanguageEnum;
use App\Model\Doctor\Specialization;
use App\Model\Platform\City;
use App\Model\Platform\State;
use App\Model\User\Skills;
use App\Model\User\Specialties;
use App\Model\Visit\DoctorCalender;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class HomeController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }

    public function index(){


        $doctors = User::where('approve' , 1)
            ->where('doctor_status' , 'active')
            ->where('status' , 'active')
            ->select(
                'id',
                'fullname',
                'username',
                'gender',
                'doctor_nickname',
                'bio',
                'picture',
                'job_title',
                'skill_json',
                'special_json'
            )
            ->inRandomOrder()
            ->limit(15)
            ->get();

        $users = [];
        if($doctors){
            foreach ($doctors as $item){
                $users[] = $item['id'];
            }
        }

        $online = [];
        $online_user = DoctorCalender::whereIn('user_id', $users)->where('data'  , '=', date('Y-m-d'))->select('user_id')->get();
        if($online_user){
            foreach ($online_user as $item){

                if(!in_array($item['user_id'] , $online)){
                    $online[] = $item['user_id'];
                }

            }
        }

        $State = State::orderBy('state', 'ASC')->get();

        $specialties = Specialties::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();

        $skills = Skills::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();

        return success_template(['doctors' => $doctors, 'state' => $State, 'skills' => $skills, 'specialties' => $specialties, 'online' => $online]);


    }

    public function getCity(){

        $ValidData = $this->validate($this->request,[
            'state' => 'required',
        ]);

        $data = City::where('state_id', $this->request->get('state'))
            ->orderBy('city', 'ASC')->get();

        return success_template($data);

    }

    public function getSpecialties(){
        $specialties = specialties_array();
        foreach ($specialties as $item){
            $doctors = User::where('approve' , 1)
                ->where('doctor_status' , 'active')
                ->where('status' , 'active')
                ->where('sp_gp' ,'LIKE','%'. $item.'%')
                ->select(
                    'id',
                    'fullname',
                    'username',
                    'gender',
                    'doctor_nickname',
                    'bio',
                    'picture',
                    'job_title',
                    'skill_json',
                    'special_json'
                )
                ->inRandomOrder()
                ->limit(7)
                ->get();

            if($doctors){
                $doctor_list[$item] = $doctors;
            }
        }

        $State = State::orderBy('state', 'ASC')->get();

        return success_template(['doctors' => $doctor_list , 'state' => $State]);

    }

    public function getSpecializations()
    {
        $specializations = Specialization::whereHas('users')->where('language_id',
            request()->has('lang') && request()->has('lang') ?
                LanguageEnum::getIdBySlug(request()->input('lang'))
                : LanguageEnum::Farsi);
        if (\request()->has('slug') && \request()->input('slug'))
        {
            $specializations = $specializations->where('slug',
                \request()->input('slug'))->orderBy('priority','ASC')->get();
        }
        else
            $specializations=$specializations->orderBy('priority','ASC')->get();
        return success_template($specializations);
    }

    public function getAllCities()
    {
        $cities = City::whereNotIn('id',[342])->get();
        return success_template($cities);
    }

    public function getAllStates()
    {
        $states = State::whereNotIn('id',[33])->get();
        return success_template($states);
    }

    public function vaccinationCenters()
    {
        $token = '75IMzsRFRypQ78%2C4gwaXRhFuGepVm%2C4f1KmyDzjptXuj%2C5N45AhVh6j8Bwa%2C1u7HCpzLqlwKNs%2C5fGDqBHISczRJx%2C7qFxddEE9bmTKr%2C6cwUppk2Iwr0FR%2C58cAnNNKko9cFi%2C25dxsELsqQHHEb%2C3vluKFNhNu4KLA%2C3S38zjO8NSOhvu%2C1wCXkNSjWJI4di%2CPxqXjd0EZV7sIi%2CPvZyHXXHZ76CEM%2CPu2qh3cbQxIkFt%2CPnhVsMtaDWoCXw%2CPmrQEiHe77Zhng%2CPYMNswwZ8DcaXN%2CPTJqMQRFvuUcJN%2CPNM8mzLntVTFSp%2CPLEFw0FEFvX5QQ%2CPKjpRza8dOQlug%2CPEMAx4ikR7VOfG%2CPDtaeSZJmmia8s%2CPBfnA4IXCnnDXN%2CP7avdTGlvkXkvz%2C7oekKky9jINspN%2C7eNSWEm6jBdOus%2C7cOEOD03iUd6fo%2C731Bhdqt6eUi0e%2C6vw93I5nNEwxcA%2C6ma3kQGzl9GY8B%2C6dZ2AT34VxE7Vr%2C6cmZgCHwjhEoYe%2C6Xx58vN742pX42%2C6XtmHjZA6NIErZ%2C6LB63GDuXYUNgo%2C6Jp3qEiAuehzm9%2C6JYj1ARZSduqdh';
        $url = 'https://poi.raah.ir/web/v4/preview-bulk/'.$token;

        $cl = new Client([
            'headers' => [
                "Accept" => "application/json",
            ]
        ]);
        $resp = json_decode($cl->get($url)->getBody()->getContents(), true);

        $response = [];

        foreach ($resp as $item){
            foreach ($item as $i) {
                $result = [
                    'name' => $i['name'],
                    'address' => $i['address'],
                    'telephone' => $i['telephone'],
                    'website' => $i['website']
                ];

                if (is_array($i['traits'])) {
                    foreach ($i['traits'] as $trait) {
                        $result['traits'][] = $trait['name'];
                    }

                    if (count($result['traits']) > 0) {
                        $response[] = $result;
                    }
                }
            }
        }

        return $response;
    }

}
