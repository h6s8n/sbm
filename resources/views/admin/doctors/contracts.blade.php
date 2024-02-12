@extends('admin.layouts.app')

@section('page_name', 'قرارداد ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_name">نام و نام خانوادگی </label>
                    <input type="text" class="form-control" id="filter_name" name="filter_name"
                           value="{{ @$_GET['filter_name'] }}">
                </div>
            </div>

            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_mobile">شماره موبایل</label>
                    <input type="text" class="form-control" id="filter_mobile" name="filter_mobile"
                           value="{{ @$_GET['filter_mobile'] }}">
                </div>
            </div>

            <form class="filter_list" method="get" style="direction: rtl" id="myForm">
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="from">تاریخ قرارداد از:</label>
                        <input id="from" type="text" class="form-control observer" name="from" required>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to"> تاریخ قرارداد تا:</label>
                        <input id="to" type="text" class="form-control observer" name="to" required>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="form-group">
                        <label for="type">نوع قرارداد</label>
                        <select name="type" id="type" class="form-control">
                            <option value="real" {{@$_GET['contract_type']=='real' ? 'selected' : ''}}>حقیقی</option>
                            <option value="legal" {{@$_GET['contract_type']==1 ? 'legal' : ''}}>حقوقی</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-6">
                    <div class="form-group">
                        <label for="category">دسته بندی</label>
                        <select name="category" id="category" class="form-control">
                            <option value="wallet" {{@$_GET['category']=='wallet' ? 'selected' : ''}}>کیف پول</option>
                            <option value="cod" {{@$_GET['category']=='cod' ? 'selected' : ''}}>کارت خوان</option>
                            <option value="visit" {{@$_GET['category']=='visit' ? 'visit' : ''}}>ویزیت</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_status">وضعیت</label>
                        <select type="text" class="form-control" id="filter_status" name="filter_status">
                            <option value="" {{ @$_GET['filter_status'] == null ? 'selected' : '' }}></option>
                            <option value="active" {{ @$_GET['filter_status'] == 'active' ? 'selected' : '' }}>فعال</option>
                            <option value="expired" {{ @$_GET['filter_status'] == 'expired' ? 'selected' : '' }}>منقضی شده</option>
                        </select>
                    </div>
                </div>
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="filter_secretary">منشی</label>--}}
{{--                        <input type="text" class="form-control" id="filter_secretary" name="filter_secretary"--}}
{{--                               value="{{ @$_GET['filter_secretary'] }}">--}}
{{--                    </div>--}}
{{--                </div>--}}

                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-sm-2 col-xs-6">
                        <button type="button" class="btn btn-block btn-success btn-rounded"
                                onclick="search()">جستجو</button>
                    </div>
{{--                    <div class="col-sm-2 col-xs-6">--}}
{{--                        <a>--}}
{{--                            <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">--}}
{{--                                خروجی اکسل--}}
{{--                            </button>--}}
{{--                        </a>--}}
{{--                    </div>--}}
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div
                            style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        تعداد کل رکوردها : {{ number_format($request ? $request->total() : 0) }} عدد
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>

    @if($request)
        <div class="white-box">

            <div class="portlet-body">
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover">
                        <tr role="row" class="heading">
                            <th>نام و نام خانوادگی</th>
                            <th>موبایل</th>
                            <th>وضعیت</th>
                            <th>دسته بندی</th>
                            <th>نوع قرارداد</th>
                            <th>تاریخ شروع قرارداد</th>
                            <th>تاریخ پایان قرارداد</th>
                            <th>کارمزد خدمت</th>
                            <th></th>
                        </tr>


                        @foreach($request as $k => $item)

                            @php
                                $map = [
                                    'wallet' => 'کیف پول',
                                    'visit' => 'ویزیت',
                                    'cod' => 'کارتخوان',
                                ];
                            $category = $map[$item['category']];
                            @endphp

                            <tr role="row" class="filter">
                                <td>{{ ($item['doctor']['fullname']) ? $item['doctor']['doctor_nickname'] . ' ' . $item['doctor']['fullname'] : '-' }}</td>
                                <td>{{ ($item['doctor']['mobile']) ? $item['doctor']['mobile'] : '-' }}</td>
                                <td>{{ $item['status'] == 'active' ? 'فعال' : 'منقضی شده'}}</td>
                                <td>{{ $category}}</td>
                                <td>{{ $item['contract_type'] == 'real' ? 'حقیقی' : 'حقوقی'}}</td>
                                <td>{{ jdate('Y/m/d ', strtotime($item['start_at'])) }}</td>
                                <td>{{ jdate('Y/m/d ', strtotime($item['expire_at'])) }}</td>
                                <td>{{ number_format($item['percent'],2) }}</td>
                                <td>
{{--                                    <div class="col-xs-6">--}}
{{--                                    <a class="btn btn-block btn-info btn-rounded request_but"--}}
{{--                                           href="{{ url('cp-manager/doctor/contract/edit/' . $item['id']) }}"--}}
{{--                                           style="white-space: normal"> ویرایش </a>--}}
{{--                                    </div>--}}
                                    <div class="col-xs-12">
                                        <a class="btn btn-block btn-success btn-rounded request_but"
                                           href="{{route('doctors.contract.create',$item['user_id'])}}">ثبت قرارداد</a>
                                        <hr>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                    </table>
                </div>
                {!! $request->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                <div class="clearfix"></div>
            </div>

        </div>
    @endif
@endsection
<script>
    {{--function exportList(){--}}
    {{--    let form = document.getElementById('myForm');--}}
    {{--    form.action = '{{route('export.contract')}}'--}}
    {{--    form.submit();--}}
    {{--}--}}
    function search(){
        let form = document.getElementById('myForm');
        form.action = '{{route('doctors.contract.index')}}'
        form.submit();
    }
</script>
