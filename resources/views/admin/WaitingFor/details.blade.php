@extends('admin.layouts.app')

@section('page_name', 'جزییات افراد در انتظار')

@section('content')
    <div class="white-box">
        <div class="badge badge-purple">{{'لیست جزییات بیماران در انتظار دکتر '.$doctor->fullname}}</div>
        <form  method="get">
        <div class="row">
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="from"> از تاریخ</label>
                    <input type="text" class="form-control observer" id="from"
                           name="from"
                           value="{{ @$_GET['from'] }}">
                </div>
            </div>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="to"> تا تاریخ</label>
                    <input type="text" class="form-control observer"
                           id="to"
                           name="to"
                           value="{{ @$_GET['to'] }}">
                </div>
            </div>
            <div class="col-sm-3 col-xs-12" style="padding-top: 17px;">
                <a>
                    <button type="submit" class="btn btn-block btn-success btn-rounded">اعمال فیلتر</button>
                </a>
            </div>
        </div>
        </form>
        <form method="get" action="{{route('waiting.details.export',$doctor->id)}}">
            <div class="row">
                <div class="col-sm-3 col-xs-12" style="padding-top: 17px;">
                    <a>
                        <button type="submit" class="btn btn-block btn-success btn-rounded">خروجی اکسل همه</button>
                    </a>
                </div>

            </div>
            <hr>
        </form>
        <div class="portlet-body">
            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tbody>
                    <tr role="row" class="heading">
                        <th></th>
                        <th> بیمار</th>
                        <th> موبایل بیمار</th>
                        <th>تعداد درخواست ها</th>
                        <th> تاریخ آخرین درخواست</th>
                        <th> تاریخ تغییر وضعیت</th>
                        <th>وضعیت</th>
                        <th></th>
                    </tr>
                    @php $row_count =1 @endphp
                    @foreach($items as $item)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td>{{$item->user->fullname}}</td>
                            <td>{{$item->user->mobile}}</td>
                            <td>{{$item->counts}}</td>
                            <td>{{jdate('Y-m-d ساعت H:i',strtotime($item->created_at))}}</td>
                            <td>{{jdate('Y-m-d ساعت H:i',strtotime($item->updated_at))}}</td>
                            <td style="{{$item->sent_message == 0  ? 'color:red;' : 'color:green;'}}">
                                {{$item->sent_message == 0 ?
'ارسال نشده' : ($item->sent_message == 1 ?
'ارسال شده' : ('درخواست اشتباه'))}}</td>
                            <td style="{{$item->user->hasTimeWith($item->doctor_id) ? 'color:green;' : 'color:red;'}}"
                            >{{$item->user->hasTimeWith($item->doctor_id) ? 'ویزیت فعال دارند' : 'ویزیت فعال ندارند'}}</td>
                        </tr>
                        @php $row_count = $row_count +1;@endphp
                    @endforeach
                    </tbody>
                </table>
            </div>
            {!! $items->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
            <div class="clearfix"></div>
        </div>
    </div>
@endsection
