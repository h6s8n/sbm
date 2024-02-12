<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Model\Doctor\DoctorDetail;
use App\Model\Platform\FrequentlyAskedQuestion;
use Doctrine\Common\Lexer\AbstractLexer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DoctorDetailController extends Controller
{
    public function create($id)
    {
        $request = DoctorDetail::where('user_id', $id)->first();
        if ($request)
            $faqs = $request->user->FAQs()->get();
        else
            $faqs = null;
        return view('admin.DoctorDetails.create', compact('request', 'id', 'faqs'));
    }

    public function store($id)
    {
        $details = DoctorDetail::where('user_id', $id)->first();
        if ($details) {
            try {
                $details->description = \request()->input('description');
                $details->title = \request()->input('title');
                $details->content = \request()->input('content');
                $details->video_url = \request()->input('video_url');
                $details->save();
            } catch (\Exception $exception) {
                return redirect()->back()->with(['error' => 'به روز رسانی انجام نشد']);
            }
        } else {
            try {
                DoctorDetail::create([
                    'description' => \request()->input('description'),
                    'title' => \request()->input('title'),
                    'content' => \request()->input('content'),
                    'video_url'=>\request()->input('video_url'),
                    'user_id' => $id,
                ]);
            } catch (\Exception $exception) {
                return redirect()->back()->with(['error' => 'ثبت جزییات دکتر انجام نشد']);
            }
        }
        try {
            if ($details)
                $details->user->FAQs()->delete();
            $i = 0;
            foreach (\request()->input('question') as $question) {
                FrequentlyAskedQuestion::create([
                    'questionable_id' => $id,
                    'questionable_type' => 'App\User',
                    'question' => $question,
                    'answer' => \request()->input('answer')[$i]
                ]);
                $i = $i + 1;
            }
            return redirect()->back()->with(['success' => 'تغییرات با موفقیت انجام شد']);
        } catch (\Exception $exception) {
            return redirect()->back()->with(['error' => 'ثبت جزییات دکتر انجام نشد']);
        }
    }
}
