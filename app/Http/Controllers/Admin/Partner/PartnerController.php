<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Events\SMS\SetTimeNotificationEvent;
use App\Http\Controllers\Admin\VisitController;
use App\Model\Partners\Insurance;
use App\Model\Partners\Partner;
use App\Model\Partners\PartnerInsurance;
use App\Model\Partners\PartnerService;
use App\Model\Partners\Service;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Repositories\v2\File\FileInterface;
use App\Repositories\v2\Visit\VisitLogRepository;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PartnerController extends Controller
{
    private $file;

    public function __construct(FileInterface $file)
    {
        $this->file = $file;
        require(base_path('app/jdf.php'));
    }

    public function index()
    {
        $partners = Partner::paginate(10);
        return view('admin.partner.index', compact('partners'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $services = Service::all();
        $insurances = Insurance::all();
        return view('admin.partner.create', compact('services', 'insurances'));
    }

    /**
     * @return RedirectResponse
     */
    public function store()
    {
        /* Validate */
        \request()->validate([
            'name' => 'required',
            'phone' => 'required',
            'biography' => 'required',
            'address' => 'required',
            'location' => 'required',
            'times' => 'required',
            'doctor_percent' => 'required',
            'partner_percent' => 'required',
            'partner_percent' => 'required',
            'insurance_id' => 'required',
            'service_id' => 'required',
            'logo' => 'required',
        ]);

        $data = collect(request()->except(['service_id', 'insurance_id']))
            ->filter(function ($value) {
                return null !== $value;
            })->toArray();

        if (\request()->hasFile('logo')) {
            $response = $this->file->UploadLogoOrProfile(\request()->file('logo'),
                'partners',
                true);

            if ($response)
                $data['logo'] = $response->object;
            else
                return redirect()->back()->with(['error' => 'مشکلی در ثبت فایل بوجود آمده است']);
        }
        $data['token'] = str_random(8);
        $data['slug'] = str_replace(' ', '-', $data['name']);

        /* @var Partner $partner */
        $partner = Partner::create($data);
        foreach (\request()->input('service_id') as $service_id)
            PartnerService::create(['partner_id' => $partner->id, 'service_id' => $service_id]);
        foreach (\request()->input('insurance_id') as $insurance_id)
            PartnerInsurance::create(['partner_id' => $partner->id, 'insurance_id' => $insurance_id]);
        return redirect()->back()->with(['success' => 'با موفقیت ثبت شد']);
    }

    public function edit(Partner $partner)
    {
        $services = Service::all();
        $insurances = Insurance::all();
        $support = User::where('id', $partner->support_id)->first();
        return view('admin.partner.edit', compact('partner', 'insurances', 'services', 'support'));
    }

    public function update(Partner $partner)
    {

        /* Validate */
        \request()->validate([
            'name' => 'required',
            'phone' => 'required',
            'biography' => 'required',
            'address' => 'required',
            'location' => 'required',
            'times' => 'required',
            'doctor_percent' => 'required',
            'partner_percent' => 'required',
            'sheba' => 'nullable|size:24',
            'insurance_id' => 'required',
            'service_id' => 'required',
            'email' => 'nullable|string|email|max:255',
        ]);

        $data = collect(request()->except(['email', 'password', 'service_id', 'insurance_id']))
            ->filter(function ($value) {
                return null !== $value;
            })->toArray();

        if (\request()->hasFile('logo')) {
            $response = $this->file->UploadLogoOrProfile(\request()->file('logo'),
                'partners',
                true);

            if ($response)
                $data['logo'] = $response->object;
            else
                return redirect()->back()->with(['error' => 'مشکلی در ثبت فایل بوجود آمده است']);
        }
        $data['doctor_percent'] = change_number($data['doctor_percent']);
        $data['sheba'] = change_number(\request()->get('sheba'),null);
        $data['partner_percent'] = change_number($data['partner_percent']);

        if (\request()->has('email')) {
            $user = User::where('email', \request()->get('email'))->first();
            if ($user) {
                if (\request()->has('password')) {
                    $user->approve = 8;
                    $user->email = \request()->get('email');
                    $user->password = Hash::make(\request()->get('password'));
                    $user->save();
                    $data['support_id'] = $user->id;
                }
            } else {
                $new = [];
                $new['approve'] = 8;
                $new['email'] = \request()->get('email');
                $new['name'] = \request()->get('name');
                $new['family'] = '';
                $new['fullname'] = \request()->get('name');
                $new['password'] = Hash::make(\request()->get('password'));
                $new['token'] = str_random(10);
                $support = User::create($new);
                $data['support_id'] = $support->id;
            }
        }

        $partner->fill($data);
        $partner->save();
        PartnerService::where('partner_id', $partner->id)->delete();
        PartnerInsurance::where('partner_id', $partner->id)->delete();
        foreach (\request()->input('service_id') as $service_id)
            PartnerService::create(['partner_id' => $partner->id, 'service_id' => $service_id]);
        foreach (\request()->input('insurance_id') as $insurance_id)
            PartnerInsurance::create(['partner_id' => $partner->id, 'insurance_id' => $insurance_id]);
        return redirect()->back()->with(['success' => 'با موفقیت انجام شد']);
    }

    public function batchFinish(Partner $partner)
    {
        return \view('admin.partner.batchFinish', compact('partner'));
    }

    public function batchFinishStore(Partner $partner)
    {
        \request()->validate(['from' => 'required', 'to' => 'required']);
        $from = change_number(\request()->input('from'));
        $to = change_number(\request()->input('to'));

        /* @var \Hekmatinasser\Verta\Verta $from_date */
        $from = explode('/', $from);
        $from_date = Verta::create();
        $from_date->year($from[0]);
        $from_date->month($from[1]);
        $from_date->day($from[2]);

        $to = explode('/', $to);
        $to_date = Verta::create();
        $to_date->year($to[0]);
        $to_date->month($to[1]);
        $to_date->day($to[2]);

        $from_date = $from_date->formatGregorian('Y-m-d');
        $to_date = $to_date->formatGregorian('Y-m-d');

        $repository = new VisitController(new VisitLogRepository());
        $counter = 0;
        $all = 0;
        foreach ($partner->doctors()->get() as $doctor) {

            $events = $doctor->DoctorEvents('not_end')
                ->whereHas('calendar', function ($query) use ($partner) {
                    $query->where('partner_id', $partner->id);
                })->whereDate('reserve_time', '>=', $from_date)->whereDate('reserve_time', '<=', $to_date)->get();

            foreach ($events as $event) {
                $all++;
                if ($repository->finishRep($event))
                    $counter++;
            }
        }
        return redirect()->back()->with(['success' => 'از مچموع ' . $all . ' ویزیت تعداد ' . $counter . ' با موفقیت بسته شد']);
    }

    public function batchCalendar(Partner $partner)
    {
        return view('admin.partner.batchCalendar', compact('partner'));
    }

    public function storeBatchCalendar(Partner $partner)
    {
        $ValidData = $this->validate($this->request, [
            'price' => 'nullable|numeric',
            'day' => 'required|numeric',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'sum_date' => 'required|numeric',
            'date_time' => 'required',
            'time' => 'required',
            'capacity' => 'required|numeric|max:20',
        ]);

        if (\request()->has('price') && \request()->input('price') > 0) {
            if (\request()->input('price') < OurBeneficiary())
                return error_template("حداقل میلغ ویزیت " . OurBeneficiary() . " ریال می باشد");
        }

        if (!$ValidData['price']) $this->request['price'] = 0;

        $date = change_number($ValidData['year']) . '/' . $ValidData['month'] . '/' . $ValidData['day'];
        $dateTime = jalali_to_gregorian(change_number($ValidData['year']), $ValidData['month'], $ValidData['day'], '/');

        $dateTimeFull = $ValidData['date_time'];
        $timeFull = $ValidData['time'];

        $dateTimeFullNew = [];
        if ($dateTimeFull) {
            foreach ($dateTimeFull as $item) {
                $dateTimeFullNew[] = $item;
            }

            $dateTimeFull = $dateTimeFullNew;
        }


        $partner_price = ($this->request->get('partner_price')) ? $this->request->get('partner_price') : 0;

        $off_price = 0;
        $original_price = $this->request->get('price');
        $price = $original_price;
        if ($off_price && ($off_price < $original_price)) {
            $price = $off_price;
        }

        $dollar_priceـoff = 0;
        $original_dollar_price = 0;
        $dollar_price = $original_dollar_price;
        if ($dollar_priceـoff && ($dollar_priceـoff < $original_dollar_price)) {
            $dollar_price = $dollar_priceـoff;
        }

        if ($timeFull) {

            foreach ($partner->doctors()->get() as $user) {
                foreach ($timeFull as $time) {
                    $i = 0;
                    for ($i; $i < $ValidData['sum_date']; $i++) {
                        $dateTimeNew = Carbon::parse($dateTime)->addDays($i);
                        $en_date = date('Y-m-d', strtotime($dateTimeNew));
                        $fa_date = jdate('Y-m-d', strtotime($dateTimeNew));
                        $well_date = jdate('l', strtotime($dateTimeNew));

                        if (in_array($well_date, $dateTimeFull)) {
                            $request = DoctorCalender::where('user_id', $user)
                                ->where('fa_data', $fa_date)
                                ->where('time', $time)
                                ->first();

                            if (!$request) {

                                $newTime = new DoctorCalender();
                                $newTime->user_id = $user->id;
                                $newTime->fa_data = $fa_date;
                                $newTime->data = $dateTimeNew;
                                $newTime->time = $time;
                                $newTime->capacity = $ValidData['capacity'];
                                $newTime->reservation = 0;
                                $newTime->off_price = ($off_price) ? $off_price : 0;
                                $newTime->original_price = $original_price;
                                $newTime->price = $price;

                                $newTime->off_dollar_price = ($dollar_priceـoff) ? $dollar_priceـoff : 0;
                                $newTime->original_dollar_price = $original_dollar_price;
                                $newTime->dollar_price = $dollar_price;
                                $newTime->partner_price = $partner_price;
                                $newTime->partner_id = $partner->id;
//                            var_dump($newTime);
                                $newTime->save();

                                if ($i == 0)
                                    SetTimeNotificationEvent::dispatch($newTime);

                            }

                        }

                    }
                }
            }
        } else {
            return back()->with('error', 'ساعت را وارد کنید.')->withInput();
        }
        return back()->with('success', 'ثیت با موفقیت انجام شد.')->withInput();
    }
}
