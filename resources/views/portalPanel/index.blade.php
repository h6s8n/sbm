@extends('portalPanel.layouts.app')

@section('page_name', 'داشبورد')

@section('content')

    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نام پزشک </label>
                        <input type="text" class="form-control" id="filter_name" name="filter_name"
                               value="{{ @$_GET['filter_name'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_mobile">موبایل پزشک </label>
                        <input type="text" class="form-control" id="filter_mobile" name="filter_mobile"
                               value="{{ @$_GET['filter_mobile'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_transId">شناسه پرداخت ریالی </label>
                        <input type="number" class="form-control" id="filter_transId" name="filter_transId"
                               value="{{ @$_GET['filter_transId'] }}">
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
                        <label for="filter_status">وضعیت </label>
                        <select id="filter_status" class="form-control" name="filter_status">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="paid_decrease" {{ (@$_GET['filter_status'] == 'paid_decrease') ? 'selected' : '' }}>
                                تسویه شده
                            </option>
                            <option value="pending_decrease" {{ (@$_GET['filter_status'] == 'pending_decrease') ? 'selected' : '' }}>
                                در انتظار تسویه
                            </option>
                        </select>
                    </div>
                </div>


                <div class="clearfix"></div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>

                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        مجموعه تراکنش ها {{ isset($request) ? number_format($request->total()) : 0 }} عدد
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
                @if(isset($request) && count($request) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">

                                {{--@if($status_list == 'final')
                                <th>اعمال</th>
                                @endif--}}
                                <th> </th>
                                <th> نام/موبایل پزشک</th>
                                <th>تاریخ درخواست</th>
                                <th> مبلغ (ریال)</th>
                                <th> شناسه پرداخت‌ ریالی</th>
                                <th>آدرس کیف پول</th>
                                <th> وضعیت </th>
                                <th>قیمت روز تتر</th>
                                <th>تعداد تتر</th>
                                <th>عملیات</th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($request as $k => $item)
                                @php
                                    $service_mapping = [
                                         'visit' => 'ویزیت',
                                         'surgery' => 'عمل جراحی',
                                         'other' => 'خدمات سلامت',
                                         '' => '-'
                                       ];

                                    $service = $service_mapping[$item->service];

                                    $settlement_type_mapping = [
                                         'rial' => 'ریالی',
                                         'other' => 'ارز دیجیتال',
                                         '' => '-',
                                         ];

                                    $settlement_type = $settlement_type_mapping[$item->settlement_type];

                                    $status_mapping = [
                                         'pending_decrease' => 'در انتظار تسویه',
                                         'paid_increase' => 'واریز شده توسط بیمار',
                                         'pending_increase' => 'در انتظار واریز بیمار',
                                         'paid_decrease' => 'تسویه شده'
                                         ];

                                    $status = $status_mapping[$item->status];
                                @endphp
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{ $item->doctor->nickname . ' ' . $item->doctor->fullName }}
                                    <br>
                                        <a href="tel:{{ $item->doctor->mobile }}">
                                            {{ $item->doctor->mobile }}
                                        </a>
                                    </td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item->created_at)) }}</td>
                                    <td>{{ (number_format(abs($item->amount))) }}</td>
                                    <td>{{ $item->transId }}</td>
                                    <td>{{ $item->account_id }}</td>
                                    <td>{{ $status }}</td>
                                    <td>{{ $item->tether_current_price }}</td>
                                    <td>{{ $item->tether_count }}</td>
                                    <td>
                                        @switch($item->status)
                                            @case('pending_decrease')
                                                <a class="btn btn-success btn-rounded"
                                                   href="{{ route('digital.wallet.confirmPay',[$item->transId]) }}">
                                                    تسویه
                                                </a>
                                            @break

                                            @default
                                            -
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

