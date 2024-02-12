@extends('admin.layouts.app')

@section('page_name', 'واریزی به اپراتور درگاه سلامت ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_name">نام اوپراتور </label>
                        <input type="text" class="form-control" id="filter_name" name="filter_name"
                               value="{{ @$_GET['filter_name'] }}">
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
        <div class="col-md-2 float-left mt-5">
            <a class="btn btn-block btn-success btn-rounded " href="/cp-manager/bill/doctor/wallet/transactions/create">
                + ثبت واریزی به اپراتور
            </a>
        </div>
        <div class="white-box">
            <div class="portlet-body">
                <hr>
                @if(isset($request) && count($request) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <th> </th>
                                <th> نام اپراتور</th>
                                <th>تاریخ ایجاد</th>
                                <th> مبلغ (ریال)</th>
                                <th> شناسه پرداخت‌ ریالی</th>
                            </tr>
                            @php $row_count =1 @endphp

                            @foreach($request as $k => $item)
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{ $item->fullname }}</td>
                                    <td>{{ jdate('d F Y ساعت H:i', strtotime($item->created_at)) }}</td>
                                    <td>{{ (number_format(abs($item->amount))) }}</td>
                                    <td>{{ $item->transId }}</td>
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

