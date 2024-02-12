<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Model\Partners\RegistrationRequest;
use App\Http\Controllers\Controller;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        require(base_path('app/jdf.php'));
        $this->request = $request;

    }

    public function index()
    {
        $where_array = array();

        //filter set to query
        $applicant_name = trim($this->request->get('applicant_name'));
        $phone = trim($this->request->get('phone'));
        $partner_name = trim($this->request->get('partner_name'));
        $called = trim($this->request->get('called'));

        if ($phone) {
            $where_array[] = array('phone', "LIKE", "%" . $phone . "%");
        }
        if ($applicant_name) {
            $where_array[] = array('applicant_name', "LIKE", "%" . $applicant_name . "%");
        }
        if ($partner_name) {
            $where_array[] = array('partner_name', "LIKE", "%" . $partner_name . "%");
        }
        if ($called) {
            $where_array[] = array('called', $called);
        }

        $requests = RegistrationRequest::orderByDesc('created_at')
            ->where($where_array);

        if ($this->request->has('from') &&
            $this->request->input('from') &&
            $this->request->has('to') &&
            $this->request->input('to')){
            $from = change_number($this->request->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from_date = $from_date->formatGregorian('Y-m-d');

            $to = change_number($this->request->input('to'));
            /* @var \Hekmatinasser\Verta\Verta $to_date */
            $to = explode('/',$to);
            $to_date = Verta::create();
            $to_date->year($to[0]);
            $to_date->month($to[1]);
            $to_date->day($to[2]);
            $to_date = $to_date->formatGregorian('Y-m-d');

            $requests = $requests->whereDate('created_at','>=',$from_date)
                ->whereDate('created_at','<=',$to_date);
        }
        $requests = $requests->paginate(10);

        return view('admin.partner.registrationRequest', compact('requests'));
    }


    public function edit($id)
    {
        $request = RegistrationRequest::find($id);
        return view('admin.partner.registrationRequestEdit',compact('request'));
    }

    public function update(RegistrationRequest $registrationRequest)
    {
        $data = \request()->all();
        $data['called']= true;
        $registrationRequest->fill($data)->save();
        return redirect()->route('registration-request.index')->with(['success'=>'توضیحات با موفقیت ثبت شد']);

    }


    public function destroy($id)
    {
        $request = RegistrationRequest::find($id);

        $request->delete();

        return back()->with('success', 'حذف با موفقیت انجام شد.')->withInput();

    }
}
