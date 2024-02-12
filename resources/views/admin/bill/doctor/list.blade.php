@extends('admin.layouts.app')

@section('page_name', $title ?? ' صورت حساب مالی پزشکان ' )

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
                        <label for="filter_dr_mobile">موبایل پزشک </label>
                        <input type="text" class="form-control" id="filter_dr_mobile" name="filter_dr_mobile"
                               value="{{ @$_GET['filter_dr_mobile'] }}">
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
                        <label for="filter_start_date">از تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_start_date" name="filter_start_date"
                               value="{{ @$_GET['filter_start_date'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تا تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_end_date" name="filter_end_date"
                               value="{{ @$_GET['filter_end_date'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_visit_status">وضعیت ویزیت</label>
                        <select id="filter_visit_status" class="form-control" name="filter_visit_status">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="end" {{ (@$_GET['filter_visit_status'] == 'end') ? 'selected' : '' }}>پایان
                                ویزیت
                            </option>
                            <option
                                value="not_end" {{ (@$_GET['filter_visit_status'] == 'not_end') ? 'selected' : '' }}>
                                مجاز برای ویزیت
                            </option>
                            <option value="cancel" {{ (@$_GET['filter_visit_status'] == 'cancel') ? 'selected' : '' }}>
                                کنسل شده
                            </option>
                            <option
                                value="refunded" {{ (@$_GET['filter_visit_status'] == 'refunded') ? 'selected' : '' }}>
                                برگشت شده به بیمار
                            </option>
                        </select>
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
                        <label for="partner_id">بیمارستان</label>
                        <select id="partner_id" class="form-control" name="partner_id">
                            <option value="-1" >همه</option>
                            <option value="0" >سلامت بدون مرز</option>
                            @foreach($partners as $partner)
                            <option value="{{$partner->id}}" {{ (@$_GET['partner_id'] == $partner->id) ? 'selected' : '' }}>
                                {{$partner->name}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input name="filter_partner" value="{{@$_GET['filter_partner']}}" hidden>

                @if($status_list == 'final')
                    <div class="col-md-3 col-xs-12">
                        <div class="form-group">
                            <label for="filter_status">وضعیت پرداخت</label>
                            <select id="filter_status" class="form-control" name="filter_status">
                                <option value="">لطفا انتخاب کنید</option>
                                {{--<option value="pending" {{ (@$_GET['filter_status'] == 'pending') ? 'selected' : '' }}>درحال بررسی</option>--}}
                                <option value="paid" {{ (@$_GET['filter_status'] == 'paid') ? 'selected' : '' }}>پرداخت
                                    شد
                                </option>
                                <option value="cancel" {{ (@$_GET['filter_status'] == 'cancel') ? 'selected' : '' }}>لغو
                                    شد
                                </option>
                                <option value="pending" {{ (@$_GET['filter_status'] == 'pending') ? 'selected' : '' }}>
                                    در انتظار بررسی
                                </option>
                            </select>
                        </div>
                    </div>
                @endif

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_patient_from_">بیمار ثبت نامی از</label>
                        <select id="filter_patient_from_" class="form-control" name="filter_patient_from_">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="151122" {{ (@$_GET['filter_patient_from_'] == '151122') ? 'selected' : '' }}>پالسی نو
                            </option>
                            <option value="103496" {{ (@$_GET['filter_patient_from_'] == '103496') ? 'selected' : '' }}>
                                های کیش
                            </option>
                            <option value="83401" {{ (@$_GET['filter_patient_from_'] == '83401') ? 'selected' : '' }}>
                                جیرینگ
                            </option>
                        </select>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">خروجی اکسل</button>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        مجموعه تراکنش ها {{ number_format($request->total()) }} عدد
                    </div>
                </div>

                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post" action="{{route('bill.done.group')}}">
        {{ csrf_field() }}
        <div class="white-box">
            <div class="alert alert-warning">
                <ul>
                    <li>ابتدا موارد مورد نظر را انتخاب کنید سپس پرداخت گروهی را اعمال کنید</li>
                    <li>برای پر داخت های تکی از دکمه پرداخت شد همان سطر استفاده کنید</li>
                </ul>
            </div>
            <div class="col-sm-2 col-xs-12">
                <button type="submit" class="btn btn-block btn-success">پرداخت گروهی</button>
            </div>
            <div class="portlet-body">
                <hr>
                @if(count($request) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">

                                {{--@if($status_list == 'final')
                                <th>اعمال</th>
                                @endif--}}
                                <th><input type="checkbox" id="select-all"></th>
                                <th>نام پزشک</th>
                                <th>نام بیمار</th>
                                <th>نوع ویزیت</th>
                                <th>تاریخ درخواست</th>
                                <th>تاریخ شروع ویزیت</th>
                                <th>تاریخ پایان ویزیت</th>
                                <th>تاریخ واریز</th>
                                <th> مبلغ کل (ریال)</th>
                                <th> مبلغ پرداختی (ریال)</th>
                                <th>محل</th>
                                <th> وضعیت پرداخت</th>
                                <th> وضعیت ویزیت</th>
                            </tr>
                            @php $row_count =1 @endphp

                            @foreach($request as $k => $item)
                                @php
                                    $partner = \App\Model\Partners\Partner::where('id', $item['partner_id'])->first();

                                $price = $item->UserTransaction('paid')->first() ?  $item->UserTransaction('paid')->first()->amount : 0@endphp
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}  @if($item['pay_dr_status']=='pending' && $price>0)<input type="checkbox" name="event_id_done[]" value="{{$item['id']}}">@endif</td>
                                    <td>{{ $item['doctor_nickname'] . ' ' . $item['dr_fullname'] }}</td>
                                    <td>{{ $item['us_fullname']. ' ' .$item['us_mobile']. ' ' . $item['us_email']}}</td>
                                    <td>
                                        {{ \App\Enums\VisitTypeEnum::name($item['type'] ?? 1) }}
                                    </td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item['created_at'])) }}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item['reserve_time'])) }}</td>
                                    <td>{{ ($item['visit_status'] == 'end') ? jdate('d F Y ساعت H:i', strtotime($item['finish_at'])) : '-' }}</td>
                                    <td>{{ ($item['visit_status'] == 'end') && $item['pay_dr_status']=='paid' ? jdate('d F Y ساعت H:i', strtotime($item['paid_date'])) : '-' }}</td>
                                    <td>{{number_format($price)}}</td>
                                    <td>{{ number_format($item['pay_amount']) }}</td>
{{--                                    <td>{{$item['visit_status'] == 'end' ? ($item['source'] == 1 ? 'SBM' : 'Arzpaya') : '---'}}</td>--}}
                                    <td>{{$item['visit_status'] == 'end' ? ($item['transaction_partner_id'] == 0 ? 'SBM' : $partner->name) : '---'}}</td>
                                    <td>
                                        @switch($item['pay_dr_status'])
                                            @case('pending')
                                            @if($price > 0)

                                                @if($item['dr_sheba'])
                                                    <a class="btn btn-success btn-rounded pay-warning"
                                                       href="{{ url('cp-manager/bill/doctor/pay/' . $item['id']) }}"
                                                       data-key="{{ $item['doctor_nickname'] . ' ' . $item['dr_fullname'] }}">
                                                        پرداخت صورت حساب </a>
                                                    <hr>
                                                    <a class="btn btn-warning btn-rounded pay-done"
                                                       href="{{route('bill.done',$item['id'])}}"
                                                       data-key="{{ $item['doctor_nickname'] . ' ' . $item['dr_fullname'] }}">پرداخت شد</a>
                                                @else
                                                    شماره شبا وجود ندارد
                                                @endif

                                            @else
                                                @php
                                                $text = "پزشک گرامی مبلغ ".$price."بابت صورت حساب ".jdate('d F Y ساعت H:i', strtotime($item['reserve_time']))."بیمار ".$item['us_fullname']." به حساب شما واریز شد.";
                                                @endphp
                                                پرداخت شد
                                                <a class="btn btn-success btn-rounded" target="_blank"
                                                   href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($item['dr_mobile'])."&text=".urlencode($text)."&app_absent=0"}}>
                                                    واتس اپ</a>
                                            @endif
                                            @break
                                            @case('paid')
                                            پرداخت شد
                                            @break
                                            @case('cancel')
                                            لغو شد
                                            @break
                                            @default
                                            ویزیت پایان نیافته است
                                            @break
                                        @endswitch
                                    </td>
                                    <td style="color: {{$item['visit_status']=='refunded'? 'red' : ''}}">
                                        @switch($item['visit_status'])
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
                                        @endswitch
                                    </td>
                                </tr>


                                @php
                                    if($item['partner_id'] > 0){
                                        @endphp
                                        <tr>
                                            <td colspan="6">
                                                {{$item['message']}}
                                            </td>
                                            <td colspan="4">
                                                👆 این ویزیت مربوط به :
                                                <span style="color: red">
                                                    {{$partner->name}}
                                                </span>
                                            </td>
                                        </tr>
                                        @php
                                    }
                                @endphp
                                @php $row_count = $row_count +1;@endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $request->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    <div class="clearfix"></div>

                @else

                    <div class="msg">
                        <div class="alert alert-warning alert-info" style="text-align: center">صورت حسابی یافت نشد.
                        </div>
                    </div>

                @endif


            </div>

        </div>
    </form>

    <script>
        $('.pay-warning').click(function (e) {
            e.preventDefault();
            var hreflink = $(this).attr('href');
            var key = $(this).attr('data-key');
            swal({
                title: "",
                text: "آیا از تسویه حساب با " + key + " مطمئن هستید ؟ ",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                cancelButtonText: "خیر نیازی نیست",
                confirmButtonText: "بله اطمینان دارم",
                closeOnConfirm: false
            }, function () {
                window.location.href = hreflink;
            });
        });
        $('.pay-done').click(function (e) {
            e.preventDefault();
            var hreflink = $(this).attr('href');
            var key = $(this).attr('data-key');
            swal({
                title: "",
                text: "آیا از تسویه حساب با " + key + " مطمئن هستید ؟ ",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                cancelButtonText: "خیر نیازی نیست",
                confirmButtonText: "بله اطمینان دارم",
                closeOnConfirm: false
            }, function () {
                window.location.href = hreflink;
            });
        });
        let exportList = () => {
            let form = document.getElementById('myForm');
            form.action = '{{route('export.bill.list')}}';
            form.submit();
        }

    </script>
    <script>
        $('#select-all').click(function(event) {
            if(this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;
                });
            }
        });
    </script>
@endsection
