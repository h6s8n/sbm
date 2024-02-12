@extends('admin.layouts.app')

@section('page_name', 'لیست پزشکان ' . $partner->name)

@section('content')

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام و نام خانوادگی</th>
                        <th>ایمیل</th>
                        <th>شماره موبایل</th>
                        <th>تاریخ عضویت</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($partner->doctors as $pa)
                        <tr role="row" class="filter">
                            <td>{{ ($pa->fullname) ? $pa->doctor_nickname . ' ' . $pa->fullname : '-' }}</td>
                            <td>{{$pa->email}}</td>
                            <td>{{$pa->mobile}}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s', strtotime($pa->created_at)) }}</td>
                            <td>
                                <div class="col-xs-12 col-md-6">
                                    <a href="{{url('cp-manager/calenders/?partner=' . $partner->id . '&doctor=' . $pa->id)}}" class="btn btn-warning btn-block">مشاهده زمان بندی</a>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <a class="btn btn-block btn-info" href="{{ url('cp-manager/doctor/edit/' . $pa->id) }}" style="white-space: normal"> ویرایش </a>
                                </div><hr>
{{--                                @if($pa->DoctorEvents()->count() == 0)--}}
{{--                                <div class="col-xs-12 col-md-12">--}}
{{--                                    <a class="btn btn-block btn-danger" href="{{ route('delete.all.calendars',['user'=>$pa->id,'partner'=>$partner->id]) }}" style="white-space: normal"> حذف تمام وقت های خالی بیمارستانی</a>--}}
{{--                                </div>--}}
{{--                                    @endif--}}
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
