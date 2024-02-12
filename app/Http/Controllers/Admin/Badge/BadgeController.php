<?php

namespace App\Http\Controllers\Admin\Badge;

use App\Model\Badge\Badge;
use App\Model\Badge\BadgeRequest;
use App\Model\Badge\UserBadge;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Promise\all;

class BadgeController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        require (base_path('app/jdf.php'));
    }

    public function create()
    {
        return view('admin.Badge.create');
    }

    public function index()
    {
        $badges = Badge::paginate(10);
        return view('admin.Badge.index', compact('badges'));
    }

    public function store(Request $request)
    {
        \request()->validate([
            'name' => 'required',
            'priority' => 'required',
            'icon' => 'required'
        ]);
        $badge = Badge::create($request->all());
        if ($badge instanceof Badge) {
            if ($request->file('icon')) {
                $icon = $this->uploadImageCt('icon', 'icon');
                $badge->icon = $icon;
                $badge->save();
            }
            return redirect()->back()->with('success', 'نشان جدید با موفقیت ثبت شد');
        } else {
            return redirect()->back()->with('error', 'ثبت اطلاعات با مشکل مواجه شده است');
        }
    }

    public function edit(Badge $badge)
    {
        return view('admin.Badge.edit', compact('badge'));
    }

    public function update(Badge $badge)
    {
        \request()->validate([
            'name' => 'required',
            'priority' => 'required'
        ]);
        $data = \request()->all();
        if (\request()->file('icon')) {
            $icon = $this->uploadImageCt('icon', 'icon');
            $data['icon'] = $icon;
        }
        $badge->fill($data)->save();
        return redirect()->back()->with(['success' => 'ویرایش نشان با موفقیت انجام شد']);
    }

    public function assign(User $user)
    {
        $badges = Badge::all();
        return view('admin.Badge.DoctorBadge', compact('badges','user'));
    }

    public function storeAssign($user)
    {
        if (\request()->has('activation_time')) {
            $activation_time = str_replace('/', '-',
                change_number(\request()->input('activation_time')));
            $activation_time = explode('-', $activation_time);
            $activation_time = Verta::getGregorian($activation_time[0], $activation_time[1], $activation_time[2]);
            $activation_time = Carbon::create($activation_time[0], $activation_time[1], $activation_time[2])->format('Y-m-d');
        }
        if (\request()->has('expiration_time')) {
            $expiration_time = str_replace('/', '-',
                change_number(\request()->input('expiration_time')));
            $expiration_time = explode('-', $expiration_time);
            $expiration_time = Verta::getGregorian($expiration_time[0], $expiration_time[1], $expiration_time[2]);
            $expiration_time = Carbon::create($expiration_time[0], $expiration_time[1], $expiration_time[2])->format('Y-m-d');
        }
        UserBadge::create([
            'badge_id'=>$this->request->input('badge_id'),
            'user_id'=>$user,
            'last_changed_user_id'=>auth()->id(),
            'activation_time'=>$activation_time,
            'expiration_time'=>$expiration_time
        ]);
        return redirect()->back();
    }

    public function detach(Badge $badge,User $user)
    {
        $user->badges()->detach($badge);
        return redirect()->back()->with(['success' => ' نشان با موفقیت حذف شد']);
    }

    public function requests()
    {
        $badge_requests = BadgeRequest::paginate(20);
        return view('admin.Badge.badgeRequest', compact('badge_requests'));
    }

    public function editRequest(BadgeRequest $request)
    {
        return view('admin.Badge.editRequest', compact('request'));
    }

    public function updateRequest(BadgeRequest $request)
    {
        \request()->validate([
            'status' => 'required',
        ]);

        \request()->merge(['updated_by'=>auth()->id()]);

        $data = \request()->all();
        $request->fill($data)->save();
        return redirect()->back()->with(['success' => ' با موفقیت انجام شد']);
    }
}
