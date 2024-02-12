@extends('admin.layouts.app')

@section('page_name', 'کاربران')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="col-sm-12">
                <p style="font-size: 16px">خروجی اکسل</p>

            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/user/export-users') }}" class="btn btn-block btn-info btn-rounded">همه
                    کاربران</a>
            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/user/export-inactive') }}" class="btn btn-block btn-info btn-rounded">کاربران
                    فعال و بدون وقت</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl">

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
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_status">وضعیت</label>
                        <select id="filter_status" class="form-control" name="filter_status">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="active" {{ (@$_GET['filter_status'] == 'active') ? 'selected' : '' }}>فعال
                            </option>
                            <option value="inactive" {{ (@$_GET['filter_status'] == 'inactive') ? 'selected' : '' }}>غیر
                                فعال
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_from_">ثبت نام شده از</label>
                        <select id="filter_from_" class="form-control" name="filter_from_">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="151122" {{ (@$_GET['filter_from_'] == '151122') ? 'selected' : '' }}>پالسی نو
                            </option>
                            <option value="103496" {{ (@$_GET['filter_from_'] == '103496') ? 'selected' : '' }}>
                                های کیش
                            </option>
                            <option value="83401" {{ (@$_GET['filter_from_'] == '83401') ? 'selected' : '' }}>
                                جیرینگ
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_to_">هدایت شده به</label>
                        <select id="filter_to_" class="form-control" name="filter_to_">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="4" {{ (@$_GET['filter_to_'] == '4') ? 'selected' : '' }}>پالسی نو
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_credit">اعتبار</label>
                        <select  id="filter_credit" class="form-control" name="filter_credit">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="1" {{ (@$_GET['filter_credit'] == '1') ? 'selected' : '' }}>دارد</option>
                            <option value="0" {{ (@$_GET['filter_credit'] == '0') ? 'selected' : '' }}>ندارد</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_email">ایمیل</label>
                        <input type="text" class="form-control" id="filter_email" name="filter_email" value="{{ @$_GET['filter_email'] }}">
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
    </div>

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th></th>
                        <th>نام و نام خانوادگی</th>
                        <th>ایمیل</th>
                        <th>شماره موبایل</th>
                        <th>تاریخ عضویت</th>
                        <th>وضعیت</th>
                        <th>اعتبار</th>
                        <th>اعمال</th>
                    </tr>
                    @php $row_count =1 @endphp
                    @foreach($request as $k => $item)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td>{{ ($item['fullname']) ? $item['fullname'] : '-' }}</td>
                            <td>{{ ($item['email']) ? $item['email'] : 'بدون ایمیل' }}</td>
                            <td>{{ ($item['mobile']) ? $item['mobile'] : 'بدون موبایل' }}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s', strtotime($item['created_at'])) }}</td>
                            <td>
                                @switch($item['status'])
                                    @case('active')
                                    فعال
                                    @break
                                    @case('inactive')
                                    غیر فعال
                                    @break
                                @endswitch
                            </td>
                            <td>{{ number_format($item['credit']) }} ریال</td>
                            <td>
                                @can('users-edit')
                                <div class="col-xs-4">
                                    <a class="btn btn-block btn-info btn-rounded request_but"
                                       href="{{ url('cp-manager/user/edit/' . $item['id']) }}"> ویرایش </a>
                                </div>
                                @endcan
                                @can('users-transactions')
                                <div class="col-xs-4">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{route('transactions.users.index',$item)}}">تراکنش</a>
                                </div>
                                    @endcan
                                @if($item['credit'] > 0 && $item['account_sheba'])
                                    <div class="col-xs-4">
                                        <a class="btn btn-block btn-warning btn-rounded request_but"
                                           href="{{route('user.initialize.money',$item)}}"> انتقال اعتبار </a>
                                    </div>
                                @endif
                                    <div class="col-xs-4">
                                    <a class="btn btn-success btn-rounded" target="_blank"
                                       href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($item['mobile'])."&app_absent=0"}}>
                                        واتس اپ</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-4">
                                        <a class="btn btn-block btn-success btn-rounded request_but"
                                           href="{{route('user.change.approve',['user'=>$item['id']])}}">تغییر به
                                            پزشک</a>
                                        <hr>
                                    </div>
                            </td>
                        </tr>
                        @php $row_count =$row_count+1 @endphp
                    @endforeach

                </table>
            </div>
            {!! $request->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
