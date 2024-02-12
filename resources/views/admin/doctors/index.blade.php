@extends('admin.layouts.app')

@section('page_name', 'پزشکان')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="col-sm-12">
                <p style="font-size: 16px">خروجی اکسل</p>

            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/doctor/export-doctors') }}" class="btn btn-block btn-info btn-rounded">همه
                    پزشکان</a>
            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/doctor/export-inactive') }}" class="btn btn-block btn-info btn-rounded">
                    ثبت نامی و بدون وقت</a>
            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/doctor/export-status/active') }}"
                   class="btn btn-block btn-info btn-rounded">پزشکان تایید شده</a>
            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/doctor/export-status/inactive') }}"
                   class="btn btn-block btn-info btn-rounded">پزشکان معلق</a>
            </div>
            <div class="col-sm-2 col-xs-6">
                <a href="{{ url('cp-manager/doctor/export-status/failed') }}"
                   class="btn btn-block btn-info btn-rounded">پزشکان رد شده</a>
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
                        <label for="filter_special_code">شماره نظام پزشکی</label>
                        <input type="text" class="form-control" id="filter_special_code" name="filter_special_code"
                               value="{{ @$_GET['filter_special_code'] }}">
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
                            <option value="imported" {{ (@$_GET['filter_status'] == 'imported') ? 'selected' : '' }}>
                                ایمپورت شده
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_doctor_status">وضعیت اطلاعات پنل</label>
                        <select id="filter_doctor_status" class="form-control" name="filter_doctor_status">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="active" {{ (@$_GET['filter_doctor_status'] == 'active') ? 'selected' : '' }}>
                                تایید
                            </option>
                            <option
                                value="inactive" {{ (@$_GET['filter_doctor_status'] == 'inactive') ? 'selected' : '' }}>
                                معلق
                            </option>
                            <option value="failed" {{ (@$_GET['filter_doctor_status'] == 'failed') ? 'selected' : '' }}>
                                رد شده
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_email">ایمیل</label>
                        <input type="text" class="form-control" id="filter_email" name="filter_email"
                               value="{{ @$_GET['filter_email'] }}">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="specialization_id">گروه پزشکی</label>
                        <select id="specialization_id" class="form-control" name="specialization_id">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($specializations as $sp)
                                <option value="{{$sp->id}}" {{@$_GET['specialization_id'] == $sp->id ? 'selected' : ''}}>{{$sp->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="from">از تاریخ</label>
                        <input type="text" class="form-control observer" name="from">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to">تا تاریخ</label>
                        <input type="text" class="form-control observer" name="to">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_visit_counter">وقت های آتی</label>
                        <select id="filter_visit_counter" class="form-control" name="filter_visit_counter">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="desc" {{ (@$_GET['filter_visit_counter'] == 'desc') ? 'selected' : '' }}>
                                بیشترین
                            </option>
                            <option
                                value="asc" {{ (@$_GET['filter_visit_counter'] == 'asc') ? 'selected' : '' }}>
                                کمترین
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to">دارای ویزیت</label>
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

                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <hr>
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


    <div class="white-box">

        <div class="portlet-body">
            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th></th>
                        <th>نام کامل</th>
                        <th>ایمیل</th>
                        <th>گروه پزشکی</th>
                        <th>موبایل</th>
                        <th>عضویت</th>
                        <th>موفق</th>
                        <th>آتی</th>
                        <th>غیبت</th>
                        <th>وضعیت</th>
                        <th> پنل</th>
                        <th>اعمال</th>
                    </tr>

                    @foreach($request as $k => $item)
                        <tr role="row" class="filter">
                            <td>
                                <a href="{{route('doctor.detail.create',$item['id'])}}" class="icon icon-doc"
                                   style="color: red"></a>
                            </td>
                            <td>{{ ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-' }}</td>
                            <td style="max-width: 150px;overflow: auto">{{ ($item['email']) ? $item['email'] : 'بدون ایمیل' }}</td>
                            <td>
                                @if($item['sp_gp'])
                                    {{ $item['sp_gp'] }}
{{--                                @if($item->specializations()->first())--}}
{{--                                    @foreach($item->specializations()->get() as $sp)--}}
{{--                                        {{$sp->name.' '}}--}}
{{--                                    @endforeach--}}
                                @else
                                    وارد نشده
                                @endif
                            </td>
                            <td>{{ ($item['mobile']) ? $item['mobile'] : 'بدون موبایل' }}</td>
                            <td style="max-width: 150px;overflow: auto">{{ jdate('Y/m/d ساعت H:i:s', strtotime($item['created_at'])) }}</td>
                            <td>{{ number_format($visit_seen_counter[$item['id']]) }} ویزیت</td>
                            <td>{{ number_format($visit_counter[$item['id']]) }} وقت</td>
                            <td></td>
                            <td>
                                @switch($item['status'])
                                    @case('active')
                                    فعال
                                    @break
                                    @case('imported')
                                    فعال-ایمپورت شده
                                    @break
                                    @case('inactive')
                                    غیر فعال
                                    @break
                                @endswitch
                            </td>
                            <td>
                                @switch($item['doctor_status'])
                                    @case('active')
                                    تایید
                                    @break
                                    @case('inactive')
                                    معلق
                                    @break
                                    @case('failed')
                                    رد شده
                                    @break
                                @endswitch
                            </td>
                            <td style="max-width: 300px">
                                @can('doctors-edit')
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-info btn-rounded request_but"
                                           href="{{ url('cp-manager/doctor/edit/' . $item['id']) }}"
                                           style="white-space: normal"> ویرایش </a>
                                        <hr>
                                    </div>
                                @endcan

                                {{--                                <div class="col-xs-4">--}}
                                {{--                                    <a class="btn btn-block btn-warning btn-rounded request_but" href="{{ url('cp-manager/calenders/?doctor=' . $item['id']) }}"> برنامه کاری </a>--}}
                                {{--                                </div>--}}
                                @can('doctors-secretary')
                                    @if($item['mobile'])
                                        @if(!$item->secretary()->first())
                                            <div class="col-xs-6">
                                                <a class="btn btn-block btn-warning btn-rounded request_but"
                                                   href="{{route('secretary.create',$item['id'])}}">ساخت اکانت </a>
                                                <hr>
                                            </div>
                                        @else
                                            <div class="col-xs-6">
                                                <a class="btn btn-block btn-danger btn-rounded request_but"
                                                   href="{{route('secretary.edit',$item['id'])}}">ویرایش اکانت </a>
                                                <hr>
                                            </div>
                                        @endif
                                    @endif
                                @endcan
                                @can('doctors-tags')
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-primary btn-rounded request_but"
                                           href="{{route('tag.index',$item['id'])}}">تگ ها</a>
                                        <hr>
                                    </div>
                                @endcan
{{--                                @if(count($request)==1)--}}
{{--                                    <div class="col-xs-6">--}}
{{--                                        <a class="btn btn-block btn-danger btn-rounded request_but delete-all-calendars"--}}
{{--                                           href="{{route('delete.all.calendars',$item['id'])}}">حذف همه برنامه ها</a>--}}
{{--                                        <hr>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
                                @can('changing-approve')
                                    @if($item['doctor_status'] === "inactive")
                                        <div class="col-xs-6">
                                            <a class="btn btn-block btn-success btn-rounded request_but"
                                               href="{{route('doctor.change.approve',['user'=>$item['id']])}}">تغییر به
                                                بیمار</a>
                                            <hr>
                                        </div>
                                    @endif
                                @endcan
                                @can('doctor-information')
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-info btn-rounded request_but"
                                           href="{{route('doctor.information.create',['user'=>$item['id']])}}">اطلاعات
                                            پزشک</a>
                                        <hr>
                                    </div>
                                @endcan
                                @can('doctor-summary')
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-warning btn-rounded request_but"
                                           href="{{route('doctor.report',['user'=>$item['id']])}}">خلاصه گزارش</a>
                                        <hr>
                                    </div>
                                @endcan
                                    <div class="col-xs-6">
                                    <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                       href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($item['mobile'])."&app_absent=0"}}>
                                        واتس اپ</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-primary btn-rounded request_but"
                                           href="{{route('user.setting',$item['id'])}}">تنظیمات</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                           href={{"https://sbm24.com/".$item['username']}}>
                                            پروفایل در سایت</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-warning btn-rounded request_but"
                                           href="{{route('assign.badge',$item['id'])}}">نشان ها</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-success btn-rounded request_but"
                                           href="{{route('doctors.contract.index',['doctor_id' => $item['id']])}}">قرارداد ها</a>
                                        <hr>
                                    </div>
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-success btn-rounded request_but"
                                           href="{{route('doctors.contract.create',$item['id'])}}">ثبت قرارداد</a>
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
    <script>
        $('.delete-all-calendars').click(function (e) {
            e.preventDefault();
            let hreflink = $(this).attr('href');
            swal({
                title: "",
                text: " آیا از حذف برنامه های پزشک اطمینان دارید ؟ ",
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

    </script>
@endsection
