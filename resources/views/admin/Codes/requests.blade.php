
@extends('admin.layouts.app')

@section('page_name', 'لیست کد ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="mobile">موبایل یا ایمیل</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="{{ @$_GET['mobile'] }}">
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
@if($item)
{{--    @if($item && $item[0])--}}
    <div class="col-sm-2 col-xs-12" style="padding-top: 17px;padding-bottom: 17px;">
        <a href="{{route('user.delete.code',['mobile'=>@$_GET['mobile']])}}" class="btn btn-block btn-success btn-rounded">رفع بلاک امروز</a>
    </div>
{{--    @endif--}}
        <div class="white-box">
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr role="row" class="heading">
                            <th> موبایل یا ایمیل</th>
                            <th>کد</th>
                            <th>توضیحات</th>
                            <th>تاریخ درخواست</th>
                        </tr>
                        @foreach($item as $it)
                            <tr role="row" class="filter">
                                <td>{{$it->mobile}}</td>
                                <td>{{$it->code}}</td>
                                <td>{{$it->message}}</td>
                                <td>{{jdate('d F Y ساعت H:i:s' , strtotime($it->created_at))}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>

            </div>

        </div>
@endif
@endsection
