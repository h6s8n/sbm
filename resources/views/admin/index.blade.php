@extends('admin.layouts.app')

@section('page_name', 'داشبورد')

@section('content')

    <div class="row">
        <div class="col-lg-3 col-sm-3 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">تعداد کاربران</h3>
                <ul class="list-inline two-part">
                    <li>
                        <div id="sparklinedash"></div>
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-success"></i> <span class="counter text-success">{{ \App\User::where('approve', '2')->where('status', 'active')->count() }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-lg-3 col-sm-3 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">تعداد پزشکان تایید شده</h3>
                <ul class="list-inline two-part">
                    <li>
                        <div id="sparklinedash2"></div>
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span class="counter text-purple">{{ \App\User::where('approve', '1')
->where('doctor_status', 'active')->whereIn('status', ['active','imported'])->count() }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-lg-3 col-sm-3 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">تعداد پزشکان معلق</h3>
                <ul class="list-inline two-part">
                    <li>
                        <div id="sparklinedash3"></div>
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-info"></i> <span class="counter text-info">{{ \App\User::where('approve', '1')->where('doctor_status', 'inactive')->where('status', 'active')->count() }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-lg-3 col-sm-3 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">تعداد ویزیت ها</h3>
                <ul class="list-inline two-part">
                    <li>
                        <div id="sparklinedash4"></div>
                    </li>
                    <li class="text-right"><span class="text-danger" style="font-size: 18px">{{ \App\Model\Visit\EventReserves::where('status', 'active')->count() }}</span></li>
                </ul>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-xs-12 text-center">
                        <h3 class="box-title">صورت حساب پزشکان</h3>
                        <p class="m-t-30">مشاهده همه صورت حساب پزشکان در از نرم افزار.</p>
                        <p><br/>
                            <a href="{{ url('cp-manager/bill/doctors?filter_start_date=' . jdate('Y/m/d')) }}" class="btn btn-block btn-success btn-rounded">صورت حساب امروز</a>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 text-center">
                        <h3 class="box-title">کاربران</h3>
                        <p class="m-t-30">مشاهده همه کاربران موجود</p>
                        <p><br/>
                            <a href="{{ url('cp-manager/users') }}" class="btn btn-block btn-info btn-rounded">کاربران</a>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 text-center">
                        <h3 class="box-title">پزشکان</h3>
                        <p class="m-t-30">مشاهده همه پزشکان موجود</p>
                        <p><br/>
                            <a href="{{ url('cp-manager/doctors') }}" class="btn btn-block btn-warning btn-rounded">پزشکان</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
