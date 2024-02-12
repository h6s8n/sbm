@extends('admin.layouts.app')
@section('page_name', 'کاربران')
@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>
            <form class="filter_list" method="get" style="direction: rtl">
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نوع تراکنش: </label>
                        <select name="filter_name" id="filter_name" class="form-control">
                            <option value="reserves" {{ @$_GET['filter_name']=='reserves' ? 'selected' : '' }}>ویزیت
                                ها
                            </option>
                            <option value="credits" {{ @$_GET['filter_name']=='credits' ? 'selected' : '' }}>اعتبار
                            </option>
                        </select>
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
    @if($transactions)
        @if(@$_GET['filter_name'] == 'credits')
            <div class="white-box">
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover">
                            <tr role="row" class="heading">
                                <th>نام و نام خانوادگی</th>
                                <th>شماره موبایل</th>
                                <th>تاریخ</th>
                                <th>وضعیت</th>
                                <th>مبلغ</th>
                                <th>توضیحات</th>
                            </tr>
                            @foreach($transactions as $tr)
                                <tr>
                                    <td>{{$tr->user->name.' '.$tr->user->family}}</td>
                                    <td>{{$tr->user->mobile}}</td>
                                    <td>{{jdate('d F Y ساعت H:i', strtotime($tr->created_at))}}</td>
                                    <td>{{$tr->status}}</td>
                                    <td>{{$tr->amount}}</td>
                                    <td>{{$tr->message}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        @elseif(@$_GET['filter_name'] == 'reserves')
            <div class="white-box">
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover">
                            <tr role="row" class="heading">
                                <th>نام و نام خانوادگی</th>
                                <th>شماره موبایل</th>
                                <th>تاریخ</th>
                                <th>وضعیت</th>
                                <th>مبلغ واریزی</th>
                                <th>کسر از اعتبار</th>
                                <th>مبلغ کل</th>
                                <th>دکتر</th>
                                <th>پیام</th>
                                <th>شماره</th>
                            </tr>
                            @foreach($transactions as $tr)
                                <tr>
                                    <td>{{$tr->user->name.' '.$tr->user->family}}</td>
                                    <td>{{$tr->user->mobile}}</td>
                                    <td>{{jdate('d F Y ساعت H:i', strtotime($tr->created_at))}}</td>
                                    <td>{{$tr->status}}</td>
                                    <td>{{$tr->amount_paid}}</td>
                                    <td>{{$tr->used_credit}}</td>
                                    <td>{{$tr->amount}}</td>
                                    <td>{{$tr->doctor->name. ' '. $tr->doctor->family}}</td>
                                    <td>{{$tr->message}}</td>
                                    <td>{{$tr->factorNumber}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        @endif
    @endif
@endsection
