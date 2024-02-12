<?php

namespace App\Http\Controllers\Admin\Visit;

use App\Http\Controllers\Admin\VisitController;
use App\Model\Visit\VisitAction;
use App\Repositories\v2\Visit\VisitLogRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VisitActionController extends Controller
{
    public function __construct()
    {

    }
    public function index()
    {
        $actions = VisitAction::where('decision',0)->paginate(10);
        return view('admin.visit.actions',compact('actions'));
    }

    public function decision(VisitAction $action)
    {
        return view('admin.visit.decision',compact('action'));
    }

    public function update(VisitAction $action)
    {
        $decision = \request()->input('decision');
        switch ($decision){
            case 1:{
                $repository = new VisitController(new VisitLogRepository());
                $repository->refund($action->event->user,
                    $action->event()->first());
                break;
            }
            case 2:{
                $repository = new VisitController(new VisitLogRepository());
                $repository->cancelRefund($action->event()->first());
                break;
            }
        }
        $action->decision=$decision;
        $action->description = \request()->input('description');
        $action->last_changed_user_id = auth()->id();
        $action->save();
        return redirect()->route('visit.action');
    }
}
