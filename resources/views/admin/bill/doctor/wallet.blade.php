@extends('admin.layouts.app')

@section('page_name', $title ?? ' درگاه سلامت ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نام پزشک </label>
                        <input type="text" class="form-control" id="filter_name" name="filter_name"
                               value="{{ @$_GET['filter_name'] }}">
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <label for="filter_mobile">موبایل پزشک </label>
                        <input type="text" class="form-control" id="filter_mobile" name="filter_mobile"
                               value="{{ @$_GET['filter_mobile'] }}">
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <label for="filter_user_mobile">موبایل بیمار</label>
                        <input type="text" class="form-control" id="filter_user_mobile" name="filter_user_mobile"
                               value="{{ @$_GET['filter_user_mobile'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_transId">شناسه پرداخت </label>
                        <input type="text" class="form-control" id="filter_transId" name="filter_transId"
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
                            <option value="paid_increase" {{ (@$_GET['filter_status'] == 'paid_increase') ? 'selected' : '' }}>
                                 واریز شده توسط بیمار
                            </option>
                            <option value="pending_increase" {{ (@$_GET['filter_status'] == 'pending_increase') ? 'selected' : '' }}>
                                  در انتظار واریز بیمار
                            </option>
                            <option value="cancel_decrease" {{ (@$_GET['filter_status'] == 'cancel_decrease') ? 'selected' : '' }}>
                                  لغو شده
                            </option>
                        </select>
                    </div>
                </div>


                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_service">نوع خدمت </label>
                        <select id="filter_service" class="form-control" name="filter_service">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="surgery" {{ (@$_GET['filter_service'] == 'surgery') ? 'selected' : '' }}>
                                عمل جراحی
                            </option>
                            <option value="visit" {{ (@$_GET['filter_service'] == 'visit') ? 'selected' : '' }}>
                                ویزیت
                            </option>
                            <option value="other" {{ (@$_GET['filter_service'] == 'other') ? 'selected' : '' }}>
                                خدمات سلامت
                            </option>
                        </select>
                    </div>
                </div>


                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_settlement_type">نوع تسویه </label>
                        <select id="filter_settlement_type" class="form-control" name="filter_settlement_type">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="rial" {{ (@$_GET['filter_settlement_type'] == 'rial') ? 'selected' : '' }}>
                                 ریالی
                            </option>
                            <option value="other" {{ (@$_GET['filter_settlement_type'] == 'other') ? 'selected' : '' }}>
                                ارز دیجیتال
                            </option>
                            <option value="currency_cash" {{ (@$_GET['filter_settlement_type'] == 'currency_cash') ? 'selected' : '' }}>
                                ارز نقدی
                            </option>
                            <option value="currency_remit" {{ (@$_GET['filter_settlement_type'] == 'currency_remit') ? 'selected' : '' }}>
                                حواله ارزی
                            </option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="payment_type" value="{{ $payment_type ?? 'wallet' }}">

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

    @if(!is_null($account_balance))
        <div class="white-box">
            <div class="portlet-body">
{{--                <div class="clearfix"></div>--}}

                <form  style="direction: rtl">

                    <div class="col-md-3 col-xs-12">
                        <div class="form-group">
                            <label for="filter_name">مانده حساب :</label>
                            <input type="text" class="form-control text-left" style="direction: ltr"
                                   value="{{ number_format($account_balance->account_balance). ' ریال ' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <div class="form-group">
                            <label for="filter_name">قابل برداشت :</label>
                            <input type="text" class="form-control text-left" style="direction: ltr"
                                   value="{{ number_format($account_balance->account_accessible) . ' ریال ' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <div class="form-group">
                            <label for="filter_name">در انتظار برداشت :</label>
                            <input type="text" class="form-control text-left" style="direction: ltr"
                                   value="{{ number_format($account_balance->pending_decrease) . ' ریال '}}" disabled>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <div class="form-group">
                            <label for="filter_name">قابل برداشت غیر ریالی :</label>
                            <input type="text" class="form-control text-left" style="direction: ltr"
                                   value="{{ number_format($account_balance->non_rial_account_accessible) . ' ریال '}}" disabled>
                        </div>
                    </div>

                </form>
                <div class="clearfix"></div>

            </div>
        </div>
    @endif

    <form method="post" action="{{route('bill.done.group')}}">
        <div class="col-md-2 float-left mt-5">
            <a class="btn btn-block btn-success btn-rounded " href="/cp-manager/bill/doctor/wallet/transactions/create">
              + ثبت واریزی به اپراتور
            </a>
        </div>
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
                                <th> </th>
                                <th>نام/موبایل پزشک</th>
                                <th>تاریخ درخواست</th>
                                <th>تاریخ پرداخت</th>
                                <th> مبلغ (ریال)</th>
                                <th> کارمزد بانکی (ریال)</th>
                                <th> کارمزد خدمت (ریال)</th>
                                <th> سهم پزشک (ریال)</th>
                                <th> وضعیت </th>
                                <th> شناسه پرداخت</th>
                                <th> نوع تسویه</th>
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
                                         'currency_remit' => 'حواله ارزی',
                                         'currency_cash' => 'ارز نقدی',
                                         '' => '-',
                                         ];

                                    $settlement_type = $settlement_type_mapping[$item->settlement_type];

                                    $status_mapping = [
                                         'pending_decrease' => 'در انتظار تسویه',
                                         'paid_increase' => 'واریز شده توسط بیمار',
                                         'pending_increase' => 'در انتظار واریز بیمار',
                                         'paid_decrease' => 'تسویه شده',
                                         'cancel_decrease' => 'لغو شده'
                                         ];

                                    $status = $status_mapping[$item->status];
                                @endphp
                                <tr role="row" class="filter">
                                    <td title="{{$item->id}}">{{$row_count}}</td>
                                    <td>{{ $item->doctor->nickname . ' ' . $item->doctor->fullName }}
                                        <br>
                                        <a href="tel:{{ $item->doctor->mobile }}">
                                            {{ $item->doctor->mobile }}
                                        </a>
                                    </td>
                                    <td style="direction: ltr">{{ jdate('Y/m/d H:i:s', strtotime($item->created_at)) }}</td>
                                    <td style="direction: ltr">{{ $item->paid_at ? jdate('Y/m/d H:i:s', strtotime($item->paid_at)) : '-' }}</td>
                                    @php $cssColor = $item->amount > 0 ? 'green' : 'red'; @endphp
                                    <td style="direction: ltr; color: {!! $cssColor !!}">
                                        {{ (number_format(/*abs*/($item->amount))) }}
                                    </td>
                                    <td>{{ (number_format(abs($item->bank_wage))) }}</td>
                                    <td>{{ (number_format(abs($item->service_wage))) }}</td>
                                    <td> @if($item->status == 'paid_decrease' || $item->status == 'pending_decrease') - @else{{ (number_format(abs($item->amount - ($item->service_wage + $item->bank_wage)))) }}@endif</td>
                                    <td>
                                        {{ $status }}
                                    <br>
                                        @if($item->user_id != null)
                                            {{ $item->user->mobile }}
                                        @endif
                                    </td>
                                    <td>{{ $item->transId }}</td>
                                    <td>{{ $settlement_type }}</td>
                                    <td>
                                        @switch($item->status)
                                            @case('pending_decrease')
                                                @if($item->settlement_type == 'other' && $item->transId == null)
                                                <a class="btn btn-success btn-rounded"
                                                   href="{{ route('doctor.wallet.confirmPay',[$item->id]) }}">ثبت شناسه پرداخت</a>
                                                @elseif($item->settlement_type == 'rial')
                                                    <a class="btn btn-warning btn-rounded pay-warning"
                                                       data-key="{{ $item->doctor->nickname . ' ' . $item->doctor->fullName }}"
                                                       href="{{ route('doctor.wallet.pay',[$item->id]) }}">
                                                        تسویه
                                                    </a>
                                                @endif
                                            <a class="btn btn-danger btn-rounded"
                                               onclick="confirm('آیا از لغو این درخواست اطمینان دارید؟')"
                                               href="{{ route('doctor.wallet.cancel',[$item->id]) }}">
                                                لغو
                                            </a>


                                            @break

                                            @default
                                            -
                                            @break
                                        @endswitch

                                    </td>

                                </tr>

                                @if($item->description)
                                    <tr>
                                        <td></td>
                                        <td> ☝️
                                            توضیحات</td>
                                        <td colspan="10">{{$item->description}}</td>
                                    </tr>
                                @endif

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
            form.action = '{{route('export.bill.wallet')}}';
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
