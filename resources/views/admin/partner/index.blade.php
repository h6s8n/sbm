@extends('admin.layouts.app')

@section('page_name', 'لیست بیمارستان ها')

@section('content')
{{--    <div class="white-box">--}}
{{--        <div class="portlet-body">--}}
{{--            <div class="col-sm-12">--}}
{{--                <p style="font-size: 16px">خروجی اکسل</p>--}}

{{--            </div>--}}
{{--            <div class="col-sm-2 col-xs-6">--}}
{{--                <a href="{{ url('cp-manager/user/export-users') }}" class="btn btn-block btn-info btn-rounded">همه--}}
{{--                    کاربران</a>--}}
{{--            </div>--}}
{{--            <div class="col-sm-2 col-xs-6">--}}
{{--                <a href="{{ url('cp-manager/user/export-inactive') }}" class="btn btn-block btn-info btn-rounded">کاربران--}}
{{--                    فعال و بدون وقت</a>--}}
{{--            </div>--}}
{{--            <div class="clearfix"></div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="white-box">--}}
{{--        <div class="portlet-body">--}}
{{--            <div class="clearfix"></div>--}}
{{--            <br>--}}

{{--            <form class="filter_list" method="get" style="direction: rtl">--}}

{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_name">نام و نام خانوادگی </label>--}}
{{--                        <input type="text" class="form-control" id="filter_name" name="filter_name"--}}
{{--                               value="{{ @$_GET['filter_name'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_mobile">شماره موبایل</label>--}}
{{--                        <input type="text" class="form-control" id="filter_mobile" name="filter_mobile"--}}
{{--                               value="{{ @$_GET['filter_mobile'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_status">وضعیت</label>--}}
{{--                        <select id="filter_status" class="form-control" name="filter_status">--}}
{{--                            <option value="">لطفا انتخاب کنید</option>--}}
{{--                            <option value="active" {{ (@$_GET['filter_status'] == 'active') ? 'selected' : '' }}>فعال--}}
{{--                            </option>--}}
{{--                            <option value="inactive" {{ (@$_GET['filter_status'] == 'inactive') ? 'selected' : '' }}>غیر--}}
{{--                                فعال--}}
{{--                            </option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_credit">اعتبار</label>--}}
{{--                        <select  id="filter_credit" class="form-control" name="filter_credit">--}}
{{--                            <option value="">لطفا انتخاب کنید</option>--}}
{{--                            <option value="1" {{ (@$_GET['filter_credit'] == '1') ? 'selected' : '' }}>دارد</option>--}}
{{--                            <option value="0" {{ (@$_GET['filter_credit'] == '0') ? 'selected' : '' }}>ندارد</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_email">ایمیل</label>--}}
{{--                        <input type="text" class="form-control" id="filter_email" name="filter_email" value="{{ @$_GET['filter_email'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="clearfix"></div>--}}
{{--                <div class="col-sm-2 col-xs-12">--}}
{{--                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>--}}
{{--                </div>--}}
{{--                <div class="clearfix"></div>--}}
{{--            </form>--}}
{{--        </div>--}}
{{--    </div>--}}

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th></th>
                        <th>نام </th>
                        <th>تلفن</th>
                        <th>نامک</th>
                        <th>موقعیت</th>
                        <th>زمان ها</th>
                        <th>درصد دکتر</th>
                        <th>درصد بیمارستان</th>
                        <th></th>
                    </tr>
                    @php $row_count =1 @endphp
                    @foreach($partners as $partner)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td>{{$partner->name}}</td>
                            <td>{{$partner->phone}}</td>
                            <td>{{$partner->slug}}</td>
                            <td>{{$partner->location}}</td>
                            <td>{{$partner->times}}</td>
                            <td>{{$partner->doctor_percent}}</td>
                            <td>{{$partner->partner_percent}}</td>
                            <td>
                                <div style="display: flex;flex-wrap: wrap;justify-content: space-between;">
                                <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                    <a href="{{route('partner.edit',$partner->id)}}" class="btn btn-warning btn-block">ویرایش</a>
                                </div>
                                <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                    <a href="{{route('partner.doctors',$partner->id)}}" class="btn btn-info btn-block">پزشکان</a>
                                </div>
                                <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                    <a href="{{url('/cp-manager/bill/doctor/no_end?filter_partner='. $partner->id)}}" class="btn btn-danger btn-block">وقت ها</a>
                                </div>
                                <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                    <a href="{{'https://sbm24.com/h/' . $partner->slug}}" target="_blank" class="btn btn-success btn-block">نمایش</a>
                                </div>
                                <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                    <a href="{{route('partner.batch.finish',$partner->id)}}"  class="btn btn-warning btn-block">خاتمه دسته ای</a>
                                </div>
                                    <div class="col-xs-12 col-md-6" style="padding: 5px;">
                                        <a href="{{route('partner.batch.calendar',$partner)}}"  class="btn btn-primary btn-block">ثبت وقت جمعی</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @php $row_count =$row_count+1 @endphp
                    @endforeach

                </table>
            </div>
            {!! $partners->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
