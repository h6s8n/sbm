@extends('admin.layouts.app')

@section('page_name', ' لیست ویزیت ها ')
@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_user">نام پزشک </label>
                        <input type="text" class="form-control" id="filter_user" name="filter_user"
                               value="{{ @$_GET['filter_user'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_br">نام بیمار </label>
                        <input type="text" class="form-control" id="filter_br" name="filter_br"
                               value="{{ @$_GET['filter_br'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_br_mobile">موبایل بیمار </label>
                        <input type="text" class="form-control" id="filter_br_mobile" name="filter_br_mobile"
                               value="{{ @$_GET['filter_br_mobile'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_br_email">ایمیل بیمار </label>
                        <input type="text" class="form-control" id="filter_br_email" name="filter_br_email"
                               value="{{ @$_GET['filter_br_email'] }}">
                    </div>
                </div>
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
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to">نوع ویزیت</label>
                        <select name="calendar_type" class="form-control">
                            <option value="">همه</option>
                            <option value="1" {{@$_GET['calendar_type']==1 ? 'selected' : ''}}>معمولی</option>
                            <option value="3" {{@$_GET['calendar_type']==3 ? 'selected' : ''}}>آفلاین</option>
                            <option value="2" {{@$_GET['calendar_type']==2 ? 'selected' : ''}}>فوری</option>
                            <option value="4" {{@$_GET['calendar_type']==4 ? 'selected' : ''}}>تفسیر آزمایش</option>
                            <option value="5" {{@$_GET['calendar_type']==5 ? 'selected' : ''}}>حضوری</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to">بیمارستان</label>
                        <select name="partner_id" class="form-control">
                            <option value="">همه</option>
                            @foreach($partners as $partner)
                            <option value="{{$partner->id}}" {{@$_GET['partner_id']==$partner->id ? 'selected' : ''}}>
                                {{$partner->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="specialization_id">تخصص</label>
                        <select name="specialization_id" id="specialization_id" class="form-control">
                            <option value="">همه</option>
                            @foreach($specializations as $specialization)
                                <option value="{{$specialization->id}}" {{@$_GET['specialization_id']==$specialization->id ? 'selected' : ''}}>
                                    {{$specialization->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                @if((@$_GET['from']) || (@$_GET['to']))
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <a href="{{route('list.of.visits')}}" class="btn btn-block btn-warning btn-rounded">تاریخ امروز</a>
                </div>
                @endif
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">اعمال فیلترها</button>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        مجموع {{ number_format($events->total()) }} عدد
                    </div>
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post">
        {{ csrf_field() }}

        <div class="white-box">

            <div class="portlet-body">

                @if(count($events) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <th></th>
                                <th>نام پزشک</th>
                                <th>تخصص</th>
                                <th>نام بیمار</th>
                                <th>نوع ویزیت</th>
                                <th>تاریخ ویزیت</th>
                                <th>تاریخ درخواست</th>
                                <th>تاریخ پایان ویزیت</th>
                                <th>بیمارستان</th>
                                <th> مبلغ کل (ریال)</th>
                                <th> وضعیت ویزیت</th>
                                <th>اعمال</th>
                                <th></th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($events as $event)
                                @php $price = $event->UserTransaction('paid')->first() ?  $event->UserTransaction('paid')->first()->amount : 0@endphp
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{ $event->doctor->fullname.' - '.$event->doctor->mobile}}</td>
                                    <td>{{ $event->doctor->allSpecializationsString()}}</td>
                                    <td>{{ $event->user->fullname.' - '.$event->user->mobile . ' ' . $event->user->email }}</td>
                                    <td>{{ $event->getVisitTypeString() }}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($event['reserve_time'])) }}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($event['created_at'])) }}</td>
                                    <td>{{ ($event['visit_status'] == 'end') ? jdate('d F Y ساعت H:i', strtotime($event['finish_at'])) : '-' }}</td>
                                    <td>{{$event->calendar ? ($event->calendar->partner()->first() ? $event->calendar->partner()->first()->name : 'SBM') : 'SBM'}}</td>
                                    <td>{{number_format($price)}}</td>
                                    <td style="color: {{$event->visit_status=='not_end' ? 'green' :
                                         ($event->visit_status=='end'? 'red' :
                                          ($event->visit_status == 'absence_of_doctor' ? 'purple' : ''))}}">
                                        @switch($event->visit_status)
                                            @case('end')
                                            پایان ویزیت
                                            @break
                                            @case('not_end')
                                            مجاز برای ویزیت
                                            @break
                                            @case('cancel')
                                            کنسل شده
                                            @break
                                            @case('refunded')
                                            برگشت وجه به بیمار
                                            @break
                                            @case('refundRequest')
                                            اعلام عدم حضور پزشک
                                            @break
                                            @case('newTimeRequest')
                                            درخواست وقت جدید از همین دکتر
                                            @break
                                            @case('newDoctorRequest')
                                        درخواست وقت از دکتر جدید
                                            @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($event->visit_status == 'not_end')
                                            <div class="col-xs-6">
                                                <a class="btn btn-block btn-danger btn-rounded request_but"
                                                   href="{{route('admin.finish.visit',$event->id)}}">پایان
                                                    ویزیت </a>
                                            </div>
                                            <div class="col-xs-6">
                                                <a class="btn btn-block btn-info btn-rounded request_but"
                                                   href="{{route('admin.create.call',['token_room'=>$event->token_room])}}">تماس
                                                    امن</a>
                                            </div>
                                            <hr>
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-primary btn-rounded request_but"
                                                   href="{{route('visit.cancel.admin',$event->id)}}">کنسل
                                                    (کسر۲۰درصد)</a>
                                            </div>
                                            <hr>
                                            <hr>
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-warning btn-rounded request_but"
                                                   href="{{route('admin.visit.refund',['user'=>$event->user_id,'er'=>$event->id])}}">بازگشت
                                                    وجه (بدون کسر)</a>
                                            </div>
{{--                                        @elseif($event->visit_status == 'cancel' )--}}
{{--                                            <div class="col-xs-12">--}}
{{--                                                <a class="btn btn-block btn-success btn-rounded request_but"--}}
{{--                                                   href="{{route('visit.open.again',$event->id)}}">بازگشایی--}}
{{--                                                    مجدد</a>--}}
{{--                                            </div>--}}

                                        @elseif($event->visit_status == 'end')
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-success btn-rounded request_but"
                                                   href="{{route('finished.visit.open.again',$event->id)}}">بازگشایی
                                                    مجدد</a>
                                            </div>
                                        @elseif($event->visit_status == 'absence_of_doctor')
                                            در انتظار بررسی ادمین
                                        @elseif($event->visit_status == 'cancel')
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-success btn-rounded request_but"
                                                   href="{{route('visit.cancel_refund',$event->id)}}">
                                                    لغو عودت</a>
                                            </div>
                                        @endif
                                        <hr>
                                        <hr>
                                        <div class="col-xs-12">
                                            <a class="btn btn-block btn-success btn-rounded request_but"
                                               href="{{route('admin.change.number',$event->id)}}">تغییر شماره تماس
                                                امن</a>
                                        </div>
                                        <hr>
                                        <hr>
                                        <div  class="col-xs-12">
                                            <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                               href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($event->doctor->mobile)."&app_absent=0"}}>
                                                واتس اپ</a>
                                            <hr>
                                        </div>
                                            <div  class="col-xs-12">
                                                <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                                   href={{"https://sbm24.com/".$event->doctor->username}}>
                                                    پروفایل پزشک در سایت</a>
                                                <hr>
                                            </div>
                                            <div  class="col-xs-12">
                                                <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                                   href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($event->user->mobile)."&app_absent=0"}}>
                                                     واتس اپ بیمار</a>
                                                <hr>
                                            </div>
                                    </td>
                                    <td>
                                        <div class="col-xs-12">
                                            <a class="btn btn-block btn-success btn-rounded request_but"
                                               href="{{route('visit.logs',$event->id)}}">لاگ ها</a>
                                        </div>
                                    </td>
                                </tr>
                                @php $row_count = $row_count +1;@endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $events->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    <div class="clearfix"></div>

                @else

                    <div class="msg">
                        <div class="alert alert-warning alert-info" style="text-align: center">ویزیتی یافت نشد.</div>
                    </div>

                @endif


            </div>

        </div>
    </form>

    {{--    <script>--}}
    {{--        $('.pay-warning').click(function(e){--}}
    {{--            e.preventDefault();--}}
    {{--            var hreflink = $(this).attr('href');--}}
    {{--            var key = $(this).attr('data-key');--}}
    {{--            swal({--}}
    {{--                title: "",--}}
    {{--                text: "آیا از تسویه حساب با "+key+" مطمئن هستید ؟ ",--}}
    {{--                type: "warning",--}}
    {{--                showCancelButton: true,--}}
    {{--                confirmButtonColor: "#DD6B55",--}}
    {{--                cancelButtonText: "خیر نیازی نیست",--}}
    {{--                confirmButtonText: "بله اطمینان دارم",--}}
    {{--                closeOnConfirm: false--}}
    {{--            }, function(){--}}
    {{--                window.location.href = hreflink;--}}
    {{--            });--}}
    {{--        });--}}

    {{--        let exportList = () =>{--}}
    {{--            let form = document.getElementById('myForm');--}}
    {{--            form.action = '{{route('export.bill.list')}}';--}}
    {{--            form.submit();--}}
    {{--        }--}}

    {{--    </script>--}}

@endsection
