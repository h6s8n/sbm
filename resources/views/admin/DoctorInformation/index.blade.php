@extends('admin.layouts.app')

@section('page_name', 'اطلاعات پزشکان')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نام و نام خانوادگی </label>
                        <input type="text" class="form-control" id="filter_name" name="filter_name"
                               value="{{ @$_GET['filter_name'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_mobile">شماره موبایل</label>
                        <input type="text" class="form-control" id="filter_mobile" name="filter_mobile"
                               value="{{ @$_GET['filter_mobile'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_email">ایمیل</label>
                        <input type="text" class="form-control" id="filter_email" name="filter_email"
                               value="{{ @$_GET['filter_email'] }}">
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
                        <th>منشی مطب</th>
                        <th>تلفن منشی مطب</th>
                        <th>منشی ویژه</th>
{{--                        <th>توضیحات دایمی</th>--}}
{{--                        <th>توضیحات موقت</th>--}}
                        <th>اعمال</th>
                    </tr>

                    @foreach($information as $inf)
                        <tr role="row" class="filter">
                            <td>{{$inf->doctor->fullname}}</td>
                            <td>{{$inf->office_secretary_name}}</td>
                            <td>{{$inf->office_secretary_mobile}}</td>
                            <td>{{$inf->doctor->secretary ? $inf->doctor->secretary->full_name : 'وارد نشده'}}</td>
{{--                            <td>{{$inf->permanent_comment}}</td>--}}
{{--                            <td>{{$inf->temporary_comment}}</td>--}}
                            <td>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-info btn-rounded request_but"
                                       href="{{route('doctor.information.create',$inf->doctor)}}"
                                       style="white-space: normal"> ویرایش </a>
                                    <hr>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $information->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
