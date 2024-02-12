@extends('admin.layouts.app')

@section('page_name', 'گزارش  پزشکان')

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
{{--                        مجموعه رکورد ها {{ number_format($request->total()) }} عدد--}}
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
                                <th> </th>
                                <th>نام پزشک</th>

                                <th> مبلغ تراکنش ویزیت</th>
                                <th> سهم سامانه</th>
                                <th> سهم پزشک</th>
                                <th>مانده</th>

                                <th> مبلغ تراکنش درگاه پرداخت</th>
                                <th> سهم سامانه</th>
                                <th> سهم پزشک</th>
                                <th> کارمزد</th>
                                <th>مانده</th>

                                <th> مبلغ تراکنش پوز</th>
                                <th> سهم سامانه</th>
                                <th> سهم پزشک</th>
                                <th>مانده</th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($request as $k => $item)
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{ $item['fullname'] }}</td>
                                    <td style="background: #3FE5FF">-</td>
                                    <td style="background: #3FE5FF">-</td>
                                    <td style="background: #3FE5FF">-</td>
                                    <td style="background: #3FE5FF">-</td>
                                    <td style="background: #EFFF2F">{{ $item['wallet_sum_increase'] }}</td>
                                    <td style="background: #EFFF2F">{{ $item['wallet_service_wage'] }}</td>
                                    <td style="background: #EFFF2F">{{ $item['wallet_doctor_wage'] }}</td>
                                    <td style="background: #EFFF2F">{{ $item['wallet_bank_wage'] }}</td>
                                    <td style="background: #EFFF2F">{{ $item['wallet_account_balance'] }}</td>
                                    <td style="background: rgba(22,255,83,0.29)">{{ $item['cod_sum_increase'] }}</td>
                                    <td style="background: rgba(22,255,83,0.29)">{{ $item['cod_service_wage'] }}</td>
                                    <td style="background: rgba(22,255,83,0.29)">{{ $item['cod_doctor_wage'] }}</td>
                                    <td style="background: rgba(22,255,83,0.29)">{{ $item['cod_account_balance'] }}</td>
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
        let exportList = () => {
            let form = document.getElementById('myForm');
            form.action = '{{route('export.bill.total')}}';
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
