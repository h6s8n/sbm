@extends('partnerPanel.layouts.app')

@section('page_name', ' صورت حساب مالی')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_doctor">نام پزشک </label>
                        <input type="text" class="form-control" id="filter_doctor" name="filter_doctor"
                               value="{{ @$_GET['filter_doctor'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_patient">نام بیمار </label>
                        <input type="text" class="form-control" id="filter_patient" name="filter_patient"
                               value="{{ @$_GET['filter_patient'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_from_date">از تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_from_date" name="filter_from_date"
                               value="{{ @$_GET['filter_from_date'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_to_date">تا تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_to_date" name="filter_to_date"
                               value="{{ @$_GET['filter_to_date'] }}">
                    </div>
                </div>

                {{--                <input name="filter_partner" value="{{@$_GET['filter_partner']}}" hidden>--}}

                {{--                @if($status_list == 'final')--}}
                {{--                    <div class="col-md-3 col-xs-12">--}}
                {{--                        <div class="form-group">--}}
                {{--                            <label for="filter_status">وضعیت پرداخت</label>--}}
                {{--                            <select id="filter_status" class="form-control" name="filter_status">--}}
                {{--                                <option value="">لطفا انتخاب کنید</option>--}}
                {{--                                --}}{{--<option value="pending" {{ (@$_GET['filter_status'] == 'pending') ? 'selected' : '' }}>درحال بررسی</option>--}}
                {{--                                <option value="paid" {{ (@$_GET['filter_status'] == 'paid') ? 'selected' : '' }}>پرداخت--}}
                {{--                                    شد--}}
                {{--                                </option>--}}
                {{--                                <option value="cancel" {{ (@$_GET['filter_status'] == 'cancel') ? 'selected' : '' }}>لغو--}}
                {{--                                    شد--}}
                {{--                                </option>--}}
                {{--                                <option value="pending" {{ (@$_GET['filter_status'] == 'pending') ? 'selected' : '' }}>--}}
                {{--                                    در انتظار بررسی--}}
                {{--                                </option>--}}
                {{--                            </select>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                @endif--}}

                <div class="clearfix"></div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    {{--                    <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">خروجی اکسل</button>--}}
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        {{--                        مجموعه تراکنش ها {{ number_format($request['total']) }} عدد--}}
                    </div>
                </div>

                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post" action="{{route('bill.done.group')}}">
        {{ csrf_field() }}
        <div class="white-box">
            {{--            <div class="alert alert-warning">--}}
            {{--                <ul>--}}
            {{--                    <li>ابتدا موارد مورد نظر را انتخاب کنید سپس پرداخت گروهی را اعمال کنید</li>--}}
            {{--                    <li>برای پر داخت های تکی از دکمه پرداخت شد همان سطر استفاده کنید</li>--}}
            {{--                </ul>--}}
            {{--            </div>--}}
            {{--            <div class="col-sm-2 col-xs-12">--}}
            {{--                <button type="submit" class="btn btn-block btn-success">پرداخت گروهی</button>--}}
            {{--            </div>--}}
            <div class="portlet-body">
                <hr>
                @if(isset($request['data']) && count($request['data']) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">

                                {{--@if($status_list == 'final')
                                <th>اعمال</th>
                                @endif--}}
                                <th>ردیف</th>
                                <th>شناسه</th>
                                <th> مبلغ پرداختی (ریال)</th>
                                <th> نام دکتر</th>
                                <th> نام بیمار</th>
                                <th>تاریخ ویزیت</th>
                                <th>تاریخ تقریبی واریز</th>
                                <th>فیش واریز</th>
                                <th> وضعیت پرداخت</th>
                            </tr>
                            @php $row_count =1 @endphp

                            @foreach($request['data'] as $k => $item)
                                <tr role="row" class="filter">
                                    <td>{{ $row_count }}</td>
                                    <td>{{ $item['id'] }}</td>
                                    <td>{{ number_format($item['amount']) }}</td>
                                    <td>{{ $item['dr_fullname'] }}</td>
                                    <td>{{ $item['patient_fullname'] }}</td>
                                    <td>{{ $item['created_at'] }}</td>
                                    <td>{{ $item['estimated_deposit_time'] }}</td>
                                    <td><a href="{{ $item['receipt_url'] }}">مشاهده</a></td>
                                    <td>{{ $item['status'] }}</td>
                                </tr>
                                @php $row_count = $row_count +1;@endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @php $simbol = request()->fullUrl() == request()->url() ? '?' : '&'; @endphp
                    <ul class="pagination" role="navigation">
                        <li class="page-item" aria-disabled="true" aria-label="&laquo; Previous">
                            <a @if($request['prev_page_url']) href="{{request()->fullUrl().$simbol.$request['prev_page_url']}}" class="page-link" @else class="disable" @endif rel="prev" aria-label="&lsaquo; Previous">&lsaquo;</a>
                        </li>

                        <li class="page-item">
                            <a @if($request['next_page_url']) class="page-link" href="{{request()->fullUrl().$simbol.$request['next_page_url']}}" @else class="dissable" @endif rel="next" aria-label="Next &raquo;">&rsaquo;</a>
                        </li>
                    </ul>
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
