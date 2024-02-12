@php require_once (base_path('app/jdf.php')); @endphp
@extends('admin.layouts.app')

@section('page_name', 'درخواست اعتبار')

@section('content')
    <div class="white-box">
        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>کاربر</th>
                        <th>شماره موبایل </th>
                        <th>شماره شبا</th>
                        <th>اعتبار</th>
                        <th>مبلغ موردنظر</th>
                        <th>تاریخ درخواست</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($refunds as $refund)
                        <tr role="row" class="filter">
                            <td>{{$refund->user->fullname}}</td>
                            <td>{{$refund->user->mobile}}</td>
                            <td>{{$refund->user->account_sheba}}</td>
                            <td>{{number_format($refund->user->credit)}}</td>
                            <td>{{number_format($refund->amount)}}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s' , strtotime($refund->created_at))}}</td>
                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-success btn-rounded request_but"
                                       href="" style="font-size: 12px;white-space: normal">
                                        تصمیم گیری
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $refunds->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
