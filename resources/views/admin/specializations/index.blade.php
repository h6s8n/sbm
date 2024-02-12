@extends('admin.layouts.app')

@section('page_name', 'لیست تخصص ها')

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


    <form id="myForm" class="filter_list" method="get" action="{{route('specialization.index')}}" style="direction: rtl">

        <div class="col-md-3 col-xs-12">
            <div class="form-group">
                <label for="filter_user">نام تخصص	</label>
                <input type="text" class="form-control" id="filter_user" name="name" value="{{ @$_GET['name'] }}">
            </div>
        </div>
        <div class="col-md-3 col-xs-12">
            <div class="form-group">
                <label for="lang">زبان تخصص</label>
                <select name="lang" id="lang" class="form-control">
                    @foreach($languages as $language)
                        <option value="{{$language->slug}}" {{@$_GET['lang'] == $language->slug ? 'selected' : ''}}>{{$language->name}}</option>
                    @endforeach
                </select>
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
                        <th>زبان</th>
                        <th>پزشکان مرتبط</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($specializations as $sp)
                        <tr role="row" class="filter">
                            <td>{{$sp->name}}</td>
                            <td>{{$sp->slug}}</td>
                            <td>
                                {{$sp->language()->first()->name}}
                            </td>
                            <td>{{$sp->users()->count()}}</td>
                            <td>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{route('specialization.edit',$sp)}}"> ویرایش</a>
                                </div>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-warning btn-rounded request_but"
                                       href="{{route('specialization.destroy',$sp)}}"> حذف</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $specializations->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
