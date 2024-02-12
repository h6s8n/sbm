@extends('admin.layouts.app')

@section('page_name', 'پزشکان')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form class="filter_list" method="get" style="direction: rtl" id="myForm">

{{--                <div class="col-md-3 col-xs-6">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="date">پزشکانی که در تاریخ انتخابی هیچ وقتی ثبت نگردند </label>--}}
{{--                        <select name="date" id="date" class="form-control">--}}
{{--                            <option value="1" {{@$_GET['date']==1 ? 'selected' : ''}}>یک هفته</option>--}}
{{--                            <option value="2" {{@$_GET['date']==2 ? 'selected' : ''}}>دو هفته</option>--}}
{{--                            <option value="3" {{@$_GET['date']==3 ? 'selected' : ''}}>سه هفته</option>--}}
{{--                            <option value="4" {{@$_GET['date']==4 ? 'selected' : ''}}>یک ماه</option>--}}
{{--                            <option value="8" {{@$_GET['date']==8 ? 'selected' : ''}}>دو ماه</option>--}}
{{--                            <option value="13" {{@$_GET['date']==13 ? 'selected' : ''}}>سه ماه</option>--}}
{{--                            <option value="17" {{@$_GET['date']==17 ? 'selected' : ''}}>چهار ماه</option>--}}
{{--                            <option value="21" {{@$_GET['date']==21 ? 'selected' : ''}}>پنج ماه</option>--}}
{{--                            <option value="26" {{@$_GET['date']==26 ? 'selected' : ''}}>شش ماه</option>--}}
{{--                            <option value="30" {{@$_GET['date']==30 ? 'selected' : ''}}>هفت ماه</option>--}}
{{--                            <option value="35" {{@$_GET['date']==35 ? 'selected' : ''}}>هشت ماه</option>--}}
{{--                            <option value="40" {{@$_GET['date']==40 ? 'selected' : ''}}>نه ماه</option>--}}
{{--                            <option value="52" {{@$_GET['date']==52 ? 'selected' : ''}}>یک سال</option>--}}
{{--                            <option value="104" {{@$_GET['date']==104 ? 'selected' : ''}}>دو سال</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="from"> تاریخ آخرین نوبت</label>
                        <input type="text" class="form-control observer" name="from" required>
                    </div>
                </div>
{{--                <div class="col-md-3 col-xs-12">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="to">تا تاریخ</label>--}}
{{--                        <input type="text" class="form-control observer" name="to" required>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="col-md-3 col-xs-6">
                    <div class="form-group">
                        <label for="partner">نوع وقت</label>
                        <select name="partner" id="partner" class="form-control">
                            <option value="0" {{@$_GET['partner']==0 ? 'selected' : ''}}>SBM</option>
                            <option value="1" {{@$_GET['partner']==1 ? 'selected' : ''}}>بیمارستانی</option>
                        </select>
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
                        <label for="filter_user">نوع</label>
                        <select type="text" class="form-control" id="filter_status" name="filter_status">
                            <option value="" {{ @$_GET['filter_status'] == null ? 'selected' : '' }}></option>
                            <option value="active" {{ @$_GET['filter_status'] == 'active' ? 'selected' : '' }}>ثبت نامی</option>
                            <option value="imported" {{ @$_GET['filter_status'] == 'imported' ? 'selected' : '' }}>ایمپورت شده</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_secretary">منشی</label>
                        <input type="text" class="form-control" id="filter_secretary" name="filter_secretary"
                               value="{{ @$_GET['filter_secretary'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_counts">تعداد ویزیت موفق</label>
                        <select type="" class="form-control" id="filter_counts" name="filter_counts">
                            <option value="desc" {{ @$_GET['filter_counts'] == 'desc' ? 'selected' : '' }}>بیشترین</option>
                            <option value="asc" {{ @$_GET['filter_counts'] == 'asc' ? 'selected' : '' }} >کمترین</option>
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-sm-2 col-xs-6">
                        <button type="button" class="btn btn-block btn-success btn-rounded"
                                onclick="search()">جستجو</button>
                    </div>
                    <div class="col-sm-2 col-xs-6">
                        <a>
                            <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">
                                خروجی اکسل
                            </button>
                        </a>
                    </div>
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
                            <th>نام کامل</th>
                            <th>تخصص ها</th>
                            <th> موبایل</th>
                            <th> عضویت</th>
                            <th>نوع دکتر</th>
                            @if(@$_GET['partner'] == 1)
                            <th style="width: 10%">بیمارستان ها</th>
                            @endif
                            <th>ویزیت های موفق</th>
                            <th>کل وقت ها</th>
                            <th>آخرین وقت</th>
                            <th>انتظار</th>
                            <th></th>
                            <th></th>
                        </tr>

                        @foreach($request as $k => $item)
                            <tr role="row" class="filter">
                                <td>{{ ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-' }}</td>
                                <td>
                                    @if($item->specializations()->first())
                                        @foreach($item->specializations()->get() as $sp)
                                            {{$sp->name.' '}}
                                        @endforeach
                                    @else
                                        وارد نشده
                                    @endif
                                </td>
                                <td>{{ ($item['mobile']) ? $item['mobile'] : 'بدون موبایل' }}</td>
                                <td>{{ jdate('Y/m/d ساعت H:i:s', strtotime($item['created_at'])) }}</td>
                                <td>{{$item['status'] == 'active' ? 'ثبت نامی' : 'ایمپورت شده'}}</td>
                                @if(@$_GET['partner'] == 1)
                                <td>
                                    @foreach($item->partners()->get() as $partner)
                                        {{$partner->name}}
                                    @endforeach
                                </td>
                                @endif
                                <td>{{$item->DoctorEvents('end')->count()}}</td>
                                @if(@$_GET['partner'] == 0)
                                    <td>{{$item->calenders()->where(function ($query){
    $query->whereNull('partner_id')->orWhere('partner_id',0);})->count()}}</td>
                                @else
                                    <td>{{$item->calenders()->where(function ($query){
    $query->whereNotNull('partner_id')->orWhere('partner_id','!=',0);})->count()}}</td>
                                @endif
                                <td>{{$item->calenders()->max('fa_data') ?? $_GET['from']}}</td>
                                <td>{{$item->Waiting()->where('sent_message',0)->count()}}</td>
                                <td>
                                    @can('doctors-edit')
                                            <a class="btn btn-block btn-info btn-rounded request_but"
                                               href="{{ url('cp-manager/doctor/edit/' . $item['id']) }}"
                                               style="white-space: normal"> ویرایش </a>
                                    @endcan
                                    <hr>
                                    @if((@$_GET['partner'] == 0))
                                            <a class="btn btn-block btn-warning btn-rounded request_but"
                                               href="{{route('calendar.create',['user_id'=>$item['id']])}}"
                                               style="white-space: normal"> وقت </a>
                                    @endif
                                </td>
                                <td style="    border-right: hidden;">
                                        <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                           href={{"https://api.whatsapp.com/send/?phone=".StandardNumber($item['mobile'])."&app_absent=0"}}>
                                            واتس اپ</a>
                                        <hr>
                                    <a class="btn btn-block btn-info btn-rounded request_but"
                                       target="_blank"
                                       href="{{ 'https://sbm24.com/'. $item['username'] }}"
                                       style="white-space: normal"> پروفایل </a>
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
    function exportList(){
        let form = document.getElementById('myForm');
        form.action = '{{route('export.underemployed')}}'
        form.submit();
    }
    function search(){
        let form = document.getElementById('myForm');
        form.action = '{{route('doctors.underemployed')}}'
        form.submit();
    }
</script>
