@extends('admin.layouts.app')

@section('page_name', ' صورت حساب مالی پزشکان ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>
            <div class="alert alert-danger">
                <ul>
                    <li>لطفا در انخاب بازه زمانی دقت نمایید</li>
                    <li>سایت مپ مورد نظر برای تمام پزشکان ایپمورت شده، در بازه زمانی انتخابی ساخته می شود</li>
                    <li>تگ lastmod به صورت پیش فرض تاریخ ثبت پزشک در نظر گرفته شده در صورت نیاز میتوانید تاریخ دلخواه را وارد کنید</li>
                    <li>تگ changefreq به صورت پیش فرض weekly در نظر گرفته شده در صورت نیاز میتوانید مقدار دلخواه را وارد کنید</li>
                </ul>
            </div>
            <form id="myForm"  method="post" action="{{route('sitemap.make')}}" style="direction: rtl">
                {{csrf_field()}}
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_start_date">از تاریخ</label>
                        <input type="date" class="form-control" name="from" required>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تا تاریخ</label>
                        <input type="date" class="form-control" name="to" required>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تگ lastmod</label>
                        <input type="date" class="form-control" name="lastmod">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تگ changefreq</label>
                        <input type="text" class="form-control" name="changefreq" style="direction: ltr" >
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="type">
                        <input type="radio" id="type" name="type" value="imported" checked>پزشکان ایمپورت شده</label>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="typea">
                            <input type="radio" id="typea" name="type" value="active">پزشکان ثبت نامی</label>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">ایجاد</button>
                    {{--                    <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">خروجی اکسل</button>--}}
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post">
        <div class="white-box">

            <div class="portlet-body">
                <div class="col-xs-6">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <td></td>
                                <td>از</td>
                                <td>تا</td>
                                <td>تعداد</td>
                                <td></td>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($sitemaps as $sitemap)
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{$sitemap->from_}}</td>
                                    <td>{{$sitemap->to_}}</td>
                                    <td>{{$sitemap->amount}}</td>
                                    <td>
                                        <a class="btn btn-primary" href="{{$sitemap->path}}">مشاهده</a>
                                    </td>
                                </tr>
                                @php $row_count = $row_count+1; @endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <td>تاریخ</td>
                                <td>تعداد</td>
                            </tr>
                            @foreach($logs as $log)
                                <tr role="row" class="filter">
                                    <td>{{$log->created_at}}</td>
                                    <td>{{$log->amount}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                    <div class="clearfix"></div>


            </div>

        </div>
    </form>

@endsection
