@extends('admin.layouts.app')

@section('page_name', 'درخواست های تبلیغات')

@section('content')
    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>عنوان تبلیغ</th>
                        <th>حوزه فعالیت</th>
                        <th>عنوان خدمت / جایگاه تبلیغ</th>
                        <th>نام درخواست کننده</th>
                        <th>موبایل درخواست کننده</th>
                        <th>وضعیت درخواست</th>
                        <th>وضعیت پرداخت</th>
                        <th>تاریخ درخواست</th>
                        <th>تاریخ پرداخت</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($advertising as $ad)
                        <tr role="row" class="filter">
                            <td>{{$ad->title}}</td>
                            <td>{{$ad->subject}}</td>
                            <td>{{$ad->plan}}</td>
                            <td>{{$ad->fullname}}</td>
                            <td>
                                <a href="tel:{{$ad->mobile}}" title="تماس">
                                {{$ad->mobile}}
                                </a>
                            </td>
                            <td class="@if($ad->status == 'ثبت شده') success @else warning @endif">{{ $ad->status }}</td>
                            <td class="@if($ad->payment_status == 'پرداخت شده') success @else danger @endif">{{ $ad->payment_status }}</td>
                            <td>{{$ad->created_at}}</td>
                            <td>{{$ad->paid_at}}</td>

                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{ route('advertising.edit',$ad) }}"> تغییر وضعیت</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $advertising->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
