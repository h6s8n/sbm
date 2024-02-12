@extends('partnerPanel.layouts.app')

@section('page_name', ' صورت حساب مالی پزشکان ')

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
                                <th>تاریخ درخواست</th>
                                <th>تاریخ شروع ویزیت</th>
                                <th>تاریخ پایان ویزیت</th>
{{--                                <th> مبلغ کل (ریال)</th>--}}
                               <th> مبلغ پرداختی (ریال)</th>
                               <th> تاریخ واریز</th>
                                <th> وضعیت پرداخت</th>
                                <th> وضعیت ویزیت</th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($request as $k => $item)
                                @php
                                    $partner = \App\Model\Partners\Partner::where('id', $item['partner_id'])->first();

                                $price = $item->UserTransaction('paid')->first() ?  $item->UserTransaction('paid')->first()->amount : 0@endphp
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}  @if($item['pay_dr_status']=='pending' && $price>0)<input type="checkbox" name="event_id_done[]" value="{{$item['id']}}"@endif></td>
                                    <td>{{ $item['doctor_nickname'] . ' ' . $item['dr_fullname'] }}</td>
                                    <td>{{ $item['us_fullname'] . $item['us_mobile']}}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item['created_at'])) }}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item['reserve_time'])) }}</td>
                                    <td>{{ ($item['visit_status'] == 'end') ? jdate('d F Y ساعت H:i', strtotime($item['finish_at'])) : '-' }}</td>
{{--                                    <td>{{number_format($price)}}</td>--}}
                                    <td>{{ number_format($item['pay_amount']) }}</td>
                                    <td>{{ ($item['visit_status'] == 'end') ? jdate('d F Y ساعت H:i', strtotime($item['paid_at'])) : '-'}}</td>
                                    <td>
                                        @switch($item['pay_dr_status'])
                                            @case('pending')
                                            @if($price > 0)
                                                پرداخت نشده
                                            @else
                                                پرداخت شد
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
            form.action = '{{route('export.partner.bill.list')}}';
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
