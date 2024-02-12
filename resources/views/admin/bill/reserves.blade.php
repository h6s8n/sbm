@extends('admin.layouts.app')

@section('page_name', ' صورت حساب مالی تراکنش ها ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_code">شماره صورت حساب</label>
                        <input type="text" class="form-control" id="filter_code" name="filter_code" value="{{ @$_GET['filter_code'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="pay_status">وضعیت پرداخت</label>
                        <select id="pay_status" class="form-control" name="filter_pay_status">
                            <option value="">لطفا انتخاب کنید</option>
                                <option value="paid" {{@$_GET['filter_pay_status'] == 'paid' ? 'selected' : ''}}>پرداخت شده</option>
                                <option value="pending" {{@$_GET['filter_pay_status'] == 'pending' ? 'selected' : ''}}>پرداخت نشده</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_user">نام پزشک</label>
                        <input type="text" class="form-control" id="filter_user" name="filter_user" value="{{ @$_GET['filter_user'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_user_username">نام بیمار</label>
                        <input type="text" class="form-control" id="filter_user_username" name="filter_user_username" value="{{ @$_GET['filter_user_username'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_start_date">از تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_start_date" name="filter_start_date" value="{{ @$_GET['filter_start_date'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تا تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_end_date" name="filter_end_date" value="{{ @$_GET['filter_end_date'] }}">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <hr>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        تعداد کل رکوردها : {{ number_format($request ? $request->total() : 0) }} عدد
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>

    <div class="white-box">

        <div class="portlet-body">

            @if(count($request) > 0)
            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tbody>
                    <tr role="row" class="heading">
                        <th></th>
                        <th>ساعت درخواست</th>
                        <th>نام پزشک</th>
                        <th>نام بیمار</th>
                        <th>تاریخ نوبت </th>
                        <th>زمان نوبت </th>
                        <th>وضعیت پرداخت </th>
                        <th>شناسه پرداخت </th>
                        <th> قیمت کل (ریال)</th>
                        <th> اعتبار مصرفی (ریال)</th>
                        <th> قیمت پرداخت شده (ریال)</th>
                        <th>شرح صورت حساب</th>
                        <th>عملیات</th>
                    </tr>
                    @php $row_count =1 @endphp

                    @foreach($request as $k => $item)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td style="font-family: Tahoma">{{ $item['created_time'] }}</td>
                            <td>{{ $item['dr_name'] }}</td>
                            <td>{{ $item['user_name']}}<br>
                                @if($item['mobile'])
                                <a href="tel:{{$item['mobile']}}">{{ $item['mobile'] }}</a>
                                @else
                                <a href="mailto:{{$item['email']}}">{{ $item['email'] }}</a>
                                @endif
                            </td>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['time'] . ' الی ' . ($item['time'] + 1) }}</td>
                            <td>{{ $item['pay_status'] == 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</td>
                            <td>{{ $item['transId'] }}</td>
                            <td>{{ number_format($item['amount']) }}</td>
                            <td>{{ number_format(($item['amount'] - $item['amount_paid'])) }}</td>
                            <td>{{ number_format($item['amount_paid']) }}</td>
                            <td>{{ $item['message'] }}</td>
                            <td>
                                @unless($item['message'])
                                    <div class="col-xs-12">
                                        <a class="btn btn-block btn-primary btn-rounded request_but "
                                           href="{{route('transactionReserve.edit',$item['id'])}}">تماس برقرار شد</a>
                                    </div>
                                @endunless
                            </td>
                        </tr>
                        @php $row_count = $row_count +1; @endphp
                    @endforeach
                    </tbody>
                </table>
            </div>
{{--            {!! $request->render() !!}--}}
                {!! $request->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}

                <div class="clearfix"></div>

            @else

                <div class="msg">
                    <div class="alert alert-warning alert-info" style="text-align: center">صورت حسابی یافت نشد.</div>
                </div>

            @endif


        </div>

    </div>
@endsection
