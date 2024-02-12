<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Model\Doctor\Specialization;
use App\Model\Language;
use App\Model\Doctor\Skill;
use App\Repositories\v1\Doctor\Specialization\SpecializationInterface;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SkillController extends Controller
{
    private $skill;
    protected $request;

    public function __construct(Skill $skill, Request $request)
    {
        $this->skill = $skill;
        $this->request = $request;
    }

    public function create()
    {
        return view('admin.skills.create');
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $skills = Skill::where('name', 'LIKE', "%".$this->request->input('name')."%")
            ->orderBy('id','DESC')->paginate(50);
//        $skill = $this->skill->orderBy($skill,'priority','DESC');
//        $skill = $this->skill->paginate($skill,10);
        return view('admin.skills.index', compact('skills'));
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
            'name.required' => 'ورود نام مهارت الزامی است'
        ]);
        $skill = $this->skill->create($request->all());
        if ($skill instanceof Skill) {
            return redirect()->back()->with('success', 'مهارت جدید با موفقیت ثبت شد');
        } else {
            return redirect()->back()->with('error', 'ثبت اطلاعات با مشکل مواجه شده است');
        }
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Skill $skill)
    {
        $data = \request()->all();
        $data['name'] = trim($data['name']);
        $data['name'] = str_replace('  ', ' ', $data['name']);
        $response = $skill->update($data);
        if ($response) {
            return redirect()->back()->with(['success' => 'ویرایش با موفقیت انجام شد']);
        }
        return redirect()->back()->with(['error' => 'انجام نشد']);
    }

    public function destroy(Skill $skill)
    {
        $users_skills = User::where('approve',1)->get();

        $skill_json = '{"value":'.$skill->id.',"label":"'.$skill->name.'"}';

        foreach ($users_skills as $users_skill){

            if (str_contains($users_skill->skill_json, $skill_json)){

                $new_skill_json = str_replace($skill_json,'',$users_skill->skill_json);

                $users_skill->skill_json = str_replace('[,','[', str_replace(',,',',',$new_skill_json));

                $users_skill->save();

            }

        }
        $response = $skill->delete();
        if ($response)
            return redirect()->back()->with(['success' => 'مهارت با موفقیت حذف شد']);
        return redirect()->back()->with(['error' => 'انجام نشد']);
    }
}
