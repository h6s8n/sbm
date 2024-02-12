@extends('admin.layouts.app')

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
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="partner_id">بیمارستان</label>
                        <select id="partner_id" class="form-control" name="partner_id">
                            <option value="-1">همه</option>
                            <option value="0">سلامت بدون مرز</option>
                            @foreach($partners as $partner)
                                <option
                                    value="{{$partner->id}}" {{ (@$_GET['partner_id'] == $partner->id) ? 'selected' : '' }}>
                                    {{$partner->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input name="filter_partner" value="{{@$_GET['filter_partner']}}" hidden>
                <div class="clearfix"></div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">خروجی
                        اکسل
                    </button>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        مجموعه تراکنش ها {{ number_format($invoices->total()) }} عدد
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
                @if(count($invoices) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">

                                {{--@if($status_list == 'final')
                                <th>اعمال</th>
                                @endif--}}
{{--                                <th><input type="checkbox" id="select-all"></th>--}}
                                <th>نام پزشک</th>
                                <th>نام بیمار</th>
                                <th>تاریخ درخواست</th>
                                <th>تاریخ شروع ویزیت</th>
                                <th>تاریخ پایان ویزیت</th>
                                <th> مبلغ کل (ریال)</th>
                                <th> مبلغ پرداختی (ریال)</th>
                                <th> وضعیت انتقال</th>
                                <th> وضعیت ویزیت</th>
                            </tr>
                            @foreach($invoices as $invoice)
                            <tr role="row" class="filter">
                                <td>{{$invoice->doctor->fullname}}</td>
                                <td>{{$invoice->user->fullname}}</td>
                                <td>{{$invoice->event->created_at}}</td>
                                <td>{{$invoice->event->reserve_time}}</td>
                                <td>{{$invoice->event->finish_at}}</td>
                                <td>{{number_format($invoice->transaction->amount)}}</td>
                                <td>{{number_format($invoice->amount)}}</td>
                                <td>{{$invoice->migrated ? 'انجام شده' : 'انجام نشده'}}</td>
                                <td>{{$invoice->event->visit_status}}</td>
                            </tr>

                            @if($invoice->calendar->partner_id > 0)
                                <tr>
                                    <td colspan="6"></td>
                                    <td colspan="4">
                                        👆 این ویزیت مربوط به :
                                        <span style="color: red">
                                                    {{$invoice->calendar->partner->name}}
                                                </span>
                                    </td>
                                </tr>
                            @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $invoices->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
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
        $('#select-all').click(function (event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function () {
                    this.checked = false;
                });
            }
        });
    </script>
@endsection
