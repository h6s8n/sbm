@extends('admin.layouts.app')

@section('page_name', 'درخواست های ثبت مراکز مجازی')

@section('content')

    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="applicant_name">نام درخواست کننده </label>
                        <input type="text" class="form-control" id="applicant_name" name="applicant_name"
                               value="{{ @$_GET['applicant_name'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="phone">تلفن</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="{{ @$_GET['phone'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="partner_name">نام مرکز</label>
                        <input type="text" class="form-control" id="partner_name" name="partner_name"
                               value="{{ @$_GET['partner_name'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="called">وضعیت</label>
                        <select id="called" class="form-control" name="called">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="1" {{ (@$_GET['called'] == '1') ? 'selected' : '' }}>
                                بررسی شده
                            </option>
                            <option value="0" {{ (@$_GET['called'] == '0') ? 'selected' : '' }}>
                                بررسی نشده
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="from">از تاریخ</label>
                        <input type="text" class="form-control observer" name="from">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to">تا تاریخ</label>
                        <input type="text" class="form-control observer" name="to">
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <hr>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        تعداد کل رکوردها : {{ number_format($requests ? $requests->total() : 0) }} عدد
                    </div>
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
                        <th></th>
                        <th>نام مرکز</th>
                        <th>تلفن</th>
                        <th>نام درخواست کننده</th>
                        <th>سمت درخواست کننده</th>
                        <th>تعداد پزشکان مرکز</th>
                        <th>وضعیت</th>
                        <th>توضیحات</th>
                        <th>تاریخ درخواست</th>
                        <th>عملیات</th>
                    </tr>
                    @php $row_count =1 @endphp
                    @foreach($requests as $request)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td>{{$request->partner_name}}</td>
                            <td><a href="tel:{{$request->phone}}" title="تماس">{{$request->phone}}</a></td>
                            <td>{{$request->applicant_name}}</td>
                            <td>{{$request->applicant_post}}</td>
                            <td>{{$request->total_doctors}}</td>
                            <td>{{$request->called ? 'بررسی شده' : 'بررسی نشده'}}</td>
                            <td>{!! $request->description ?? '---' !!}</td>
                            <td>{{$request->created_at}}</td>
                            <td>
                                @if(!$request->called)
                                    <div class="col-xs-8">
                                        <a class="btn btn-block btn-primary btn-rounded request_but "
                                           href="{{route('registration-request.edit',$request->id)}}">تماس برقرار شد</a>
                                    </div>
                                @endif
                                    <div class="col-xs-4">
                                        <a class="btn btn-block btn-danger btn-rounded request_but" href="{{ route('registration-request.destroy',$request->id) }}"> حذف </a>
                                    </div>
                            </td>
                        </tr>
                        @php $row_count =$row_count+1 @endphp
                    @endforeach

                </table>
            </div>
            {!! $requests->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
