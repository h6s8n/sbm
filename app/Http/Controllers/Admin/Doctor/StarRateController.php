<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\StarRate;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StarRateController extends Controller
{
    public function index()
    {
        $flag_status = -1;
        $comments = StarRate::where('votable_type', 'App\User')
            ->whereHas('doctor', function ($query) {
                if (\request()->input('dr_name')) {
                    $key = '%' . \request()->input('dr_name') . '%';
                    $query->where(function ($where) use ($key) {
                        $where->orWhere('name','LIKE' ,$key)
                            ->orWhere('family','LIKE' ,$key)
                            ->orWhere('fullname','LIKE' ,$key);
                    });
                }
            });

        if (\request()->has('flag_status') &&
            \request()->input('flag_status') >= 0 &&
            \request()->input('flag_status') != null) {
            $flag_status = \request()->input('flag_status');
            if ($flag_status == 3) {
                $comments = $comments->whereNotNull('reply');
            } else {
                $comments = $comments->where('flag', \request()->input('flag_status'));
            }
        }
        if (\request()->has('from') &&
            \request()->input('from') &&
            \request()->has('to') &&
            \request()->input('to')) {
            $from = change_number(\request()->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/', $from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from_date = $from_date->formatGregorian('Y-m-d');

            $to = change_number(\request()->input('to'));
            /* @var \Hekmatinasser\Verta\Verta $to_date */
            $to = explode('/', $to);
            $to_date = Verta::create();
            $to_date->year($to[0]);
            $to_date->month($to[1]);
            $to_date->day($to[2]);
            $to_date = $to_date->formatGregorian('Y-m-d');
            $comments = $comments->whereDate('created_at', '>=', $from_date)
                ->whereDate('created_at', '<=', $to_date);
        }
        $comments = $comments->orderBy(DB::raw('created_at'))->paginate(10);

        return view('admin.Comments.index',
            compact('comments'))
            ->with(['flag_status' => $flag_status]);
    }

    public function confirm(StarRate $comment)
    {
        $comment->flag = 1;
        $comment->save();
        return redirect()->back()->with(['success' => 'نظر با موفقیت تایید شد']);
    }

    public function rate_confirm(StarRate $comment)
    {
        $comment->flag = 5;
        $comment->save();
        return redirect()->back()->with(['success' => 'امتیاز با موفقیت ثبت شد']);
    }

    public function reject(StarRate $comment)
    {
        $comment->flag = 2;
        $comment->save();
        return redirect()->back()->with(['success' => 'نظر با موقیت رد شد']);
    }

    public function reply(StarRate $comment)
    {
        return view('admin.Comments.reply',
            compact('comment'));
    }

    public function edit(StarRate $comment)
    {
        return view('admin.Comments.edit',
            compact('comment'));
    }

    public function update(StarRate $comment)
    {
        $comment->fill(\request()->all())->save();
        if (!\request()->get('flag')){
            $comment->flag = 1;
            $comment->save();
        }
        return redirect()->route('comment.index');

    }
}
