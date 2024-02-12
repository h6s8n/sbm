@extends('partnerPanel.layouts.app')

@section('page_name', 'برنامه پزشکان')

@section('content')
    <div class="white-box">
        <div class="portlet-body">

            <div class="row">

                <div class="col-sm-2 col-xs-12">
                    @if(isset($_GET['partner']) && !empty($_GET['partner']) && !empty($_GET['doctor']))
                        <a href="{{ url('cp-partner/calender/add?partner=' . $_GET['partner'] . '&user_id='. $_GET['doctor']) }}"
                           class="btn btn-block btn-info btn-rounded">افزودن برنامه جدید</a>
                    @else
                        <a href="{{ url('cp-partner/calender/add') }}" class="btn btn-block btn-info btn-rounded">افزودن برنامه جدید</a>
                    @endif
                </div>

            </div>

        </div>
    </div>

    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نام و نام خانوادگی	</label>
                        <input type="text" class="form-control" id="filter_name" name="filter_name" value="{{ @$_GET['filter_name'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_date">تاریخ</label>
                        <input type="text" class="form-control" id="filter_date" name="filter_date" placeholder="{{ jdate('Y-m-d') }}" value="{{ @$_GET['filter_date'] }}">
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>



    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام و نام خانوادگی</th>
                        <th>تاریخ</th>
                        <th>روز</th>
                        <th>ساعت</th>
                        <th>ظرفیت</th>
                        <th>رزرو شده</th>
                        <th>مبلغ</th>
                        <th>اعمال</th>
                    </tr>
{{--                    {{dd(\Carbon\Carbon::getDays())}}--}}
                    @foreach($request as $k => $item)
                        <tr role="row" class="filter">
                            <td>{{ ($item['fullname']) ? $item['fullname'] : '-' }}</td>
                            <td>{{ $item['fa_data'] }}</td>
                            <td>{{DayOfWeek(\Carbon\Carbon::parse(($item['data']))->dayOfWeek)}}</td>
                            <td>{{ $item['time'] . ' الی ' . ($item['time'] + 1) }}</td>
                            <td>{{ number_format($item['capacity']) }}</td>
                            <td>{{ number_format($item['reservation']) }}</td>
                            <td>{{ number_format($item['price']) }} ریال</td>
                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-danger btn-rounded request_but" href="{{ url('cp-partner/calender/delete/' . $item['id']) }}"> حذف </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $request->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
