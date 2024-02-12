
@extends('admin.layouts.app')

@section('page_name', 'امتیاز ویزیت ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_user">نام پزشک	</label>
                        <input type="text" class="form-control" id="filter_user" name="filter_user" value="{{ @$_GET['filter_user'] }}">
                    </div>
                </div>
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_br">نام بیمار	</label>--}}
{{--                        <input type="text" class="form-control" id="filter_br" name="filter_br" value="{{ @$_GET['filter_br'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_start_date">از تاریخ</label>--}}
{{--                        <input type="text" class="form-control observer" id="filter_start_date" name="filter_start_date" value="{{ @$_GET['filter_start_date'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_end_date">تا تاریخ</label>--}}
{{--                        <input type="text" class="form-control observer" id="filter_end_date" name="filter_end_date" value="{{ @$_GET['filter_end_date'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_visit_status">وضعیت ویزیت</label>--}}
{{--                        <select  id="filter_visit_status" class="form-control" name="filter_visit_status">--}}
{{--                            <option value="">لطفا انتخاب کنید</option>--}}
{{--                            <option value="end" {{ (@$_GET['filter_visit_status'] == 'end') ? 'selected' : '' }}>پایان ویزیت</option>--}}
{{--                            <option value="not_end" {{ (@$_GET['filter_visit_status'] == 'not_end') ? 'selected' : '' }}>مجاز برای ویزیت</option>--}}
{{--                            <option value="cancel" {{ (@$_GET['filter_visit_status'] == 'cancel') ? 'selected' : '' }}>کنسل شده</option>--}}
{{--                            <option value="refunded" {{ (@$_GET['filter_visit_status'] == 'refunded') ? 'selected' : '' }}>برگشت شده به بیمار</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}

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
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <th></th>
                                <th> پزشک</th>
                                <th> بیمار</th>
                                <th> تاریخ ویزیت</th>
                                <th> امتیاز ثبت شده توسط پزشک</th>
                                <th> امتیاز ثبت شده توسط بیمار</th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($rates as $rate)
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{$rate->doctor->fullname.' - '.$rate->doctor->mobile}}</td>
                                    <td>{{$rate->user->fullname.' - '.$rate->user->mobile}}</td>
                                    <td>{{jdate('Y-m-d' , strtotime($rate->data))}}</td>
                                    <td>{{$rate->DoctorRate->rate}}</td>
                                    <td>{{$rate->UserRate->rate}}</td>
                                </tr>
                                @php $row_count = $row_count +1;@endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $rates->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    <div class="clearfix"></div>

            </div>

        </div>
    </form>

@endsection
