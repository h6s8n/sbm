@extends('admin.layouts.app')

@section('page_name', 'درخواست ها')

@section('content')
    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام و نام خانوادگی</th>
                        <th>پلن</th>
                        <th>شماره تلفن</th>
                        <th>وضعیت درخواست</th>
                        <th>وضعیت پرداخت</th>
                        <th>تاریخ درخواست</th>
                        <th>عملیات</th>
                    </tr>
                    @foreach($badge_requests as $request)
                        <tr role="row" class="filter">
                            <td>
                                <a href="
                                    @if($request->user_id)
                                        {{route('assign.badge',$request->user_id)}}
                                    @else
                                        {{ url('cp-manager/doctors?filter_mobile='.$request->phone) }}
                                    @endif
                                        ">
                                    {{$request->full_name}}
                                </a>
                            </td>
                            <td>
                                <a href="{{route('badge.edit',$request->badge_id)}}">{{$request->plan}}</a>
                            </td>
                            <td><a href="tel:{{ $request->phone }}" title="تماس">{{ $request->phone }}</a></td>
                            <td class="@if($request->status == 'ثبت شده') success @else warning @endif">{{ $request->status }}</td>
                            <td class="@if($request->pay_status == 'پرداخت شده') success @else danger @endif">{{ $request->pay_status }}</td>
                            <td>{{ $request->created_at }}</td>
                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{route('badge-request.edit',$request)}}"> تغییر وضعیت </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $badge_requests->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
