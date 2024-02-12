@php require_once (base_path('app/jdf.php')); @endphp
@extends('admin.layouts.app')

@section('page_name', 'درخواست ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>دکتر</th>
                        <th>شماره موبایل دکتر</th>
                        <th>کاربر</th>
                        <th>شماره موبایل کاربر</th>
                        <th>تاریخ ویزیت</th>
                        <th>تاریخ درخواست</th>
                        <th>مبلغ پرداختی</th>
                        <th>تعداد پیامهای دکتر</th>
                        <th>نوع درخواست</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($actions as $action)
                        <tr role="row" class="filter">
                            <td>{{$action->event->doctor->name.' '.$action->event->doctor->family}}</td>
                            <td>{{$action->event->doctor->mobile}}</td>
                            <td>{{$action->event->user->name.' '.$action->event->user->family}}</td>
                            <td>{{$action->event->user->mobile}}</td>
                            <td>{{ $action->event->reserve_time ? jdate('Y/m/d ساعت H:i:s' , strtotime($action->event->reserve_time)) : '--'}}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s' , strtotime($action->created_at))}}</td>
                            <td>{{$action->event->UserTransaction('paid')->first() ?
 $action->event->UserTransaction('paid')->first()->amount : 'یافت نشد'}}</td>
                            <td>{{$action->event->DoctorMessages()->count()}}</td>
                            <td>{{\App\Enums\VisitActionsEnum::returnMessage($action->action)}}</td>
                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-success btn-rounded request_but"
                                       href="{{route('visit.action.decision',$action)}}" style="font-size: 12px;white-space: normal">
                                       تصمیم گیری
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $actions->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
