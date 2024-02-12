@extends('admin.layouts.app')

@section('page_name', 'ویزیت های بی پزشک')

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
                        <th>اعمال</th>
                    </tr>
                    @foreach($meetings as $visit)
                        <tr role="row" class="filter">
                            <td>{{$visit->doctor->name.' '.$visit->doctor->family}}</td>
                            <td>{{$visit->doctor->mobile}}</td>
                            <td>{{$visit->user->name.' '.$visit->user->family}}</td>
                            <td>{{$visit->user->mobile}}</td>
                            <td>{{ $visit->reserve_time ? jdate('Y/m/d ساعت H:i:s' , strtotime($visit->reserve_time)) : '--'}}</td>
                            <td>{{ $visit->last_activity_user ? jdate('Y/m/d ساعت H:i:s' , strtotime($visit->last_activity_user)) : '--'}}</td>
                            <td>{{$visit->UserTransaction('paid')->first() ?
 $visit->UserTransaction('paid')->first()->amount : 'یافت نشد'}}</td>
                            <td>{{$visit->DoctorMessages()->count()}}</td>
                            <td>
                                <form method="POST"
                                      action="{{route('absence.refund',['user'=>$visit->user_id,'er'=>$visit->id])}}">
                                    {{csrf_field()}}
                                    <div class="col-xs-6">
                                        <button type="submit"
                                                class="btn btn-block btn-info btn-rounded request_but" style="font-size: 12px;white-space: normal">
                                            بازگشت وجه
                                        </button>
                                    </div>
                                </form>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-danger btn-rounded request_but"
                                       href="{{route('cancel.refund',$visit->id)}}" style="font-size: 12px;white-space: normal">
                                        عدم موافقت
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $meetings->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
