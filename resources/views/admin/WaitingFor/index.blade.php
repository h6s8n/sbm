@extends('admin.layouts.app')

@section('page_name', 'افراد در انتظار')

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
                        <label for="filter_counts">تعداد در انتظار</label>
                        <select type="" class="form-control" id="filter_counts" name="filter_counts">
                            <option value="desc" {{ @$_GET['filter_counts'] == 'desc' ? 'selected' : '' }}>بیشترین</option>
                            <option value="asc" {{ @$_GET['filter_counts'] == 'asc' ? 'selected' : '' }} >کمترین</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_login_history">سابقه ی ورود</label>
                        <select type="" class="form-control" id="filter_login_history" name="filter_login_history">
                            <option value="" {{ @$_GET['filter_login_history'] == null ? 'selected' : '' }}></option>
                            <option value='1' {{ @$_GET['filter_login_history'] == '1' ? 'selected' : '' }}>دارد</option>
                            <option value='0' {{ @$_GET['filter_login_history'] == '0' ? 'selected' : '' }} >ندارد</option>
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-6" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>
                <div class="col-sm-2 col-xs-6" style="padding-top: 17px;">
                    <a>
                        <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportList()">خروجی
                            اکسل
                        </button>
                    </a>
                </div>

                <div class="col-sm-2 col-xs-6" style="padding-top: 17px;">
                    <a>
                        <button type="button" class="btn btn-block btn-success btn-rounded" onclick="exportDetailList()">خروجی
                            اکسل جزئیات
                        </button>
                    </a>
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>
    <form method="post">
        {{ csrf_field() }}
        <div class="white-box">

            <div class="portlet-body">
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr role="row" class="heading">
                            <th></th>
                            <th> پزشک</th>
                            <th> گروه پزشکی</th>
                            <th>سابقه ی ورود</th>
                            <th> موبایل</th>
                            <th> منشی</th>
                            <th> نوع</th>
                            <th> تعداد در انتظار</th>
                            <th style="width: 150px;"></th>
                        </tr>
                        @php $row_count =1 @endphp
                        @foreach($items as $item)
                            <tr role="row" class="filter">
                                <td>{{$row_count}}</td>
                                <td>{{$item->doctor->fullname}}</td>
{{--                                <td>{{$item->doctor->allSpecializationsString()}}</td>--}}
                                <td>
                                    @if($item->doctor->sp_gp)
                                        {{ $item->doctor->sp_gp }}
                                        {{--                                @if($item->specializations()->first())--}}
                                        {{--                                    @foreach($item->specializations()->get() as $sp)--}}
                                        {{--                                        {{$sp->name.' '}}--}}
                                        {{--                                    @endforeach--}}
                                    @else
                                        وارد نشده
                                    @endif
                                </td>
                                <td>{{ $item->doctor->hasLoginHistory() ? 'دارد' : 'ندارد' }}</td>
                                <td>{{$item->doctor->mobile}}</td>
                                <td>{{$item->doctor->secretary ?
$item->doctor->secretary->full_name : 'وارد نشده'}}</td>
                                <td>{{$item->doctor->status ==='active' ? 'ثبت نامی' : ($item->doctor->status==='imported' ? 'ایمپورت شده' : '')}}</td>
                                <td>{{$item->counts}}</td>
                                <td>
                                    <div style="display: flex;flex-wrap: wrap">
                                        <div class="col-xs-12">
                                            <a class="btn btn-block btn-primary btn-rounded request_but"
                                               href="{{route('waiting.details',$item->doctor->id)}}">جزییات</a>
                                        </div>
                                        <div class="col-xs-12" style="margin-top: 10px">
                                            <a class="btn btn-block btn-warning btn-rounded request_but"
                                               target="_blank"
                                               href="{{'https://sbm24.com/'.$item->doctor->username}}">پروفایل پزشک</a>
                                        </div>
                                        <div class="col-xs-12" style="margin-top: 10px">
                                            <a class="btn btn-block btn-success btn-rounded request_but"
                                               target="_blank"
                                               href="https://wa.me/{{StandardNumber($item->doctor->mobile)}}">واتس
                                                اپ</a>
                                        </div>
                                        <div class="col-xs-12" style="margin-top: 10px">
                                            <a class="btn btn-block btn-primary btn-rounded request_but"
                                               href="{{route('manual.send.sms',$item->doctor_id)}}">ارسال SMS</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php $row_count = $row_count +1;@endphp
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $items->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                <div class="clearfix"></div>

            </div>

        </div>
    </form>
    <script>
        let exportList = () => {
            let form = document.getElementById('myForm');
            form.action = '<?php echo e(route('waiting.export')); ?>';
            form.submit();
        }

        let exportDetailList = () => {
            let form = document.getElementById('myForm');
            form.action = '<?php echo e(route('waiting.specialization.details.export')); ?>';
            form.submit();
        }
    </script>
@endsection
