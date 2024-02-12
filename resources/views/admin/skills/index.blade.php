@extends('admin.layouts.app')

@section('page_name', 'لیست مهارت ها')

@section('content')
    {{--    <div class="white-box">--}}
    {{--        <div class="portlet-body">--}}
    {{--            <div class="col-sm-12">--}}
    {{--                <p style="font-size: 16px">خروجی اکسل</p>--}}

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
    {{--                        <label for="filter_name">نام و نام خانوادگی	</label>--}}
    {{--                        <input type="text" class="form-control" id="filter_name" name="filter_name" value="{{ @$_GET['filter_name'] }}">--}}
    {{--                    </div>--}}
    {{--                </div>--}}

    {{--                <div class="col-md-3 col-xs-12">--}}
    {{--                    <div class="form-group">--}}
    {{--                        <label for="filter_mobile">شماره موبایل</label>--}}
    {{--                        <input type="text" class="form-control" id="filter_mobile" name="filter_mobile" value="{{ @$_GET['filter_mobile'] }}">--}}
    {{--                    </div>--}}
    {{--                </div>--}}

    {{--                <div class="col-md-3 col-xs-12">--}}
    {{--                    <div class="form-group">--}}
    {{--                        <label for="filter_status">وضعیت</label>--}}
    {{--                        <select  id="filter_status" class="form-control" name="filter_status">--}}
    {{--                            <option value="">لطفا انتخاب کنید</option>--}}
    {{--                            <option value="active" {{ (@$_GET['filter_status'] == 'active') ? 'selected' : '' }}>فعال</option>--}}
    {{--                            <option value="inactive" {{ (@$_GET['filter_status'] == 'inactive') ? 'selected' : '' }}>غیر فعال</option>--}}
    {{--                        </select>--}}
    {{--                    </div>--}}
    {{--                </div>--}}

    {{--                <div class="col-md-3 col-xs-12">--}}
    {{--                    <div class="form-group">--}}
    {{--                        <label for="filter_doctor_status">وضعیت اطلاعات پنل</label>--}}
    {{--                        <select  id="filter_doctor_status" class="form-control" name="filter_doctor_status">--}}
    {{--                            <option value="">لطفا انتخاب کنید</option>--}}
    {{--                            <option value="active" {{ (@$_GET['filter_doctor_status'] == 'active') ? 'selected' : '' }}>تایید</option>--}}
    {{--                            <option value="inactive" {{ (@$_GET['filter_doctor_status'] == 'inactive') ? 'selected' : '' }}>معلق</option>--}}
    {{--                            <option value="failed" {{ (@$_GET['filter_doctor_status'] == 'failed') ? 'selected' : '' }}>رد شده</option>--}}
    {{--                        </select>--}}
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


    <form id="myForm" class="filter_list" method="get" action="{{route('skill.index')}}" style="direction: rtl">

        <div class="col-md-3 col-xs-12">
            <div class="form-group">
                <label for="filter_user">نام مهارت	</label>
                <input type="text" class="form-control" id="filter_user" name="name" value="{{ @$_GET['name'] }}">
            </div>
        </div>
        <div class="col-sm-2 col-xs-12" style="margin-top: 25px;">
            <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
        </div>
        <div class="clearfix"></div>

    </form>

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام</th>
                        <th>نامک</th>
{{--                        <th>پزشکان مرتبط</th>--}}
                        <th>اعمال</th>
                    </tr>
                    @foreach($skills as $skill)
                        <tr role="row" class="filter">
                            <td>{{$skill->name}}</td>
                            <td>{{$skill->sience_name}}</td>
{{--                            <td>{{$skill->users()->count()}}</td>--}}
                            <td>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{route('skill.edit',$skill)}}"> ویرایش</a>
                                </div>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-warning btn-rounded request_but"
                                       href="{{route('skill.destroy',$skill)}}"> حذف</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $skills->links() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
