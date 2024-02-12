<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Model\Doctor\Specialization;
use App\Model\Language;
use App\Repositories\v1\Doctor\Specialization\SpecializationInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SpecializationController extends Controller
{
    private $specialization;
    protected $request;

    public function __construct(SpecializationInterface $specialization, Request $request)
    {
        $this->specialization = $specialization;
        $this->request = $request;
    }

    public function create()
    {
        $languages = Language::all();
        return view('admin.specializations.create',compact('languages'));
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $languages = Language::all();

        $specializations = $this->specialization->all([
                'filter'=> 'name LIKE "%'.$this->request->input('name').'%"',
            ]);
        $specializations = $this->specialization->orderBy($specializations,'priority','DESC');
            $specializations = $this->specialization->paginate($specializations,10);
        return view('admin.specializations.index', compact('specializations','languages'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ], [
            'name.required' => 'ورود نام تخصص الزامی است'
        ]);
        $sp = $this->specialization->store($request->all());
        if ($sp instanceof Specialization) {
            if ($request->file('svg_url')) {
                $svg_url = $this->uploadImageCt('svg_url', 'icon');
                $sp->svg_url = $svg_url;
                $sp->save();
            }
            return redirect()->back()->with('success', 'تخصص جدید با موفقیت ثبت شد');
        } else
            return redirect()->back()->with('error', 'ثبت اطلاعات با مشکل مواجه شده است');
    }

    public function edit(Specialization $sp)
    {
        return view('admin.specializations.edit', compact('sp'));
    }

    public function update(Specialization $sp)
    {
        $data = \request()->all();
        $data['name'] = trim($data['name']);
        $data['name'] = str_replace('  ', ' ', $data['name']);
        if ($data['slug']) {
            $data['slug'] = str_replace(' ', '-', $data['slug']);
        }
        if (!$data['slug']) {
            $data['slug'] = str_replace(' ', '-', $data['name']);
        }
        $response = $this->specialization->update($sp, $data);
        if ($response['status']) {
            if (request()->file('svg_url')) {
                $svg_url = $this->uploadImageCt('svg_url', 'icon');
                $sp->svg_url = $svg_url;
                $sp->save();
            }
            return redirect()->back()->with(['success' => 'ویرایش با موفقیت انجام شد']);
        }
        return redirect()->back()->with(['error' => 'انجام نشد']);
    }

    public function destroy(Specialization $sp)
    {
        $response = $this->specialization->delete($sp);
        if ($response['status'])
            return redirect()->back()->with(['success' => 'تخصص با موفقیت حذف شد']);
        return redirect()->back()->with(['error' => 'انجام نشد']);
    }
}
