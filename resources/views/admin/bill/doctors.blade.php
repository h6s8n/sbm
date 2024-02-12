@extends('admin.layouts.app')

@section('page_name', ' صورت حساب مالی پزشکان ')

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
                        <label for="filter_user">نام و نام خانوادگی	</label>
                        <input type="text" class="form-control" id="filter_user" name="filter_user" value="{{ @$_GET['filter_user'] }}">
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

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_status">وضعیت پرداخت</label>
                        <select  id="filter_status" class="form-control" name="filter_status">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="pending" {{ (@$_GET['filter_status'] == 'pending') ? 'selected' : '' }}>درحال بررسی</option>
                            <option value="paid" {{ (@$_GET['filter_status'] == 'paid') ? 'selected' : '' }}>پرداخت شد</option>
                            <option value="cancel" {{ (@$_GET['filter_status'] == 'cancel') ? 'selected' : '' }}>لغو شد</option>
                        </select>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post">
        {{ csrf_field() }}
        <div class="white-box">
            <div class="portlet-body">

                <div class="row">

                    <div class="col-sm-2 col-xs-12">
                        <button type="submit" name="status" value="paid" class="btn btn-block btn-success btn-rounded">پرداخت شد</button>
                    </div>

                    <div class="col-sm-2 col-xs-12">
                        <button type="submit" name="status" value="pending" class="btn btn-block btn-warning btn-rounded">درحال بررسی</button>
                    </div>

                    <div class="col-sm-2 col-xs-12">
                        <button type="submit" name="status" value="cancel" class="btn btn-block btn-danger btn-rounded">لغو شد</button>
                    </div>

                </div>

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
                            <th>اعمال</th>
                            <th>شماره صورت حساب</th>
                            <th>نام پزشک</th>
                            <th>تاریخ </th>
                            <th> مبلغ کل (ریال)</th>
                            <th> وضعیت پرداخت</th>
                        </tr>
                        @php $row_count =1 @endphp
                        @foreach($request as $k => $item)
                            <tr role="row" class="filter">
                                <td>{{$row_count}}</td>
                                <td>
                                    <div class="checkbox checkbox-info">
                                        <div style="display: inline-block; margin-right: 15px">
                                            <input id="checkbox{{ $item['id'] }}" type="checkbox" name="doctors[]" value="{{ $item['id'] }}">
                                            <label for="checkbox{{ $item['id'] }}"> انتخاب </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-family: Tahoma">SD{{ $item['id'] }}</td>
                                <td>{{ $item['fullname'] }}</td>
                                <td>{{ jdate('Y/m/d', strtotime($item['created_at'])) }}</td>
                                <td>{{ number_format($item['amount']) }}</td>
                                <td>
                                    @switch($item['status'])
                                        @case('pending')
                                        درحال بررسی
                                        @break
                                        @case('paid')
                                        پرداخت شد
                                        @break
                                        @case('cancel')
                                        لغو شد
                                        @break
                                    @endswitch
                                </td>
                            </tr>
                            @php $row_count = $row_count +1; @endphp
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $request->render() !!}
                <div class="clearfix"></div>

                @else

                    <div class="msg">
                        <div class="alert alert-warning alert-info" style="text-align: center">صورت حسابی یافت نشد.</div>
                    </div>

                @endif


            </div>

        </div>
    </form>
@endsection
